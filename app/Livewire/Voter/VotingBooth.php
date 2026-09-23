<?php

namespace App\Livewire\Voter;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Component;

class VotingBooth extends Component
{
    // ---- Fase Verifikasi ----
    public string $nis = '';

    public bool $verified = false;

    public ?int $studentId = null;

    public string $studentName = '';

    // ---- Fase Pemilihan ----
    public ?int $selectedCandidateId = null;

    public bool $showConfirmModal = false;

    // ---- State UI ----
    public bool $isProcessing = false;

    public ?string $errorMessage = null;

    public ?string $infoMessage = null;

    public bool $voteSuccess = false;

    public function mount(): void
    {
        $this->resetBooth();
    }

    #[Computed]
    public function votingOpen(): bool
    {
        return (bool) Setting::current()->is_voting_open;
    }

    #[Computed]
    public function candidates()
    {
        return Candidate::where('is_active', true)
            ->orderBy('number_order')
            ->get();
    }

    /**
     * FASE 1: Verifikasi NIS.
     * Dibatasi rate limit per-IP untuk mencegah percobaan brute-force
     * menebak NIS siswa lain secara berurutan.
     */
    public function verifyNis(): void
    {
        $this->resetMessages();

        $throttleKey = 'verify-nis:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 15)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.";

            return;
        }

        $this->validate([
            'nis' => ['required', 'string', 'max:30'],
        ], [
            'nis.required' => 'NIS/NIK wajib diisi.',
        ]);

        RateLimiter::hit($throttleKey, 60);

        $student = Student::where('nis', $this->nis)->first();

        if (! $student) {
            SystemLog::record(
                SystemLog::TYPE_NIS_NOT_FOUND,
                "Percobaan verifikasi dengan NIS tidak terdaftar: {$this->nis}",
            );
            $this->errorMessage = 'NIS tidak ditemukan di Daftar Pemilih Tetap (DPT). Hubungi panitia jika ini keliru.';

            return;
        }

        if ($student->has_voted) {
            SystemLog::record(
                SystemLog::TYPE_DUPLICATE_ATTEMPT,
                "Percobaan vote ganda oleh {$student->name} (NIS: {$student->nis})",
                $student->id,
            );
            $this->errorMessage = 'NIS ini SUDAH melakukan pemilihan sebelumnya. Satu siswa hanya berhak memilih satu kali.';

            return;
        }

        if (! $this->votingOpen) {
            $this->errorMessage = 'Sesi voting saat ini sedang ditutup oleh panitia.';

            return;
        }

        // Sukses — tampilkan bilik suara
        $this->studentId = $student->id;
        $this->studentName = $student->name;
        $this->verified = true;
        RateLimiter::clear($throttleKey);
    }

    public function selectCandidate(int $candidateId): void
    {
        $this->selectedCandidateId = $candidateId;
        $this->showConfirmModal = true;
    }

    public function cancelConfirmation(): void
    {
        $this->selectedCandidateId = null;
        $this->showConfirmModal = false;
    }

    /**
     * FASE 2: Konfirmasi & Simpan Suara.
     *
     * TIGA LAPIS PROTEKSI RACE CONDITION (spam-click / concurrent request):
     *
     *  Lapis 1 — Application lock ($isProcessing + Cache::lock):
     *      $isProcessing menahan klik berulang dari SATU sesi browser yang
     *      sama (dikombinasikan dengan wire:loading.attr="disabled" di
     *      Blade). Cache::lock() menahan permintaan yang datang dari
     *      DUA proses PHP-FPM/Apache berbeda secara bersamaan untuk
     *      student_id yang sama (mis. 2 tab / double submit jaringan).
     *
     *  Lapis 2 — Pessimistic row lock (lockForUpdate):
     *      Di dalam DB transaction, baris `students` dikunci dengan
     *      SELECT ... FOR UPDATE. Transaksi kedua yang mencoba membaca
     *      baris yang sama akan MENUNGGU (bukan membaca data basi)
     *      sampai transaksi pertama commit/rollback.
     *
     *  Lapis 3 — Unique constraint di kolom votes.student_id:
     *      Pertahanan terakhir di level database. Bila entah bagaimana
     *      dua transaksi lolos lapis 1 & 2, MySQL akan menolak INSERT
     *      kedua dan melempar QueryException (SQLSTATE 23000) yang
     *      kita tangkap secara eksplisit.
     */
    public function confirmVote(): void
    {
        // Lapis 1a: cegah klik ganda dari sesi Livewire yang sama
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        $this->resetMessages();

        if (! $this->studentId || ! $this->selectedCandidateId) {
            $this->isProcessing = false;
            $this->errorMessage = 'Sesi tidak valid. Silakan verifikasi ulang NIS Anda.';
            $this->verified = false;

            return;
        }

        $studentId = $this->studentId;
        $candidateId = $this->selectedCandidateId;
        $lock = Cache::lock("vote-lock-student-{$studentId}", 10);

        try {
            // Lapis 1b: application-level distributed lock.
            // block(5) menunggu maksimal 5 detik jika ada proses lain
            // yang sedang memegang lock untuk student_id yang sama.
            $lock->block(5, function () use ($studentId, $candidateId) {
                DB::transaction(function () use ($studentId, $candidateId) {

                    // Lapis 2: kunci baris siswa — request lain untuk
                    // siswa yang sama akan menunggu di sini, bukan
                    // membaca has_voted yang belum ter-update.
                    $student = Student::where('id', $studentId)
                        ->lockForUpdate()
                        ->first();

                    if (! $student) {
                        throw new \RuntimeException('Data siswa tidak ditemukan.');
                    }

                    if ($student->has_voted) {
                        // Ditangkap di sini berarti race condition BERHASIL
                        // dicegah oleh lock — bukan lagi celah keamanan.
                        SystemLog::record(
                            SystemLog::TYPE_RACE_CONDITION_BLOCKED,
                            "Race condition diblokir oleh row-lock: {$student->name} (NIS: {$student->nis}) mencoba vote 2x.",
                            $student->id,
                        );
                        throw new \RuntimeException('DUPLICATE_VOTE');
                    }

                    $setting = Setting::current();
                    if (! $setting->is_voting_open) {
                        throw new \RuntimeException('SESSION_CLOSED');
                    }

                    $candidate = Candidate::where('id', $candidateId)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $candidate) {
                        throw new \RuntimeException('Kandidat tidak valid atau tidak aktif.');
                    }

                    // Lapis 3: unique constraint votes.student_id akan
                    // melempar QueryException jika baris sudah ada.
                    Vote::create([
                        'student_id' => $student->id,
                        'candidate_id' => $candidate->id,
                        'ip_address' => request()->ip(),
                        'user_agent' => substr((string) request()->userAgent(), 0, 250),
                        'voted_at' => now(),
                    ]);

                    $student->has_voted = true;
                    $student->voted_at = now();
                    $student->save();

                    $candidate->increment('vote_count');

                    SystemLog::record(
                        SystemLog::TYPE_VOTE_SUCCESS,
                        "Suara masuk: {$student->name} (NIS: {$student->nis}) memilih Paslon No. {$candidate->number_order} - {$candidate->chairman_name}/{$candidate->vice_chairman_name}.",
                        $student->id,
                    );
                });
            });

            $this->voteSuccess = true;
            $this->dispatch('vote-recorded');
        } catch (QueryException $e) {
            // Lapis 3 tereksekusi: unique constraint di DB menolak insert ganda
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                SystemLog::record(
                    SystemLog::TYPE_RACE_CONDITION_BLOCKED,
                    "Race condition diblokir oleh UNIQUE constraint database untuk student_id={$studentId}.",
                    $studentId,
                );
                $this->errorMessage = 'Suara Anda sudah tercatat sebelumnya. Tidak dapat memilih dua kali.';
            } else {
                report($e);
                $this->errorMessage = 'Terjadi kesalahan database. Silakan hubungi panitia.';
            }
            $this->verified = false;
        } catch (\RuntimeException $e) {
            $this->verified = false;
            $this->errorMessage = match ($e->getMessage()) {
                'DUPLICATE_VOTE' => 'Suara Anda sudah tercatat sebelumnya. Tidak dapat memilih dua kali.',
                'SESSION_CLOSED' => 'Sesi voting baru saja ditutup oleh panitia.',
                default => $e->getMessage(),
            };
        } catch (\Throwable $e) {
            report($e);
            $this->verified = false;
            $this->errorMessage = 'Sistem sedang sibuk, silakan coba beberapa saat lagi.';
        } finally {
            $this->isProcessing = false;
            $this->showConfirmModal = false;
        }
    }

    /**
     * Reset total ke kondisi awal — dipanggil otomatis beberapa detik
     * setelah vote sukses (lihat Alpine x-init di Blade) agar bilik
     * suara siap dipakai siswa berikutnya tanpa jejak data siswa
     * sebelumnya di layar.
     */
    public function resetBooth(): void
    {
        $this->nis = '';
        $this->verified = false;
        $this->studentId = null;
        $this->studentName = '';
        $this->selectedCandidateId = null;
        $this->showConfirmModal = false;
        $this->isProcessing = false;
        $this->voteSuccess = false;
        $this->resetMessages();
    }

    private function resetMessages(): void
    {
        $this->errorMessage = null;
        $this->infoMessage = null;
    }

    public function render()
    {
        return view('livewire.voter.voting-booth');
    }
}

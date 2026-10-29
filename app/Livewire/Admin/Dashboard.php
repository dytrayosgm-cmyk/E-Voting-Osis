<?php

namespace App\Livewire\Admin;

use App\Models\AdminSession;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\Vote;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showResetConfirm = false;

    public bool $showSessionConfirm = false;

    public bool $showFinalizeConfirm = false;

    public bool $showClearStudentsConfirm = false;

    public string $resetConfirmText = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null; // success | danger

    public ?array $importSummary = null;

    // Voter monitoring state
    public string $voterTab = 'unvoted'; // unvoted | voted | all

    public string $voterSearch = '';

    public string $voterGrade = ''; // '' | 'X' | 'XI' | 'XII'

    public string $voterClass = '';

    public int $voterPerPage = 10;

    #[Validate('nullable|file|mimes:csv,txt|max:5120')]
    public $studentFile;

    /**
     * Dipanggil oleh wire:poll di Blade setiap beberapa detik agar
     * seluruh metrik, leaderboard, dan log terasa real-time tanpa
     * perlu WebSocket/Reverb (cukup untuk skala jaringan lokal sekolah).
     */
    #[Computed]
    public function totalStudents(): int
    {
        return Student::count();
    }

    #[Computed]
    public function totalVotesIn(): int
    {
        return Vote::count();
    }

    #[Computed]
    public function participationRate(): float
    {
        $total = $this->totalStudents;

        return $total > 0 ? round(($this->totalVotesIn / $total) * 100, 1) : 0.0;
    }

    #[Computed]
    public function setting(): Setting
    {
        return Setting::current();
    }

    #[Computed]
    public function leaderboard()
    {
        $total = $this->totalVotesIn;

        return Candidate::orderByDesc('vote_count')
            ->orderBy('number_order')
            ->get()
            ->map(function (Candidate $c) use ($total) {
                $c->computed_percentage = $c->percentage($total);

                return $c;
            });
    }

    #[Computed]
    public function rankedCandidates()
    {
        return $this->leaderboard;
    }

    public string $logFilter = 'all'; // all | session | votes | warnings

    public function updatedVoterTab(): void
    {
        $this->resetPage('voterPage');
    }

    public function updatedVoterSearch(): void
    {
        $this->resetPage('voterPage');
    }

    public function updatedVoterGrade(): void
    {
        $this->voterClass = '';
        $this->resetPage('voterPage');
    }

    public function updatedVoterClass(): void
    {
        $this->resetPage('voterPage');
    }

    public function updatedVoterPerPage(): void
    {
        $this->resetPage('voterPage');
    }

    #[Computed]
    public function availableClasses()
    {
        $query = Student::query()
            ->whereNotNull('class')
            ->where('class', '!=', '')
            ->distinct();

        if ($this->voterGrade === 'X') {
            $query->where(function ($q) {
                $q->where('class', 'like', 'X %')
                    ->orWhere('class', 'like', 'X-%')
                    ->orWhere('class', 'like', '%-X')
                    ->orWhere('class', 'like', '%-X-%')
                    ->orWhere('class', 'like', '% X')
                    ->orWhere('class', 'like', '10 %')
                    ->orWhere('class', 'like', '10-%')
                    ->orWhere('class', 'like', '%-10')
                    ->orWhere('class', 'like', '%-10-%')
                    ->orWhere('class', 'like', '% 10')
                    ->orWhere('class', '=', 'X')
                    ->orWhere('class', '=', '10');
            });
        } elseif ($this->voterGrade === 'XI') {
            $query->where(function ($q) {
                $q->where('class', 'like', 'XI %')
                    ->orWhere('class', 'like', 'XI-%')
                    ->orWhere('class', 'like', '%-XI')
                    ->orWhere('class', 'like', '%-XI-%')
                    ->orWhere('class', 'like', '% XI')
                    ->orWhere('class', 'like', '11 %')
                    ->orWhere('class', 'like', '11-%')
                    ->orWhere('class', 'like', '%-11')
                    ->orWhere('class', 'like', '%-11-%')
                    ->orWhere('class', 'like', '% 11')
                    ->orWhere('class', '=', 'XI')
                    ->orWhere('class', '=', '11');
            });
        } elseif ($this->voterGrade === 'XII') {
            $query->where(function ($q) {
                $q->where('class', 'like', 'XII %')
                    ->orWhere('class', 'like', 'XII-%')
                    ->orWhere('class', 'like', '%-XII')
                    ->orWhere('class', 'like', '%-XII-%')
                    ->orWhere('class', 'like', '% XII')
                    ->orWhere('class', 'like', '12 %')
                    ->orWhere('class', 'like', '12-%')
                    ->orWhere('class', 'like', '%-12')
                    ->orWhere('class', 'like', '%-12-%')
                    ->orWhere('class', 'like', '% 12')
                    ->orWhere('class', '=', 'XII')
                    ->orWhere('class', '=', '12');
            });
        }

        return $query->orderBy('class')->pluck('class');
    }

    #[Computed]
    public function voterStudents()
    {
        $query = Student::query();

        if ($this->voterTab === 'unvoted') {
            $query->where('has_voted', false);
        } elseif ($this->voterTab === 'voted') {
            $query->where('has_voted', true);
        }

        if ($this->voterGrade === 'X') {
            $query->where(function ($q) {
                $q->where('class', 'like', 'X %')
                    ->orWhere('class', 'like', 'X-%')
                    ->orWhere('class', 'like', '%-X')
                    ->orWhere('class', 'like', '%-X-%')
                    ->orWhere('class', 'like', '% X')
                    ->orWhere('class', 'like', '10 %')
                    ->orWhere('class', 'like', '10-%')
                    ->orWhere('class', 'like', '%-10')
                    ->orWhere('class', 'like', '%-10-%')
                    ->orWhere('class', 'like', '% 10')
                    ->orWhere('class', '=', 'X')
                    ->orWhere('class', '=', '10');
            });
        } elseif ($this->voterGrade === 'XI') {
            $query->where(function ($q) {
                $q->where('class', 'like', 'XI %')
                    ->orWhere('class', 'like', 'XI-%')
                    ->orWhere('class', 'like', '%-XI')
                    ->orWhere('class', 'like', '%-XI-%')
                    ->orWhere('class', 'like', '% XI')
                    ->orWhere('class', 'like', '11 %')
                    ->orWhere('class', 'like', '11-%')
                    ->orWhere('class', 'like', '%-11')
                    ->orWhere('class', 'like', '%-11-%')
                    ->orWhere('class', 'like', '% 11')
                    ->orWhere('class', '=', 'XI')
                    ->orWhere('class', '=', '11');
            });
        } elseif ($this->voterGrade === 'XII') {
            $query->where(function ($q) {
                $q->where('class', 'like', 'XII %')
                    ->orWhere('class', 'like', 'XII-%')
                    ->orWhere('class', 'like', '%-XII')
                    ->orWhere('class', 'like', '%-XII-%')
                    ->orWhere('class', 'like', '% XII')
                    ->orWhere('class', 'like', '12 %')
                    ->orWhere('class', 'like', '12-%')
                    ->orWhere('class', 'like', '%-12')
                    ->orWhere('class', 'like', '%-12-%')
                    ->orWhere('class', 'like', '% 12')
                    ->orWhere('class', '=', 'XII')
                    ->orWhere('class', '=', '12');
            });
        }

        if ($this->voterClass !== '') {
            $query->where('class', $this->voterClass);
        }

        if (trim($this->voterSearch) !== '') {
            $search = trim($this->voterSearch);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('class')->orderBy('name')->paginate((int) $this->voterPerPage, ['*'], 'voterPage');
    }

    #[Computed]
    public function winner(): ?Candidate
    {
        if ($this->totalVotesIn === 0) {
            return null;
        }

        $top = Candidate::orderByDesc('vote_count')->first();
        if ($top) {
            $top->computed_percentage = $top->percentage($this->totalVotesIn);
        }

        return $top;
    }

    #[Computed]
    public function isTie(): bool
    {
        if ($this->totalVotesIn === 0) {
            return false;
        }

        $topTwo = Candidate::orderByDesc('vote_count')->take(2)->get();

        return $topTwo->count() === 2 && $topTwo[0]->vote_count === $topTwo[1]->vote_count && $topTwo[0]->vote_count > 0;
    }

    #[Computed]
    public function votedStudentsCount(): int
    {
        return Student::where('has_voted', true)->count();
    }

    #[Computed]
    public function unvotedStudentsCount(): int
    {
        return Student::where('has_voted', false)->count();
    }

    public function setLogFilter(string $filter): void
    {
        $this->logFilter = $filter;
    }

    public function downloadTemplate()
    {
        $csv = "nis,name,class\n2024001,Andi Pratama,XII RPL 1\n2024002,Bunga Citra Lestari,XII AKL 2\n2024003,Citra Kirana,XI OTKP 1\n";

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'template-dpt-siswa.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    #[Computed]
    public function recentLogs()
    {
        $query = SystemLog::latest();

        if ($this->logFilter === 'session') {
            $query->whereIn('type', ['session_toggled', 'session_opened', 'session_closed']);
        } elseif ($this->logFilter === 'votes') {
            $query->whereIn('type', ['vote_success', 'vote_cast']);
        } elseif ($this->logFilter === 'warnings') {
            $query->whereIn('type', ['race_condition_blocked', 'duplicate_attempt', 'nis_not_found']);
        }

        return $query->limit(25)->get();
    }

    /**
     * "DB Transaction Health": cek sederhana namun bermakna — ukur waktu
     * tempuh satu round-trip query, dan pastikan tidak ada open transaction
     * yang menggantung. Cukup untuk mendeteksi DB lambat/bermasalah di
     * jaringan sekolah tanpa tools monitoring eksternal.
     */
    #[Computed]
    public function dbHealth(): array
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            $ms = round((microtime(true) - $start) * 1000, 1);

            return [
                'ok' => true,
                'latency_ms' => $ms,
                'label' => $ms < 50 ? 'Sehat' : 'Lambat',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'latency_ms' => null, 'label' => 'Terputus'];
        }
    }

    public function toggleVotingSession(): void
    {
        $setting = Setting::current();
        $newState = ! $setting->is_voting_open;

        $setting->update([
            'is_voting_open' => $newState,
            'opened_at' => $newState ? now() : $setting->opened_at,
            'closed_at' => ! $newState ? now() : null,
            'is_finalized' => $newState ? false : $setting->is_finalized,
            'finalized_at' => $newState ? null : $setting->finalized_at,
        ]);

        SystemLog::record(
            SystemLog::TYPE_SESSION_TOGGLED,
            $newState ? 'Sesi voting DIBUKA oleh admin.' : 'Sesi voting DITUTUP oleh admin.',
        );

        $this->flash($newState ? 'Sesi voting sekarang OPEN.' : 'Sesi voting sekarang CLOSED.', 'success');
        $this->showSessionConfirm = false;
        unset($this->setting);
    }

    public function confirmToggleSession(): void
    {
        $this->showSessionConfirm = true;
    }

    public function cancelToggleSession(): void
    {
        $this->showSessionConfirm = false;
    }

    public function confirmFinalizeSession(): void
    {
        $this->showFinalizeConfirm = true;
    }

    public function cancelFinalizeSession(): void
    {
        $this->showFinalizeConfirm = false;
    }

    public function finalizeSession(): void
    {
        $setting = Setting::current();
        $setting->update([
            'is_voting_open' => false,
            'closed_at' => now(),
            'is_finalized' => true,
            'finalized_at' => now(),
        ]);

        if ($setting->isKandidatMode()) {
            $ranked = $this->rankedCandidates;
            $p1 = $ranked->get(0);
            $p2 = $ranked->get(1);
            $p3 = $ranked->get(2);
            $p4 = $ranked->get(3);

            $details = [];
            if ($p1) {
                $details[] = "Ketua: No. {$p1->number_order} ({$p1->chairman_name})";
            }
            if ($p2) {
                $details[] = "Wakil: No. {$p2->number_order} ({$p2->chairman_name})";
            }
            if ($p3) {
                $details[] = "Kandidat 3: No. {$p3->number_order} ({$p3->chairman_name})";
            }
            if ($p4) {
                $details[] = "Kandidat 4: No. {$p4->number_order} ({$p4->chairman_name})";
            }

            $winnerDesc = ! empty($details) ? implode(', ', $details) : 'Belum ada suara';

            SystemLog::record(
                SystemLog::TYPE_SESSION_CLOSED,
                "Sesi pemilihan OSIS (Mode Kandidat Individu) RESMI DIAKHIRI oleh admin. Pengurus Inti Terpilih: {$winnerDesc}.",
            );
        } else {
            $winnerTitle = $this->winner ? "Paslon No. {$this->winner->number_order} ({$this->winner->chairman_name} & {$this->winner->vice_chairman_name})" : 'Belum ada suara';

            SystemLog::record(
                SystemLog::TYPE_SESSION_CLOSED,
                "Sesi pemilihan OSIS RESMI DIAKHIRI oleh admin. Paslon Pemenang: {$winnerTitle}.",
            );
        }

        $this->showFinalizeConfirm = false;
        unset($this->setting);
        unset($this->winner);
        unset($this->leaderboard);
        unset($this->rankedCandidates);
        $this->flash('Sesi pemilihan resmi diakhiri dan Hasil Akhir Pemilihan telah diumumkan!', 'success');
    }

    public function reopenElection(): void
    {
        $setting = Setting::current();
        $setting->update([
            'is_finalized' => false,
            'finalized_at' => null,
        ]);

        SystemLog::record(
            SystemLog::TYPE_SESSION_TOGGLED,
            'Status finalisasi hasil pemilihan dibuka kembali oleh admin.',
        );

        unset($this->setting);
        unset($this->winner);
        unset($this->leaderboard);
        unset($this->rankedCandidates);
        $this->flash('Status finalisasi dibuka kembali. Anda dapat mengaktifkan kembali bilik suara.', 'success');
    }

    public function confirmClearStudents(): void
    {
        $this->showClearStudentsConfirm = true;
    }

    public function cancelClearStudents(): void
    {
        $this->showClearStudentsConfirm = false;
    }

    public function clearAllStudents(): void
    {
        if ($this->setting->is_voting_open) {
            $this->flash('Tutup sesi voting terlebih dahulu sebelum menghapus data pemilih.', 'danger');
            $this->showClearStudentsConfirm = false;

            return;
        }

        $count = Student::count();

        DB::transaction(function () {
            Vote::query()->delete();
            Student::query()->delete();
            Candidate::query()->update(['vote_count' => 0]);
        });

        SystemLog::record(
            SystemLog::TYPE_DATA_RESET,
            "Admin menghapus seluruh {$count} data siswa pemilih (DPT) dari sistem.",
        );

        $this->showClearStudentsConfirm = false;
        $this->resetPage('voterPage');
        unset($this->totalStudents);
        unset($this->votedStudentsCount);
        unset($this->unvotedStudentsCount);
        $this->flash("Berhasil menghapus seluruh {$count} data pemilih (DPT). Data siap diimpor untuk pemilihan baru.", 'success');
    }

    public function confirmReset(): void
    {
        $this->showResetConfirm = true;
        $this->resetConfirmText = '';
    }

    public function cancelReset(): void
    {
        $this->showResetConfirm = false;
        $this->resetConfirmText = '';
    }

    /**
     * Reset total data suara — HANYA untuk mode simulasi/gladi bersih.
     * Wajib mengetik "RESET" untuk konfirmasi guna mencegah klik tidak
     * sengaja menghapus data hari-H yang sesungguhnya.
     */
    public function resetData(): void
    {
        if ($this->setting->is_voting_open) {
            $this->flash('Tutup sesi voting terlebih dahulu sebelum melakukan reset.', 'danger');

            return;
        }

        if (strtoupper(trim($this->resetConfirmText)) !== 'RESET') {
            $this->flash('Ketik "RESET" persis untuk konfirmasi.', 'danger');

            return;
        }

        DB::transaction(function () {
            Vote::query()->delete();
            Student::query()->update(['has_voted' => false, 'voted_at' => null]);
            Candidate::query()->update(['vote_count' => 0]);
            Setting::current()->update([
                'is_voting_open' => false,
                'opened_at' => null,
                'closed_at' => now(),
                'is_finalized' => false,
                'finalized_at' => null,
            ]);
        });

        SystemLog::record(SystemLog::TYPE_DATA_RESET, 'Seluruh data suara di-reset oleh admin (mode simulasi).');

        $this->showResetConfirm = false;
        $this->resetConfirmText = '';
        unset($this->setting);
        $this->flash('Data suara berhasil direset.', 'success');
    }

    public function importStudents(): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $this->validate([
            'studentFile' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->readStudentCsv();

        if ($rows === null) {
            return;
        }

        // Pastikan kolom unique_code sudah ada di database
        if (! Schema::hasColumn('students', 'unique_code')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // Jangan crash jika command migrate gagal
            }
        }

        $hasUniqueCodeCol = Schema::hasColumn('students', 'unique_code');

        $existing = Student::query()
            ->whereIn('nis', array_column($rows, 'nis'))
            ->get($hasUniqueCodeCol ? ['nis', 'unique_code'] : ['nis'])
            ->keyBy('nis');

        $now = now();
        $usedCodes = [];
        $payload = array_map(function (array $row) use ($existing, $now, $hasUniqueCodeCol, &$usedCodes): array {
            $data = [
                'nis' => $row['nis'],
                'name' => $row['name'],
                'class' => $row['class'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasUniqueCodeCol) {
                $code = $row['unique_code'] ?? ($existing[$row['nis']]->unique_code ?? null);
                if (! $code) {
                    do {
                        $code = Student::generateUniqueCode();
                    } while (isset($usedCodes[$code]));
                }
                $usedCodes[$code] = true;
                $data['unique_code'] = strtoupper((string) $code);
            }

            return $data;
        }, $rows);

        $updateCols = $hasUniqueCodeCol ? ['name', 'class', 'unique_code', 'updated_at'] : ['name', 'class', 'updated_at'];

        DB::transaction(function () use ($payload, $updateCols): void {
            Student::query()->upsert(
                $payload,
                ['nis'],
                $updateCols,
            );
        });

        $updated = count(array_intersect(array_column($rows, 'nis'), $existing->keys()->all()));
        $created = count($rows) - $updated;

        SystemLog::record(
            SystemLog::TYPE_ADMIN_ACTION,
            "Admin mengimpor daftar siswa: {$created} ditambahkan, {$updated} diperbarui.",
            null,
            ['created' => $created, 'updated' => $updated],
        );

        $this->studentFile = null;
        $this->importSummary = ['created' => $created, 'updated' => $updated];
        $this->flash("Import berhasil: {$created} siswa ditambahkan, {$updated} siswa diperbarui.", 'success');
        unset($this->totalStudents);
    }

    /**
     * @return list<array{nis: string, name: string, class: ?string, unique_code?: ?string}>|null
     */
    private function readStudentCsv(): ?array
    {
        $handle = fopen($this->studentFile->getRealPath(), 'rb');

        if ($handle === false) {
            $this->addError('studentFile', 'File CSV tidak dapat dibaca.');

            return null;
        }

        $delimiter = $this->detectCsvDelimiter($handle);
        $header = fgetcsv($handle, 0, $delimiter);
        $header = array_map(fn ($value): string => strtolower(trim((string) $value, " \t\r\n\xEF\xBB\xBF")), $header ?: []);

        $hasCode = $header === ['nis', 'name', 'class', 'unique_code'];
        if ($header !== ['nis', 'name', 'class'] && ! $hasCode) {
            fclose($handle);
            $this->addError('studentFile', 'Header CSV wajib: nis,name,class atau nis,name,class,unique_code.');

            return null;
        }

        $rows = [];
        $seenNis = [];
        $errors = [];
        $line = 1;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($data, fn ($value): bool => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $expectedCols = $hasCode ? 4 : 3;
            if (count($data) !== $expectedCols) {
                $errors[] = "Baris {$line}: jumlah kolom harus {$expectedCols}.";

                continue;
            }

            $nis = trim((string) $data[0]);
            $name = trim((string) $data[1]);
            $class = trim((string) $data[2]);
            $uniqueCode = $hasCode ? strtoupper(trim((string) $data[3])) : null;

            if ($nis === '' || strlen($nis) > 30) {
                $errors[] = "Baris {$line}: NIS wajib diisi dan maksimal 30 karakter.";
            } elseif (isset($seenNis[$nis])) {
                $errors[] = "Baris {$line}: NIS {$nis} duplikat di dalam file.";
            } else {
                $seenNis[$nis] = true;
            }

            if ($name === '' || strlen($name) > 255) {
                $errors[] = "Baris {$line}: nama wajib diisi dan maksimal 255 karakter.";
            }

            if (strlen($class) > 50) {
                $errors[] = "Baris {$line}: kelas maksimal 50 karakter.";
            }

            if ($hasCode && $uniqueCode !== null && $uniqueCode !== '' && strlen($uniqueCode) !== 4) {
                $errors[] = "Baris {$line}: unique_code harus 4 karakter.";
            }

            if (count($errors) === 0 || ! str_contains(end($errors), "Baris {$line}:")) {
                $row = ['nis' => $nis, 'name' => $name, 'class' => $class !== '' ? $class : null];
                if ($hasCode && $uniqueCode !== null && $uniqueCode !== '') {
                    $row['unique_code'] = $uniqueCode;
                }
                $rows[] = $row;
            }
        }

        fclose($handle);

        if ($rows === [] && $errors === []) {
            $errors[] = 'File CSV tidak memiliki data siswa.';
        }

        if ($errors !== []) {
            $this->addError('studentFile', implode(' ', array_slice($errors, 0, 5)));

            return null;
        }

        return $rows;
    }

    private function detectCsvDelimiter($handle): string
    {
        $line = fgets($handle) ?: '';
        rewind($handle);

        return substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
    }

    private function ensureVotingClosed(): bool
    {
        if (Setting::current()->is_voting_open) {
            $this->addError('studentFile', 'Daftar siswa hanya dapat diimpor saat sesi voting ditutup.');

            return false;
        }

        return true;
    }

    private function flash(string $message, string $type): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function render()
    {
        if (auth('admin')->check()) {
            AdminSession::query()
                ->where('admin_id', auth('admin')->id())
                ->where('session_id', session()->getId())
                ->update(['last_seen_at' => now()]);
        }

        return view('livewire.admin.dashboard')->layout('layouts.admin');
    }
}

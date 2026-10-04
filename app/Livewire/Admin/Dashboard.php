<?php

namespace App\Livewire\Admin;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    use WithFileUploads;

    public bool $showResetConfirm = false;

    public bool $showSessionConfirm = false;

    public string $resetConfirmText = '';

    public ?string $flashMessage = null;

    public ?string $flashType = null; // success | danger

    public ?array $importSummary = null;

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
            ->get()
            ->map(function (Candidate $c) use ($total) {
                $c->computed_percentage = $c->percentage($total);

                return $c;
            });
    }

    public string $logFilter = 'all'; // all | session | votes | warnings

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

        $existingNis = Student::query()
            ->whereIn('nis', array_column($rows, 'nis'))
            ->pluck('nis')
            ->all();

        $now = now();
        $payload = array_map(fn (array $row): array => [
            'nis' => $row['nis'],
            'name' => $row['name'],
            'class' => $row['class'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows);

        DB::transaction(function () use ($payload): void {
            Student::query()->upsert(
                $payload,
                ['nis'],
                ['name', 'class', 'updated_at'],
            );
        });

        $updated = count(array_intersect(array_column($rows, 'nis'), $existingNis));
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
     * @return list<array{nis: string, name: string, class: ?string}>|null
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

        if ($header !== ['nis', 'name', 'class']) {
            fclose($handle);
            $this->addError('studentFile', 'Header CSV wajib persis: nis,name,class.');

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

            if (count($data) !== 3) {
                $errors[] = "Baris {$line}: jumlah kolom harus 3.";

                continue;
            }

            [$nis, $name, $class] = array_map(fn ($value): string => trim((string) $value), $data);

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

            if (count($errors) === 0 || ! str_contains(end($errors), "Baris {$line}:")) {
                $rows[] = ['nis' => $nis, 'name' => $name, 'class' => $class !== '' ? $class : null];
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
        return view('livewire.admin.dashboard')->layout('layouts.admin');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\Vote;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Export seluruh data suara masuk ke CSV lengkap dengan Berita Acara Rekapitulasi.
     * Menggunakan StreamedResponse agar aman untuk skala ribuan data.
     */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'rekapitulasi-resmi-voting-osis-'.now()->format('Y-m-d_His').'.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM agar Microsoft Excel membaca encoding UTF-8 dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            $setting = Setting::current();
            $totalDpt = Student::count();
            $totalVotes = Vote::count();
            $totalUnvoted = Student::where('has_voted', false)->count();
            $participationRate = $totalDpt > 0 ? round(($totalVotes / $totalDpt) * 100, 2) : 0.0;

            // 1. Header Berita Acara
            fputcsv($handle, ['BERITA ACARA REKAPITULASI HASIL PEMILIHAN KETUA & WAKIL OSIS']);
            fputcsv($handle, ['SMK AN-NURMANIYAH - PERIODE 2025/2026']);
            fputcsv($handle, ['Waktu Dokumen Dibuat', now()->format('d F Y, H:i:s').' WIB']);
            fputcsv($handle, ['Status Sesi', $setting->is_finalized ? 'FINAL (Pemenang Ditetapkan)' : ($setting->is_voting_open ? 'SEDANG BERJALAN (OPEN)' : 'DITUTUP (CLOSED)')]);
            fputcsv($handle, []);

            // 2. Ringkasan Partisipasi
            fputcsv($handle, ['--- RINGKASAN TINGKAT PARTISIPASI PEMILIH ---']);
            fputcsv($handle, ['Kategori', 'Jumlah', 'Persentase']);
            fputcsv($handle, ['Total Daftar Pemilih Tetap (DPT)', $totalDpt, '100%']);
            fputcsv($handle, ['Total Suara Sah Masuk', $totalVotes, $participationRate.'%']);
            fputcsv($handle, ['Total Belum Memilih (Golput/Absen)', $totalUnvoted, ($totalDpt > 0 ? round(($totalUnvoted / $totalDpt) * 100, 2) : 0).'%']);
            fputcsv($handle, []);

            // 3. Rekapitulasi Perolehan Suara Calon
            fputcsv($handle, ['--- REKAPITULASI HASIL PEROLEHAN SUARA PASLON ---']);
            fputcsv($handle, ['No. Urut', 'Pasangan Calon (Ketua & Wakil)', 'Slogan', 'Perolehan Suara Sah', 'Persentase', 'Status']);

            $candidates = Candidate::orderBy('number_order')->get();
            $maxVotes = $candidates->max('vote_count') ?? 0;

            foreach ($candidates as $c) {
                // Verifikasi hitungan langsung dari relasi tabel votes
                $actualVotes = Vote::where('candidate_id', $c->id)->count();
                $pct = $totalVotes > 0 ? round(($actualVotes / $totalVotes) * 100, 2) : 0.0;
                $status = ($actualVotes === $maxVotes && $maxVotes > 0 && $setting->is_finalized) ? 'TERPILIH (PEMENANG)' : ($actualVotes === $maxVotes && $maxVotes > 0 ? 'MEMIMPIN' : '-');

                fputcsv($handle, [
                    sprintf("No. %02d", $c->number_order),
                    "{$c->chairman_name} & {$c->vice_chairman_name}",
                    $c->slogan ?: '-',
                    $actualVotes,
                    $pct.'%',
                    $status,
                ]);
            }
            fputcsv($handle, []);

            // 4. Rincian Audit Seluruh Suara Masuk Riil
            fputcsv($handle, ['--- RINCIAN AUDIT LOG TRANSAKSI SUARA MASUK (HASIL RIIL DARI BILIK SUARA) ---']);
            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'No. Urut Paslon Dipilih', 'Paslon Terpilih', 'Waktu Memilih', 'IP Address Bilik']);

            Vote::with(['student', 'candidate'])
                ->orderBy('voted_at')
                ->chunk(200, function ($votes) use ($handle) {
                    static $no = 0;
                    foreach ($votes as $vote) {
                        $no++;
                        fputcsv($handle, [
                            $no,
                            $vote->student->nis ?? '-',
                            $vote->student->name ?? '-',
                            $vote->student->class ?? '-',
                            $vote->candidate ? sprintf("No. %02d", $vote->candidate->number_order) : '-',
                            $vote->candidate ? "{$vote->candidate->chairman_name} & {$vote->candidate->vice_chairman_name}" : '-',
                            optional($vote->voted_at)->format('Y-m-d H:i:s'),
                            $vote->ip_address,
                        ]);
                    }
                });

            fclose($handle);
        };

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, 'Admin mengekspor laporan berita acara rekapitulasi voting (CSV).');

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export daftar siswa yang belum menggunakan hak pilih (Belum Voting).
     * Sangat berguna bagi panitia untuk memanggil siswa di kelas.
     */
    public function exportUnvotedCsv(): StreamedResponse
    {
        $filename = 'daftar-siswa-belum-voting-'.now()->format('Y-m-d_His').'.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DAFTAR SISWA YANG BELUM MENGGUNAKAN HAK SUARA (BELUM VOTING)']);
            fputcsv($handle, ['SMK AN-NURMANIYAH - E-VOTING OSIS']);
            fputcsv($handle, ['Waktu Unduh', now()->format('d F Y, H:i:s').' WIB']);
            fputcsv($handle, []);

            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Token / Kode Unik', 'Status Kehadiran']);

            Student::where('has_voted', false)
                ->orderBy('class')
                ->orderBy('name')
                ->chunk(200, function ($students) use ($handle) {
                    static $no = 0;
                    foreach ($students as $student) {
                        $no++;
                        fputcsv($handle, [
                            $no,
                            $student->nis,
                            $student->name,
                            $student->class ?? 'Tanpa Kelas',
                            $student->unique_code ?? '-',
                            'Belum Memilih',
                        ]);
                    }
                });

            fclose($handle);
        };

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, 'Admin mengekspor daftar siswa yang belum memilih (CSV).');

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export seluruh DPT lengkap dengan Token / Kode Unik (untuk distribusi panitia ke siswa).
     */
    public function exportDptCsv(): StreamedResponse
    {
        $filename = 'dpt-dan-token-pemilih-'.now()->format('Y-m-d_His').'.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DAFTAR PEMILIH TETAP (DPT) & TOKEN BILIK SUARA']);
            fputcsv($handle, ['SMK AN-NURMANIYAH - E-VOTING OSIS']);
            fputcsv($handle, ['Waktu Unduh', now()->format('d F Y, H:i:s').' WIB']);
            fputcsv($handle, []);

            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Token / Kode Unik', 'Status Hak Suara', 'Waktu Memilih']);

            Student::query()
                ->orderBy('class')
                ->orderBy('name')
                ->chunk(200, function ($students) use ($handle) {
                    static $no = 0;
                    foreach ($students as $student) {
                        $no++;
                        fputcsv($handle, [
                            $no,
                            $student->nis,
                            $student->name,
                            $student->class ?? '-',
                            $student->unique_code ?? '-',
                            $student->has_voted ? 'Sudah Memilih' : 'Belum Memilih',
                            $student->voted_at ? $student->voted_at->format('Y-m-d H:i:s') : '-',
                        ]);
                    }
                });

            fclose($handle);
        };

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, 'Admin mengekspor daftar DPT beserta token unik pemilih (CSV).');

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

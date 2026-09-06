<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\Vote;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Export seluruh data suara masuk ke CSV.
     * Menggunakan StreamedResponse (bukan generate string besar di memori)
     * supaya aman meski jumlah pemilih ribuan siswa.
     */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'laporan-voting-osis-'.now()->format('Y-m-d_His').'.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 (nama siswa dengan karakter khusus) dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Paslon Dipilih', 'No. Urut Paslon', 'Waktu Memilih', 'IP Address']);

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
                            $vote->candidate->pair_name ?? '-',
                            $vote->candidate->number_order ?? '-',
                            optional($vote->voted_at)->format('Y-m-d H:i:s'),
                            $vote->ip_address,
                        ]);
                    }
                });

            fclose($handle);
        };

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, 'Admin mengekspor laporan hasil voting (CSV).');

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

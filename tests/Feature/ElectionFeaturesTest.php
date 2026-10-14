<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ElectionFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);
    }

    public function test_export_csv_contains_official_minutes_and_accurate_votes(): void
    {
        $candidate1 = Candidate::create([
            'number_order' => 1,
            'chairman_name' => 'Fauzi',
            'vice_chairman_name' => 'Nadia',
            'slogan' => 'Maju Bersama',
            'vision' => 'Visi Paslon 1',
            'mission' => ['Misi 1', 'Misi 2'],
        ]);

        $candidate2 = Candidate::create([
            'number_order' => 2,
            'chairman_name' => 'Budi',
            'vice_chairman_name' => 'Dewi',
            'slogan' => 'Kreatif Inovatif',
            'vision' => 'Visi Paslon 2',
            'mission' => ['Misi A', 'Misi B'],
        ]);

        $student1 = Student::create(['nis' => '1001', 'name' => 'Siswa Satu', 'class' => 'XII RPL 1']);
        $student2 = Student::create(['nis' => '1002', 'name' => 'Siswa Dua', 'class' => 'XII RPL 2']);
        $student3 = Student::create(['nis' => '1003', 'name' => 'Siswa Tiga', 'class' => 'XI AKL 1']);

        // Siswa 1 memilih Paslon 1
        Vote::create([
            'student_id' => $student1->id,
            'candidate_id' => $candidate1->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'voted_at' => now(),
        ]);
        $student1->has_voted = true;
        $student1->voted_at = now();
        $student1->save();
        $candidate1->increment('vote_count');

        // Siswa 2 memilih Paslon 1
        Vote::create([
            'student_id' => $student2->id,
            'candidate_id' => $candidate1->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'voted_at' => now(),
        ]);
        $student2->has_voted = true;
        $student2->voted_at = now();
        $student2->save();
        $candidate1->increment('vote_count');

        // Finalisasi sesi
        $setting = Setting::current();
        $setting->update([
            'is_voting_open' => false,
            'is_finalized' => true,
            'finalized_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports/export');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Cek header berita acara
        $this->assertStringContainsString('BERITA ACARA REKAPITULASI HASIL PEMILIHAN KETUA & WAKIL OSIS', $content);
        $this->assertStringContainsString('RINGKASAN TINGKAT PARTISIPASI PEMILIH', $content);
        $this->assertStringContainsString('REKAPITULASI HASIL PEROLEHAN SUARA PASLON', $content);
        $this->assertStringContainsString('RINCIAN AUDIT LOG TRANSAKSI SUARA MASUK', $content);

        // Cek kandidat & suara
        $this->assertStringContainsString('Fauzi & Nadia', $content);
        $this->assertStringContainsString('Budi & Dewi', $content);
        $this->assertStringContainsString('TERPILIH (PEMENANG)', $content);

        // Cek pemilih
        $this->assertStringContainsString('Siswa Satu', $content);
        $this->assertStringContainsString('Siswa Dua', $content);
    }

    public function test_export_unvoted_csv_contains_only_unvoted_students(): void
    {
        $student1 = Student::create(['nis' => '2001', 'name' => 'Siswa Belum Pilih', 'class' => 'X RPL 1']);
        $student2 = Student::create(['nis' => '2002', 'name' => 'Siswa Sudah Pilih', 'class' => 'X RPL 2']);

        $student2->has_voted = true;
        $student2->voted_at = now();
        $student2->save();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports/export-unvoted');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('DAFTAR SISWA YANG BELUM MENGGUNAKAN HAK SUARA', $content);
        $this->assertStringContainsString('Siswa Belum Pilih', $content);
        $this->assertStringNotContainsString('Siswa Sudah Pilih', $content);
    }

    public function test_livewire_dashboard_finalize_and_reopen_election(): void
    {
        $candidate = Candidate::create([
            'number_order' => 1,
            'chairman_name' => 'Fauzi',
            'vice_chairman_name' => 'Nadia',
            'vision' => 'Visi Paslon 1',
            'mission' => ['Misi 1'],
        ]);
        $candidate->vote_count = 5;
        $candidate->save();

        Setting::current()->update(['is_voting_open' => true]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(Dashboard::class)
            ->call('confirmFinalizeSession')
            ->assertSet('showFinalizeConfirm', true)
            ->call('finalizeSession')
            ->assertSet('showFinalizeConfirm', false);

        $setting = Setting::current();
        $this->assertTrue($setting->is_finalized);
        $this->assertFalse($setting->is_voting_open);
        $this->assertNotNull($setting->finalized_at);

        // Buka kembali
        Livewire::actingAs($this->admin, 'admin')
            ->test(Dashboard::class)
            ->call('reopenElection');

        $setting->refresh();
        $this->assertFalse($setting->is_finalized);
    }

    public function test_livewire_dashboard_voter_tab_and_search_filter(): void
    {
        $student1 = Student::create(['nis' => '3001', 'name' => 'Ahmad Dani', 'class' => 'XII RPL 1']);
        $student2 = Student::create(['nis' => '3002', 'name' => 'Budi Santoso', 'class' => 'XII TKJ 2']);

        $student2->has_voted = true;
        $student2->voted_at = now();
        $student2->save();

        // Cek tab default 'unvoted'
        Livewire::actingAs($this->admin, 'admin')
            ->test(Dashboard::class)
            ->assertSet('voterTab', 'unvoted')
            ->assertSee('Ahmad Dani')
            ->assertDontSee('Budi Santoso')
            // Ganti ke 'voted'
            ->set('voterTab', 'voted')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ahmad Dani')
            // Pencarian
            ->set('voterTab', 'all')
            ->set('voterSearch', 'Ahmad')
            ->assertSee('Ahmad Dani')
            ->assertDontSee('Budi Santoso');
    }

    public function test_livewire_dashboard_filter_by_angkatan_and_pagination(): void
    {
        // Buat 15 siswa: 5 di kelas X, 5 di kelas XI, 5 di kelas XII
        for ($i = 1; $i <= 5; $i++) {
            Student::create(['nis' => "100{$i}", 'name' => "Siswa X-{$i}", 'class' => 'X RPL 1']);
            Student::create(['nis' => "200{$i}", 'name' => "Siswa XI-{$i}", 'class' => 'XI TKJ 1']);
            Student::create(['nis' => "300{$i}", 'name' => "Siswa XII-{$i}", 'class' => 'XII MM 1']);
        }

        // Test filter angkatan X
        Livewire::actingAs($this->admin, 'admin')
            ->test(Dashboard::class)
            ->set('voterTab', 'all')
            ->set('voterGrade', 'X')
            ->assertSee('Siswa X-1')
            ->assertDontSee('Siswa XI-1')
            ->assertDontSee('Siswa XII-1')
            // Test filter angkatan XI
            ->set('voterGrade', 'XI')
            ->assertSee('Siswa XI-1')
            ->assertDontSee('Siswa X-1')
            ->assertDontSee('Siswa XII-1')
            // Test filter angkatan XII
            ->set('voterGrade', 'XII')
            ->assertSee('Siswa XII-1')
            ->assertDontSee('Siswa X-1')
            ->assertDontSee('Siswa XI-1')
            // Test pagination per-page = 5 baris
            ->set('voterGrade', '')
            ->set('voterPerPage', 5)
            ->assertSet('voterPerPage', 5);
    }

    public function test_livewire_dashboard_clear_all_students_when_session_closed(): void
    {
        $candidate = Candidate::create([
            'number_order' => 1,
            'chairman_name' => 'Fauzi',
            'vice_chairman_name' => 'Nadia',
            'vision' => 'Visi',
            'mission' => ['Misi'],
        ]);
        $candidate->vote_count = 1;
        $candidate->save();

        $student = Student::create(['nis' => '4001', 'name' => 'Siswa Test', 'class' => 'XII RPL 1']);
        $student->has_voted = true;
        $student->voted_at = now();
        $student->save();

        Vote::create([
            'student_id' => $student->id,
            'candidate_id' => $candidate->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'voted_at' => now(),
        ]);

        $this->assertEquals(1, Student::count());
        $this->assertEquals(1, Vote::count());
        $this->assertEquals(1, $candidate->fresh()->vote_count);

        // Jika sesi voting masih buka, penghapusan ditolak
        Setting::current()->update(['is_voting_open' => true]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(Dashboard::class)
            ->call('confirmClearStudents')
            ->assertSet('showClearStudentsConfirm', true)
            ->call('clearAllStudents')
            ->assertSet('showClearStudentsConfirm', false);

        $this->assertEquals(1, Student::count(), 'Siswa tidak boleh dihapus saat sesi voting sedang aktif.');

        // Saat sesi voting ditutup, penghapusan berhasil
        Setting::current()->update(['is_voting_open' => false]);

        Livewire::actingAs($this->admin, 'admin')
            ->test(Dashboard::class)
            ->call('confirmClearStudents')
            ->assertSet('showClearStudentsConfirm', true)
            ->call('clearAllStudents')
            ->assertSet('showClearStudentsConfirm', false);

        $this->assertEquals(0, Student::count());
        $this->assertEquals(0, Vote::count());
        $this->assertEquals(0, $candidate->fresh()->vote_count);
    }
}



<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Voter\VotingBooth;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DualModeElectionTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);
    }

    public function test_kandidat_mode_leaderboard_projection_badges(): void
    {
        Setting::current()->update([
            'voting_mode' => 'kandidat',
            'is_voting_open' => true,
        ]);

        $c1 = Candidate::create([
            'number_order' => 1,
            'type' => 'kandidat',
            'chairman_name' => 'Ahmad Fathir',
            'vision' => 'Visi 1',
            'mission' => ['Misi 1'],
            'is_active' => true,
        ]);
        $c2 = Candidate::create([
            'number_order' => 2,
            'type' => 'kandidat',
            'chairman_name' => 'Bunga Citra',
            'vision' => 'Visi 2',
            'mission' => ['Misi 2'],
            'is_active' => true,
        ]);
        $c3 = Candidate::create([
            'number_order' => 3,
            'type' => 'kandidat',
            'chairman_name' => 'Chandra Wijaya',
            'vision' => 'Visi 3',
            'mission' => ['Misi 3'],
            'is_active' => true,
        ]);
        $c4 = Candidate::create([
            'number_order' => 4,
            'type' => 'kandidat',
            'chairman_name' => 'Dinda Putri',
            'vision' => 'Visi 4',
            'mission' => ['Misi 4'],
            'is_active' => true,
        ]);

        // Berikan vote count
        $c1->vote_count = 50;
        $c1->save();
        $c2->vote_count = 30;
        $c2->save();
        $c3->vote_count = 15;
        $c3->save();
        $c4->vote_count = 5;
        $c4->save();

        $this->actingAs($this->admin, 'admin');

        // Saat OPEN: Peringkat 1 = Calon Ketua, Peringkat 2 = Calon Wakil
        Livewire::test(Dashboard::class)
            ->assertSee('Peringkat 1 • Calon Ketua OSIS')
            ->assertSee('Peringkat 2 • Calon Wakil Ketua OSIS')
            ->assertSee('Peringkat 3 • Kandidat Ketiga')
            ->assertSee('Peringkat 4 • Kandidat Keempat');

        // Saat CLOSED: Berubah menjadi Terpilih
        Setting::current()->update(['is_voting_open' => false]);

        Livewire::test(Dashboard::class)
            ->assertSee('Peringkat 1 • Terpilih Ketua OSIS')
            ->assertSee('Peringkat 2 • Terpilih Wakil Ketua OSIS')
            ->assertSee('Peringkat 3 • Kandidat Ketiga')
            ->assertSee('Peringkat 4 • Kandidat Keempat');
    }

    public function test_kandidat_mode_winner_banner_spotlight(): void
    {
        Setting::current()->update([
            'voting_mode' => 'kandidat',
            'is_voting_open' => false,
            'is_finalized' => true,
            'finalized_at' => now(),
        ]);

        $c1 = Candidate::create([
            'number_order' => 1,
            'type' => 'kandidat',
            'chairman_name' => 'Ahmad Fathir',
            'vision' => 'Visi 1',
            'mission' => ['Misi 1'],
            'is_active' => true,
        ]);
        $c2 = Candidate::create([
            'number_order' => 2,
            'type' => 'kandidat',
            'chairman_name' => 'Bunga Citra',
            'vision' => 'Visi 2',
            'mission' => ['Misi 2'],
            'is_active' => true,
        ]);
        $c3 = Candidate::create([
            'number_order' => 3,
            'type' => 'kandidat',
            'chairman_name' => 'Chandra Wijaya',
            'vision' => 'Visi 3',
            'mission' => ['Misi 3'],
            'is_active' => true,
        ]);
        $c4 = Candidate::create([
            'number_order' => 4,
            'type' => 'kandidat',
            'chairman_name' => 'Dinda Putri',
            'vision' => 'Visi 4',
            'mission' => ['Misi 4'],
            'is_active' => true,
        ]);

        // Simulasikan 10 suara
        $student = Student::create([
            'nis' => '2024001',
            'name' => 'Pemilih Test',
            'class' => 'XII RPL',
            'unique_code' => 'ABCD',
        ]);
        Vote::create([
            'student_id' => $student->id,
            'candidate_id' => $c1->id,
            'ip_address' => '127.0.0.1',
            'voted_at' => now(),
        ]);

        $c1->vote_count = 60;
        $c1->save();
        $c2->vote_count = 40;
        $c2->save();
        $c3->vote_count = 20;
        $c3->save();
        $c4->vote_count = 10;
        $c4->save();

        $this->actingAs($this->admin, 'admin');

        Livewire::test(Dashboard::class)
            ->assertSee('PENGURUS INTI TERPILIH — KETUA & WAKIL OSIS')
            ->assertSee('Ahmad Fathir (Ketua)')
            ->assertSee('Bunga Citra (Wakil)')
            ->assertSee('Chandra Wijaya')
            ->assertSee('Dinda Putri')
            ->assertSee('Peringkat 3 • Kandidat Ketiga')
            ->assertSee('Peringkat 4 • Kandidat Keempat');
    }

    public function test_kandidat_mode_winner_banner_handles_edge_cases_safely(): void
    {
        Setting::current()->update([
            'voting_mode' => 'kandidat',
            'is_voting_open' => false,
            'is_finalized' => true,
        ]);

        $this->actingAs($this->admin, 'admin');

        // Edge Case 1: Tidak ada kandidat sama sekali & 0 suara
        Livewire::test(Dashboard::class)
            ->assertSee('Sesi voting telah diakhiri, namun belum ada suara yang masuk.');

        // Edge Case 2: Hanya ada 1 kandidat dengan 1 suara
        $c1 = Candidate::create([
            'number_order' => 1,
            'type' => 'kandidat',
            'chairman_name' => 'Solo Candidate',
            'vision' => 'Visi Solo',
            'mission' => ['Misi Solo'],
            'is_active' => true,
        ]);
        $student = Student::create([
            'nis' => '2024099',
            'name' => 'Voter Solo',
            'unique_code' => 'SOLO',
        ]);
        Vote::create([
            'student_id' => $student->id,
            'candidate_id' => $c1->id,
            'ip_address' => '127.0.0.1',
            'voted_at' => now(),
        ]);
        $c1->vote_count = 1;
        $c1->save();

        // Tidak boleh crash / undefined offset saat tidak ada rank 2, 3, 4
        Livewire::test(Dashboard::class)
            ->assertSee('Solo Candidate (Ketua)')
            ->assertDontSee('undefined offset');
    }

    public function test_finalize_session_logs_kandidat_mode_correctly(): void
    {
        Setting::current()->update([
            'voting_mode' => 'kandidat',
            'is_voting_open' => true,
            'is_finalized' => false,
        ]);

        $c1 = Candidate::create([
            'number_order' => 1,
            'type' => 'kandidat',
            'chairman_name' => 'Juara Satu',
            'vision' => 'Visi 1',
            'mission' => ['Misi 1'],
            'is_active' => true,
        ]);
        $c2 = Candidate::create([
            'number_order' => 2,
            'type' => 'kandidat',
            'chairman_name' => 'Juara Dua',
            'vision' => 'Visi 2',
            'mission' => ['Misi 2'],
            'is_active' => true,
        ]);

        $student = Student::create([
            'nis' => '2024055',
            'name' => 'Pemilih',
            'unique_code' => 'XYZW',
        ]);
        Vote::create([
            'student_id' => $student->id,
            'candidate_id' => $c1->id,
            'ip_address' => '127.0.0.1',
            'voted_at' => now(),
        ]);
        $c1->vote_count = 10;
        $c1->save();
        $c2->vote_count = 5;
        $c2->save();

        $this->actingAs($this->admin, 'admin');

        Livewire::test(Dashboard::class)
            ->call('finalizeSession');

        $this->assertDatabaseHas('system_logs', [
            'type' => SystemLog::TYPE_SESSION_CLOSED,
        ]);

        $log = SystemLog::where('type', SystemLog::TYPE_SESSION_CLOSED)->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Mode Kandidat Individu', $log->message);
        $this->assertStringContainsString('Ketua: No. 1 (Juara Satu)', $log->message);
        $this->assertStringContainsString('Wakil: No. 2 (Juara Dua)', $log->message);
    }

    public function test_csv_report_export_adjusts_for_kandidat_mode(): void
    {
        Setting::current()->update([
            'voting_mode' => 'kandidat',
            'is_voting_open' => false,
            'is_finalized' => true,
        ]);

        $c1 = Candidate::create([
            'number_order' => 1,
            'type' => 'kandidat',
            'chairman_name' => 'Ketua Terpilih',
            'vision' => 'Visi',
            'mission' => ['Misi'],
            'is_active' => true,
        ]);
        $c2 = Candidate::create([
            'number_order' => 2,
            'type' => 'kandidat',
            'chairman_name' => 'Wakil Terpilih',
            'vision' => 'Visi',
            'mission' => ['Misi'],
            'is_active' => true,
        ]);

        $student1 = Student::create([
            'nis' => '2024011',
            'name' => 'Siswa Voter 1',
            'class' => 'XII AKL',
            'unique_code' => 'CSV1',
        ]);
        $student2 = Student::create([
            'nis' => '2024012',
            'name' => 'Siswa Voter 2',
            'class' => 'XII AKL',
            'unique_code' => 'CSV2',
        ]);
        Vote::create([
            'student_id' => $student1->id,
            'candidate_id' => $c1->id,
            'ip_address' => '127.0.0.1',
            'voted_at' => now(),
        ]);
        Vote::create([
            'student_id' => $student2->id,
            'candidate_id' => $c2->id,
            'ip_address' => '127.0.0.1',
            'voted_at' => now(),
        ]);
        $c1->vote_count = 10;
        $c1->save();
        $c2->vote_count = 5;
        $c2->save();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.reports.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('BERITA ACARA REKAPITULASI HASIL PEMILIHAN KANDIDAT INDIVIDU OSIS', $content);
        $this->assertStringContainsString('Nama Lengkap Kandidat', $content);
        $this->assertStringContainsString('TERPILIH (KETUA OSIS)', $content);
        $this->assertStringContainsString('TERPILIH (WAKIL KETUA OSIS)', $content);
    }

    public function test_voting_booth_displays_kandidat_mode_properly(): void
    {
        Setting::current()->update([
            'voting_mode' => 'kandidat',
            'is_voting_open' => true,
        ]);

        Candidate::create([
            'number_order' => 1,
            'type' => 'kandidat',
            'chairman_name' => 'Rizky Pratama',
            'vision' => 'Visi Rizky',
            'mission' => ['Misi Rizky'],
            'is_active' => true,
        ]);

        $student = Student::create([
            'nis' => '2024088',
            'name' => 'Bayu Ramadhan',
            'class' => 'X RPL 1',
            'unique_code' => 'BYTE',
        ]);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024088')
            ->set('unique_code', 'BYTE')
            ->call('verifyNis')
            ->assertSet('verified', true)
            ->assertSee('Pilih Kandidat OSIS')
            ->assertSee('Rizky Pratama')
            ->assertSee('KANDIDAT INDIVIDU')
            ->assertSee('Pilih Kandidat Ini');
    }
}

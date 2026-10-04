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

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superadmin;

    private Admin $regularAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = Admin::create([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $this->regularAdmin = Admin::create([
            'name' => 'Regular Admin',
            'username' => 'admin_biasa',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_superadmin_can_view_admin_list(): void
    {
        $response = $this->actingAs($this->superadmin, 'admin')
            ->get('/admin/management');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Admin');
        $response->assertSee('superadmin');
        $response->assertSee('admin_biasa');
    }

    public function test_regular_admin_forbidden_from_admin_management(): void
    {
        $response = $this->actingAs($this->regularAdmin, 'admin')
            ->get('/admin/management');

        $response->assertStatus(403);
    }

    public function test_superadmin_can_create_new_admin(): void
    {
        $response = $this->actingAs($this->superadmin, 'admin')
            ->post('/admin/management', [
                'name' => 'Admin Baru',
                'username' => 'admin_baru',
                'password' => 'secret1234',
                'password_confirmation' => 'secret1234',
                'role' => 'admin',
            ]);

        $response->assertRedirect('/admin/management');
        $this->assertDatabaseHas('admins', [
            'username' => 'admin_baru',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_update_password(): void
    {
        $response = $this->actingAs($this->superadmin, 'admin')
            ->post('/admin/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('status');

        $this->superadmin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->superadmin->password));
    }

    public function test_admin_cannot_update_password_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->superadmin, 'admin')
            ->from('/admin/password')
            ->post('/admin/password', [
                'current_password' => 'wrong_password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect('/admin/password');
        $response->assertSessionHasErrors('current_password');
    }

    public function test_admin_can_export_csv_report(): void
    {
        $response = $this->actingAs($this->superadmin, 'admin')
            ->get('/admin/reports/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_toggle_voting_session_in_dashboard(): void
    {
        Setting::current()->update(['is_voting_open' => false]);

        $this->actingAs($this->superadmin, 'admin');

        Livewire::test(Dashboard::class)
            ->call('toggleVotingSession');

        $this->assertTrue(Setting::current()->is_voting_open);

        Livewire::test(Dashboard::class)
            ->call('toggleVotingSession');

        $this->assertFalse(Setting::current()->is_voting_open);
    }

    public function test_admin_can_download_dpt_template(): void
    {
        $this->actingAs($this->superadmin, 'admin');

        Livewire::test(Dashboard::class)
            ->call('downloadTemplate')
            ->assertFileDownloaded('template-dpt-siswa.csv');
    }

    public function test_admin_can_reset_voting_data_when_session_closed(): void
    {
        Setting::current()->update(['is_voting_open' => false]);

        $student = Student::create([
            'nis' => '2024999',
            'name' => 'Siswa Tes Reset',
            'class' => 'X RPL 1',
        ]);
        $student->has_voted = true;
        $student->voted_at = now();
        $student->save();

        $candidate = Candidate::create([
            'number_order' => 99,
            'chairman_name' => 'Ketua Reset',
            'vice_chairman_name' => 'Wakil Reset',
            'vision' => 'Visi Reset',
            'mission' => ['Misi 1'],
            'vote_count' => 1,
            'is_active' => true,
        ]);

        Vote::create([
            'student_id' => $student->id,
            'candidate_id' => $candidate->id,
            'voted_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertSame(1, Vote::count());

        $this->actingAs($this->superadmin, 'admin');

        Livewire::test(Dashboard::class)
            ->set('resetConfirmText', 'RESET')
            ->call('resetData')
            ->assertSet('showResetConfirm', false);

        $this->assertSame(0, Vote::count());

        $student->refresh();
        $this->assertFalse($student->has_voted);
        $this->assertNull($student->voted_at);

        $candidate->refresh();
        $this->assertSame(0, $candidate->vote_count);
    }
}

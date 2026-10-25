<?php

namespace Tests\Feature;

use App\Livewire\Admin\CandidateManager;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateManagerTest extends TestCase
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

        Setting::current()->update(['is_voting_open' => false]);
    }

    public function test_admin_can_create_new_candidate(): void
    {
        $this->actingAs($this->admin, 'admin');

        Livewire::test(CandidateManager::class)
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('number_order', 1)
            ->set('chairman_name', 'Rafi Aditya')
            ->set('vice_chairman_name', 'Salsa Amelia')
            ->set('slogan', 'Bersama Berkarya')
            ->set('vision', 'Visi Mantap')
            ->set('missionText', "Poin 1\nPoin 2\nPoin 3")
            ->set('is_active', true)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('candidates', [
            'number_order' => 1,
            'chairman_name' => 'Rafi Aditya',
            'vice_chairman_name' => 'Salsa Amelia',
        ]);
    }

    public function test_admin_can_edit_existing_candidate(): void
    {
        $candidate = Candidate::create([
            'number_order' => 2,
            'chairman_name' => 'Bima Nugroho',
            'vice_chairman_name' => 'Citra Wulandari',
            'slogan' => 'Satu Suara',
            'vision' => 'Visi Sekolah',
            'mission' => ['Misi 1'],
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(CandidateManager::class)
            ->call('openEdit', $candidate->id)
            ->assertSet('editingId', $candidate->id)
            ->set('slogan', 'Slogan Diperbarui')
            ->call('save')
            ->assertSet('showModal', false);

        $candidate->refresh();
        $this->assertSame('Slogan Diperbarui', $candidate->slogan);
    }

    public function test_admin_cannot_delete_candidate_with_votes(): void
    {
        $candidate = Candidate::create([
            'number_order' => 3,
            'chairman_name' => 'Kandidat Suara',
            'vice_chairman_name' => 'Wakil Suara',
            'vision' => 'Visi',
            'mission' => ['Misi'],
            'is_active' => true,
        ]);
        $candidate->vote_count = 5;
        $candidate->save();

        $this->actingAs($this->admin, 'admin');

        Livewire::test(CandidateManager::class)
            ->call('delete', $candidate->id)
            ->assertHasErrors('delete');

        $this->assertDatabaseHas('candidates', ['id' => $candidate->id]);
    }

    public function test_candidate_locked_when_voting_open_unless_override(): void
    {
        Setting::current()->update(['is_voting_open' => true]);

        $this->actingAs($this->admin, 'admin');

        // Saat voting open dan override false: openCreate diblokir
        Livewire::test(CandidateManager::class)
            ->call('openCreate')
            ->assertSet('showModal', false)
            ->assertHasErrors('session');

        // Buka override
        Livewire::test(CandidateManager::class)
            ->call('toggleOverride')
            ->assertSet('overrideLock', true)
            ->call('openCreate')
            ->assertSet('showModal', true);
    }

    public function test_admin_can_update_election_period_when_editing_candidate(): void
    {
        $candidate = Candidate::create([
            'number_order' => 1,
            'chairman_name' => 'Calon Ketua',
            'vice_chairman_name' => 'Calon Wakil',
            'vision' => 'Visi',
            'mission' => ['Misi'],
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(CandidateManager::class)
            ->call('openEdit', $candidate->id)
            ->set('election_period', 'Periode 2026/2027')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertSame('Periode 2026/2027', Setting::current()->election_period);

        Livewire::test(\App\Livewire\Voter\VotingBooth::class)
            ->assertSee('Periode 2026/2027');
    }
}

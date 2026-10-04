<?php

namespace Tests\Feature;

use App\Livewire\Voter\VotingBooth;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VotingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::current()->update([
            'is_voting_open' => true,
            'opened_at' => now(),
        ]);
    }

    public function test_student_can_vote_and_status_is_marked_has_voted(): void
    {
        $student = Student::create([
            'nis' => '2024001',
            'name' => 'Budi Santoso',
            'class' => 'XII RPL 1',
        ]);

        $candidate = Candidate::create([
            'number_order' => 1,
            'chairman_name' => 'Calon Ketua',
            'vice_chairman_name' => 'Calon Wakil',
            'vision' => 'Visi mantap',
            'mission' => ['Misi 1', 'Misi 2'],
            'is_active' => true,
        ]);

        $this->assertFalse($student->has_voted);
        $this->assertNull($student->voted_at);
        $this->assertSame(0, $candidate->vote_count);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024001')
            ->call('verifyNis')
            ->assertSet('verified', true)
            ->assertSet('studentId', $student->id)
            ->call('selectCandidate', $candidate->id)
            ->assertSet('showConfirmModal', true)
            ->call('confirmVote')
            ->assertSet('voteSuccess', true)
            ->assertDispatched('vote-recorded');

        // Verifikasi database: student HARUS memiliki has_voted = true dan voted_at != null
        $student->refresh();
        $this->assertTrue($student->has_voted);
        $this->assertNotNull($student->voted_at);

        // Verifikasi kandidat menerima suara
        $candidate->refresh();
        $this->assertSame(1, $candidate->vote_count);

        // Verifikasi record vote ada di database
        $this->assertDatabaseHas('votes', [
            'student_id' => $student->id,
            'candidate_id' => $candidate->id,
        ]);
    }

    public function test_student_who_has_voted_cannot_verify_again(): void
    {
        $student = Student::create([
            'nis' => '2024002',
            'name' => 'Siti Aisyah',
            'class' => 'XII RPL 2',
        ]);
        $student->has_voted = true;
        $student->voted_at = now();
        $student->save();

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024002')
            ->call('verifyNis')
            ->assertSet('verified', false)
            ->assertSet('errorMessage', 'NIS ini SUDAH melakukan pemilihan sebelumnya. Satu siswa hanya berhak memilih satu kali.');
    }

    public function test_voting_blocked_when_session_is_closed(): void
    {
        Setting::current()->update([
            'is_voting_open' => false,
            'closed_at' => now(),
        ]);

        Student::create([
            'nis' => '2024003',
            'name' => 'Ahmad Dahlan',
            'class' => 'XI TKJ 1',
        ]);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024003')
            ->call('verifyNis')
            ->assertSet('verified', false)
            ->assertSet('errorMessage', 'Sesi voting saat ini sedang ditutup oleh panitia.');
    }
}

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
            'unique_code' => 'A1B2',
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
            ->set('unique_code', 'A1B2')
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
            'unique_code' => 'C3D4',
        ]);
        $student->has_voted = true;
        $student->voted_at = now();
        $student->save();

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024002')
            ->set('unique_code', 'C3D4')
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
            'unique_code' => 'E5F6',
        ]);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024003')
            ->set('unique_code', 'E5F6')
            ->call('verifyNis')
            ->assertSet('verified', false)
            ->assertSet('errorMessage', 'Sesi voting saat ini sedang ditutup oleh panitia.');
    }

    public function test_verification_fails_if_nis_not_in_voters_table(): void
    {
        Livewire::test(VotingBooth::class)
            ->set('nis', '9999999')
            ->set('unique_code', 'A1B2')
            ->call('verifyNis')
            ->assertHasErrors(['nis'])
            ->assertSet('verified', false);
    }

    public function test_verification_fails_if_unique_code_does_not_match_nis(): void
    {
        Student::create([
            'nis' => '2024004',
            'name' => 'Doni Siregar',
            'class' => 'X RPL 1',
            'unique_code' => 'K7L8',
        ]);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024004')
            ->set('unique_code', 'WXYZ') // Salah kode unik
            ->call('verifyNis')
            ->assertHasErrors(['unique_code'])
            ->assertSet('verified', false);
    }

    public function test_verification_fails_if_unique_code_is_not_4_characters(): void
    {
        Student::create([
            'nis' => '2024005',
            'name' => 'Rina Nose',
            'class' => 'X RPL 2',
            'unique_code' => 'K7L8',
        ]);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024005')
            ->set('unique_code', 'ABC') // Kurang dari 4
            ->call('verifyNis')
            ->assertHasErrors(['unique_code'])
            ->assertSet('verified', false);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024005')
            ->set('unique_code', 'ABCDE') // Lebih dari 4
            ->call('verifyNis')
            ->assertHasErrors(['unique_code'])
            ->assertSet('verified', false);
    }

    public function test_secret_ballot_preserves_privacy_without_storing_unique_code_in_votes(): void
    {
        $student = Student::create([
            'nis' => '2024006',
            'name' => 'Secret Voter',
            'class' => 'XII RPL 1',
            'unique_code' => 'Z9Y8',
        ]);

        $candidate = Candidate::create([
            'number_order' => 1,
            'chairman_name' => 'Calon Rahasia',
            'vice_chairman_name' => 'Wakil Rahasia',
            'vision' => 'Visi',
            'mission' => ['Misi'],
            'is_active' => true,
        ]);

        Livewire::test(VotingBooth::class)
            ->set('nis', '2024006')
            ->set('unique_code', 'z9y8') // uji case-insensitivity input
            ->call('verifyNis')
            ->assertSet('verified', true)
            ->call('selectCandidate', $candidate->id)
            ->call('confirmVote')
            ->assertSet('voteSuccess', true);

        // Pastikan kolom unique_code tidak pernah disimpan di tabel votes
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('votes', 'unique_code'));
    }
}

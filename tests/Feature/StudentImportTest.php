<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_students_from_csv(): void
    {
        Livewire::test(Dashboard::class)
            ->set('studentFile', UploadedFile::fake()->createWithContent(
                'students.csv',
                "nis,name,class\n2025001,Andi Pratama,XII IPA 1\n2025002,Bunga Citra Lestari,XII IPA 1\n",
            ))
            ->call('importStudents')
            ->assertHasNoErrors()
            ->assertSet('importSummary', ['created' => 2, 'updated' => 0]);

        $this->assertDatabaseHas('students', [
            'nis' => '2025001',
            'name' => 'Andi Pratama',
            'class' => 'XII IPA 1',
        ]);

        $student = Student::query()->where('nis', '2025001')->first();
        $this->assertNotNull($student->unique_code);
        $this->assertSame(4, strlen($student->unique_code));
    }

    public function test_import_generates_unique_codes_for_vocational_format(): void
    {
        $csv = "nis,name,class\n"
            ."1001,Ahmad Rizki,RPL-X\n"
            ."1002,Siti Nurhaliza,TKJ-XI\n"
            ."1003,Budi Santoso,AKL-XII\n";

        Livewire::test(Dashboard::class)
            ->set('studentFile', UploadedFile::fake()->createWithContent('dpt.csv', $csv))
            ->call('importStudents')
            ->assertHasNoErrors()
            ->assertSet('importSummary', ['created' => 3, 'updated' => 0]);

        $students = Student::query()->whereIn('nis', ['1001', '1002', '1003'])->get();
        $this->assertCount(3, $students);
        foreach ($students as $s) {
            $this->assertNotEmpty($s->unique_code);
            $this->assertSame(4, strlen($s->unique_code));
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}$/', $s->unique_code);
        }
    }

    public function test_invalid_duplicate_rows_are_rejected_atomically(): void
    {
        Livewire::test(Dashboard::class)
            ->set('studentFile', UploadedFile::fake()->createWithContent(
                'students.csv',
                "nis,name,class\n2025001,Andi Pratama,XII IPA 1\n2025001,Andi Kedua,XII IPA 2\n",
            ))
            ->call('importStudents')
            ->assertHasErrors('studentFile');

        $this->assertSame(0, Student::count());
    }

    public function test_import_updates_existing_student_without_resetting_vote_status(): void
    {
        $student = Student::create([
            'nis' => '2025001',
            'name' => 'Nama Lama',
            'class' => 'XII IPA 1',
        ]);
        Student::query()->whereKey($student->id)->update(['has_voted' => true, 'voted_at' => now()]);

        Livewire::test(Dashboard::class)
            ->set('studentFile', UploadedFile::fake()->createWithContent(
                'students.csv',
                "nis,name,class\n2025001,Nama Baru,XII IPA 2\n",
            ))
            ->call('importStudents')
            ->assertHasNoErrors()
            ->assertSet('importSummary', ['created' => 0, 'updated' => 1]);

        $this->assertDatabaseHas('students', [
            'nis' => '2025001',
            'name' => 'Nama Baru',
            'class' => 'XII IPA 2',
            'has_voted' => true,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VotingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('school_name', 'SMK Muhammadiyah 1 Bantul');
        Setting::set('election_name', 'Pemilihan Ketua & Formatur IPM');
        Setting::set('max_choices', 9);
        Setting::set('election_status', 'open');
    }

    public function test_student_login_with_valid_nis_and_token()
    {
        $student = Student::create([
            'nis' => '12345',
            'nama' => 'Budi Testing',
            'kelas' => 'XI TKJ 1',
            'token' => Hash::make('TOKEN123'),
            'plain_token' => 'TOKEN123',
            'status' => 'active',
            'has_voted' => false,
        ]);

        $response = $this->post(route('student.login.submit'), [
            'nis' => '12345',
            'token' => 'TOKEN123',
        ]);

        $response->assertRedirect(route('student.voting'));
        $this->assertAuthenticatedAs($student, 'student');
    }

    public function test_student_cannot_login_with_invalid_token()
    {
        Student::create([
            'nis' => '12345',
            'nama' => 'Budi Testing',
            'kelas' => 'XI TKJ 1',
            'token' => Hash::make('TOKEN123'),
            'plain_token' => 'TOKEN123',
            'status' => 'active',
            'has_voted' => false,
        ]);

        $response = $this->post(route('student.login.submit'), [
            'nis' => '12345',
            'token' => 'WRONGTOKEN',
        ]);

        $response->assertSessionHasErrors(['token']);
        $this->assertGuest('student');
    }

    public function test_student_can_submit_vote_with_exactly_9_candidates()
    {
        $student = Student::create([
            'nis' => '12345',
            'nama' => 'Budi Testing',
            'kelas' => 'XI TKJ 1',
            'token' => Hash::make('TOKEN123'),
            'plain_token' => 'TOKEN123',
            'status' => 'active',
            'has_voted' => false,
        ]);

        // Create 12 active candidates
        $candidateIds = [];
        for ($i = 1; $i <= 12; $i++) {
            $c = Candidate::create([
                'nomor_urut' => $i,
                'nama' => "Calon {$i}",
                'kelas' => 'XI TKJ 1',
                'status' => 'active',
            ]);
            if ($i <= 9) {
                $candidateIds[] = $c->id;
            }
        }

        $response = $this->actingAs($student, 'student')
            ->post(route('student.voting.submit'), [
                'candidates' => $candidateIds,
            ]);

        $response->assertRedirect(route('student.success'));

        // Assert database state
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'has_voted' => true,
        ]);

        $this->assertDatabaseHas('votes', [
            'student_id' => $student->id,
        ]);

        $vote = Vote::where('student_id', $student->id)->first();
        $this->assertCount(9, $vote->details);
    }

    public function test_student_cannot_submit_vote_with_less_than_9_candidates()
    {
        $student = Student::create([
            'nis' => '12345',
            'nama' => 'Budi Testing',
            'kelas' => 'XI TKJ 1',
            'token' => Hash::make('TOKEN123'),
            'plain_token' => 'TOKEN123',
            'status' => 'active',
            'has_voted' => false,
        ]);

        // Create 5 candidates
        $candidateIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $c = Candidate::create([
                'nomor_urut' => $i,
                'nama' => "Calon {$i}",
                'kelas' => 'XI TKJ 1',
                'status' => 'active',
            ]);
            $candidateIds[] = $c->id;
        }

        $response = $this->actingAs($student, 'student')
            ->post(route('student.voting.submit'), [
                'candidates' => $candidateIds,
            ]);

        $response->assertSessionHasErrors(['candidates']);
        $this->assertDatabaseMissing('votes', [
            'student_id' => $student->id,
        ]);
    }

    public function test_admin_login_and_dashboard_access()
    {
        $admin = Admin::create([
            'name' => 'Admin Panitia',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');

        $dashResponse = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);
    }

    public function test_admin_can_toggle_visi_misi_setting()
    {
        $admin = Admin::create([
            'name' => 'Admin Panitia',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.update'), [
                'school_name' => 'SMK Test',
                'election_name' => 'Pemilihan Test',
                'election_year' => '2026',
                'max_choices' => 9,
                'voting_timeout_minutes' => 5,
                'show_visi_misi' => 0,
            ]);

        $this->assertFalse(Setting::get('show_visi_misi'));
    }

    public function test_voting_page_respects_show_visi_misi_setting()
    {
        $student = Student::create([
            'nis' => '12345',
            'nama' => 'Budi Testing',
            'kelas' => 'XI TKJ 1',
            'token' => Hash::make('TOKEN123'),
            'plain_token' => 'TOKEN123',
            'status' => 'active',
            'has_voted' => false,
        ]);

        Candidate::create([
            'nomor_urut' => 1,
            'nama' => 'Calon 1',
            'kelas' => 'XI TKJ 1',
            'status' => 'active',
            'visi' => 'Visi test',
            'misi' => 'Misi test',
        ]);

        // When show_visi_misi is true
        Setting::set('show_visi_misi', true, 'boolean');
        $resShown = $this->actingAs($student, 'student')->get(route('student.voting'));
        $resShown->assertSee('Visi & Misi', false);

        // When show_visi_misi is false
        Setting::set('show_visi_misi', false, 'boolean');
        $resHidden = $this->actingAs($student, 'student')->get(route('student.voting'));
        $resHidden->assertDontSee('Visi & Misi', false);
    }

    public function test_admin_can_clear_all_voters()
    {
        $admin = Admin::create([
            'username' => 'admin_test2',
            'email' => 'admin2@example.com',
            'name' => 'Admin Test 2',
            'password' => Hash::make('password123'),
        ]);

        Student::create([
            'nis' => '99901',
            'nama' => 'Student 1',
            'kelas' => 'XI TKJ 1',
            'token' => Hash::make('TK01'),
            'plain_token' => 'TK01',
            'status' => 'active',
        ]);

        $this->assertEquals(1, Student::count());

        $res = $this->actingAs($admin, 'admin')
            ->delete(route('admin.students.clear-all'));

        $res->assertRedirect();
        $this->assertEquals(0, Student::count());
    }
}

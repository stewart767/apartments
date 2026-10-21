<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Institution;
use App\Models\TenantDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        
        // Seed an institution for student tests
        Institution::create([
            'id' => 1,
            'name' => 'University of Dar es Salaam',
            'description' => 'Main Campus',
            'address' => 'Ubungo, DSM',
        ]);
    }

    public function test_owner_registration_succeeds_without_tenant_details(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Owner',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'owner',
            'phone' => '+255711111111',
            'whatsapp' => '+255711111111',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
        ]);

        $response->assertRedirect('/dashboard');
        
        $user = User::where('email', 'owner@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('owner', $user->role);
        
        // Ensure no tenant details were created
        $this->assertNull(TenantDetail::where('user_id', $user->id)->first());
    }

    public function test_non_student_tenant_registration_succeeds_with_tenant_details(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Tenant',
            'email' => 'tenant@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'phone' => '+255722222222',
            'whatsapp' => '+255722222222',
            'gender' => 'female',
            'date_of_birth' => '2000-01-01',
            'national_id_passport' => 'NIDA12345',
            'emergency_contact' => '+255733333333',
        ]);

        $response->assertRedirect('/dashboard');
        
        $user = User::where('email', 'tenant@example.com')->first();
        $this->assertNotNull($user);
        
        // Ensure tenant details were created correctly
        $details = TenantDetail::where('user_id', $user->id)->first();
        $this->assertNotNull($details);
        $this->assertFalse((bool)$details->is_student);
        $this->assertEquals('NIDA12345', $details->national_id_passport);
        $this->assertEquals('+255733333333', $details->emergency_contact);
        $this->assertNull($details->institution_id);
    }

    public function test_student_tenant_registration_succeeds_with_institution_details(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'phone' => '+255722222222',
            'whatsapp' => '+255722222222',
            'gender' => 'female',
            'date_of_birth' => '2004-01-01',
            'national_id_passport' => 'NIDA56789',
            'emergency_contact' => '+255733333333',
            'is_student' => '1',
            'institution_name' => 'University of Dar es Salaam',
            'student_id' => 'REG/UDSM/2026/001',
        ]);

        $response->assertRedirect('/dashboard');
        
        $user = User::where('email', 'student@example.com')->first();
        $this->assertNotNull($user);
        
        $details = TenantDetail::where('user_id', $user->id)->first();
        $this->assertNotNull($details);
        $this->assertTrue((bool)$details->is_student);
        $this->assertEquals(1, $details->institution_id);
        $this->assertEquals('REG/UDSM/2026/001', $details->student_reg_number);
    }

    public function test_student_tenant_registration_creates_custom_institution_automatically(): void
    {
        $response = $this->post('/register', [
            'name' => 'Custom Student',
            'email' => 'customstudent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'phone' => '+255722222222',
            'whatsapp' => '+255722222222',
            'gender' => 'male',
            'date_of_birth' => '2005-01-01',
            'national_id_passport' => 'NIDA99999',
            'emergency_contact' => '+255733333333',
            'is_student' => '1',
            'institution_name' => 'Mzumbe University',
            'student_id' => 'REG/MZUMBE/2026/002',
        ]);

        $response->assertRedirect('/dashboard');
        
        // Verify Mzumbe University was created automatically
        $institution = Institution::where('name', 'Mzumbe University')->first();
        $this->assertNotNull($institution);
        
        $user = User::where('email', 'customstudent@example.com')->first();
        $this->assertNotNull($user);
        
        $details = TenantDetail::where('user_id', $user->id)->first();
        $this->assertNotNull($details);
        $this->assertEquals($institution->id, $details->institution_id);
        $this->assertEquals('REG/MZUMBE/2026/002', $details->student_reg_number);
    }

    public function test_tenant_registration_validation_fails_if_fields_missing(): void
    {
        // Missing national_id_passport and emergency_contact for tenant
        $response = $this->post('/register', [
            'name' => 'Failed Tenant',
            'email' => 'fail@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'phone' => '+255722222222',
            'gender' => 'female',
            'date_of_birth' => '2000-01-01',
        ]);

        $response->assertSessionHasErrors(['national_id_passport', 'emergency_contact']);
    }
}

<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\TenantDetail;
use App\Models\OwnerDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_register_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Tenant',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'phone' => '+255712345678',
            'is_student' => true,
            'custom_institution_name' => 'University of Dar es Salaam',
            'student_reg_number' => 'UDSM/2026/001',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Account created successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role', 'tenant_detail'],
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@test.com', 'role' => 'tenant']);
        $this->assertDatabaseHas('tenant_details', ['student_reg_number' => 'UDSM/2026/001', 'is_student' => 1]);
    }

    public function test_user_can_login_via_api(): void
    {
        $user = User::create([
            'name' => 'Owner Jane',
            'email' => 'jane@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@test.com',
            'password' => 'secret123',
            'device_name' => 'Test Mobile Device',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ]
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials provided.',
            ]);
    }

    public function test_user_can_view_and_update_profile(): void
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // View profile
        $viewResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/profile');

        $viewResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Original Name');

        // Update profile
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/profile', [
                'name' => 'Updated Name',
                'email' => 'user@test.com',
                'phone' => '+255788990011',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.phone', '+255788990011');
    }
}

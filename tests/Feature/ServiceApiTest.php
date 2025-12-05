<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_services()
    {
        // Create some services
        Resource::factory()->count(3)->create(['type' => 'service']);
        // Create non-service resources
        Resource::factory()->create(['type' => 'hall', 'name' => 'Conference Hall']);

        $response = $this->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_view_specific_service()
    {
        $service = Resource::factory()->create(['type' => 'service', 'name' => 'Consulting Service']);

        $response = $this->getJson("/api/services/{$service->id}");

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Consulting Service');
    }

    public function test_admin_can_create_service()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/services', [
            'name' => 'New Service',
            'description' => 'Test service',
            'capacity' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('service.type', 'service');

        $this->assertDatabaseHas('resources', [
            'name' => 'New Service',
            'type' => 'service',
        ]);
    }

    public function test_admin_can_update_service()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Resource::factory()->create(['type' => 'service']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/services/{$service->id}", [
            'name' => 'Updated Service',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('service.name', 'Updated Service');
    }

    public function test_admin_can_delete_service()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Resource::factory()->create(['type' => 'service']);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('resources', ['id' => $service->id]);
    }

    public function test_non_admin_cannot_create_service()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/services', [
            'name' => 'Unauthorized Service',
        ]);

        $response->assertStatus(403);
    }
}

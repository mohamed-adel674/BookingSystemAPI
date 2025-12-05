<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Availability;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_resource()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/resources', [
            'name' => 'New Conference Hall',
            'type' => 'hall',
            'capacity' => 100,
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('resource.name', 'New Conference Hall');

        $this->assertDatabaseHas('resources', ['name' => 'New Conference Hall']);
    }

    public function test_admin_can_update_resource()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $resource = Resource::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/resources/{$resource->id}", [
            'name' => 'Updated Name',
            'type' => 'service', // Ensure valid type
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('resource.name', 'Updated Name');
    }

    public function test_admin_can_delete_resource()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $resource = Resource::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/resources/{$resource->id}");

        $response->assertStatus(204); // Or 204 depending on implementation
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    public function test_admin_can_create_availability()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $resource = Resource::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/availabilities', [
            'resource_id' => $resource->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('availabilities', [
            'resource_id' => $resource->id,
            'day_of_week' => 1,
        ]);
    }
}

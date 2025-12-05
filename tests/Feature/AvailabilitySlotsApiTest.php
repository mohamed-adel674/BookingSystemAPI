<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Availability;
use Carbon\Carbon;

class AvailabilitySlotsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_availability()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        
        Availability::factory()->create([
            'resource_id' => $resource->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/availability?resource_id={$resource->id}&start_date={$nextMonday}&end_date={$nextMonday}&duration_minutes=60");

        $response->assertStatus(200)
            ->assertJsonStructure(['slots']);
    }

    public function test_admin_can_create_slot()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $resource = Resource::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/slots', [
            'resource_id' => $resource->id,
            'day_of_week' => 2,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('availabilities', [
            'resource_id' => $resource->id,
            'day_of_week' => 2,
        ]);
    }

    public function test_admin_can_update_slot()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $resource = Resource::factory()->create();
        $availability = Availability::factory()->create([
            'resource_id' => $resource->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/slots/{$availability->id}", [
            'resource_id' => $resource->id,
            'day_of_week' => 3,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('availabilities', [
            'id' => $availability->id,
            'day_of_week' => 3,
        ]);
    }

    public function test_non_admin_cannot_create_slot()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $resource = Resource::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/slots', [
            'resource_id' => $resource->id,
            'day_of_week' => 2,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        $response->assertStatus(403);
    }
}

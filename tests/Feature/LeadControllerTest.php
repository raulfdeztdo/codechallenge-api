<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Lead;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    // Prueba para listar todos los Leads.
    public function test_can_list_leads()
    {
        $user = User::factory()->create();

        $leads = Lead::factory()->count(5)->create();

        $response = $this->actingAs($user)->getJson('/api/leads/list');
        $response->assertStatus(200)
                 ->assertJson($leads->toArray());
    }

    // Prueba para almacenar un nuevo Lead.
    public function test_can_store_lead()
    {
        $user = User::factory()->create();

        $leadData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ];

        $response = $this->actingAs($user)->postJson('/api/leads/store', $leadData);
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Lead creado con éxito'])
                 ->assertJsonPath('lead.name', $leadData['name']);

        $this->assertDatabaseHas('leads', $leadData);
    }

    // Prueba para mostrar un Lead específico.
    public function test_can_show_lead()
    {
        $user = User::factory()->create();

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/leads/{$lead->id}");
        $response->assertStatus(200)
                 ->assertJson($lead->toArray());
    }

    // Prueba para actualizar un Lead.
    public function test_can_update_lead()
    {
        $user = User::factory()->create();

        $lead = Lead::factory()->create();

        $updatedData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '9876543210'
        ];

        $response = $this->actingAs($user)->postJson("/api/leads/{$lead->id}", $updatedData);
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Lead actualizado con éxito'])
                 ->assertJsonPath('lead.name', $updatedData['name']);

        $this->assertDatabaseHas('leads', $updatedData);
    }

    // Prueba para eliminar un Lead.
    public function test_can_destroy_lead()
    {
        $user = User::factory()->create();

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/leads/{$lead->id}");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Lead eliminado con éxito']);

        $this->assertDatabaseMissing('leads', $lead->toArray());
    }
}

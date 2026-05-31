<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Features\Teams\Models\Team;
use App\Features\Sectors\Models\Sector;
use App\Shared\Models\User;

class TeamApiTest extends TestCase
{
    private Sector $sector;
    private User $head;

    protected function setUp(): void
    {
        parent::setUp();

        $this->head = $this->createUser('sector_manager');
        $this->sector = Sector::factory()->create(['head_id' => $this->head->id]);
    }

    public function test_list_teams_requires_auth(): void
    {
        $response = $this->getJson('/api/teams');

        $this->assertEquals(401, $response->status());
    }

    public function test_list_teams_returns_paginated(): void
    {
        $manager = $this->createUser('sector_manager');
        Team::factory(3)->create([
            'sector_id' => $this->sector->id,
            'responsible_id' => $this->head->id,
        ]);

        $response = $this->actingAs($manager, 'sanctum')
            ->getJson('/api/teams');

        $this->assertEquals(200, $response->status());
        $this->assertCount(3, $response->json('data'));
    }

    public function test_create_team_with_valid_data(): void
    {
        $manager = $this->createUser('sector_manager');
        $user = $this->createUser('worker');
        $workerRole = \App\Shared\Models\Role::where('name', 'worker')->first();
        $user->roles()->syncWithoutDetaching([$workerRole->id]);

        $response = $this->actingAs($manager, 'sanctum')
            ->postJson('/api/teams', [
                'sector_id' => $this->sector->id,
                'name' => 'Equipa de Teste',
                'responsible_id' => $user->id,
            ]);

        $this->assertEquals(201, $response->status());
        $this->assertEquals('Equipa de Teste', $response->json('data.name'));
        $this->assertEquals($user->id, $response->json('data.responsible_id'));

        $this->assertDatabaseHas('teams', [
            'name' => 'Equipa de Teste',
            'responsible_id' => $user->id,
        ]);
    }

    public function test_create_team_validation_fails(): void
    {
        $manager = $this->createUser('sector_manager');

        $response = $this->actingAs($manager, 'sanctum')
            ->postJson('/api/teams', [
                'name' => '',
            ]);

        $this->assertEquals(422, $response->status());
    }

    public function test_get_team(): void
    {
        $team = Team::factory()->create([
            'sector_id' => $this->sector->id,
            'responsible_id' => $this->head->id,
        ]);

        $response = $this->actingAs($this->head, 'sanctum')
            ->getJson("/api/teams/{$team->id}");

        $this->assertEquals(200, $response->status());
        $this->assertEquals($team->id, $response->json('data.id'));
        $this->assertEquals($team->name, $response->json('data.name'));
    }

    public function test_get_team_not_found(): void
    {
        $manager = $this->createUser('sector_manager');

        $response = $this->actingAs($manager, 'sanctum')
            ->getJson('/api/teams/nonexistent-id');

        $this->assertEquals(404, $response->status());
    }

    public function test_update_team(): void
    {
        $team = Team::factory()->create([
            'sector_id' => $this->sector->id,
            'responsible_id' => $this->head->id,
        ]);

        $response = $this->actingAs($this->head, 'sanctum')
            ->putJson("/api/teams/{$team->id}", [
                'name' => 'Updated Team Name',
            ]);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Updated Team Name', $response->json('data.name'));
    }

    public function test_delete_team(): void
    {
        $admin = $this->createUser('admin');
        $team = Team::factory()->create([
            'sector_id' => $this->sector->id,
            'responsible_id' => $this->head->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/teams/{$team->id}");

        $this->assertEquals(200, $response->status());
        $this->assertSoftDeleted('teams', ['id' => $team->id]);
    }
}

<?php

namespace Tests\Feature\Authorization;

use Tests\TestCase;
use App\Features\Teams\Models\Team;
use App\Features\Sectors\Models\Sector;
use App\Shared\Models\User;

class TeamPoliciesTest extends TestCase
{
    private Sector $sector;
    private Team $team;
    private User $responsible;

    protected function setUp(): void
    {
        parent::setUp();

        $head = $this->createUser();
        $this->sector = Sector::factory()->create(['head_id' => $head->id]);
        $this->responsible = $this->createUser('worker');

        $this->team = Team::factory()->create([
            'sector_id' => $this->sector->id,
            'responsible_id' => $this->responsible->id,
        ]);
    }

    public function test_admin_can_view_any_team(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/teams/{$this->team->id}");

        $this->assertEquals(200, $response->status());
    }

    public function test_team_manager_can_view_own_team(): void
    {
        $teamManager = $this->createUser('team_manager');
        $workerRole = \App\Shared\Models\Role::where('name', 'worker')->first();
        $teamManager->roles()->syncWithoutDetaching([$workerRole->id]);

        // Give the user a worker record for this team so they can be a team_manager
        \App\Features\Workers\Models\Worker::create([
            'user_id' => $teamManager->id,
            'team_id' => $this->team->id,
        ]);

        $this->team->update(['responsible_id' => $teamManager->id]);

        $response = $this->actingAs($teamManager, 'sanctum')
            ->getJson("/api/teams/{$this->team->id}");

        $this->assertEquals(200, $response->status());
    }

    public function test_team_manager_cannot_view_other_team(): void
    {
        $teamManager = $this->createUser('team_manager');

        $otherTeam = Team::factory()->create([
            'sector_id' => $this->sector->id,
            'responsible_id' => $this->responsible->id,
        ]);

        $response = $this->actingAs($teamManager, 'sanctum')
            ->getJson("/api/teams/{$otherTeam->id}");

        $this->assertEquals(403, $response->status());
    }

    public function test_unauthorized_cannot_create_team(): void
    {
        $worker = $this->createUser('worker');

        $response = $this->actingAs($worker, 'sanctum')
            ->postJson('/api/teams', [
                'sector_id' => $this->sector->id,
                'name' => 'Hijacked Team',
                'responsible_id' => $this->responsible->id,
            ]);

        $this->assertEquals(403, $response->status());
    }

    public function test_unauthorized_cannot_update_team(): void
    {
        $worker = $this->createUser('worker');

        $response = $this->actingAs($worker, 'sanctum')
            ->putJson("/api/teams/{$this->team->id}", [
                'name' => 'Hijacked',
            ]);

        $this->assertEquals(403, $response->status());
    }

    public function test_admin_can_delete_any_team(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/teams/{$this->team->id}");

        $this->assertEquals(200, $response->status());
    }

    public function test_unauthorized_cannot_delete_team(): void
    {
        $worker = $this->createUser('worker');

        $response = $this->actingAs($worker, 'sanctum')
            ->deleteJson("/api/teams/{$this->team->id}");

        $this->assertEquals(403, $response->status());
    }
}

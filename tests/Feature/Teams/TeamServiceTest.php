<?php

namespace Tests\Feature\Teams;

use Tests\TestCase;
use App\Features\Teams\Models\Team;
use App\Features\Teams\Services\TeamService;
use App\Features\Sectors\Models\Sector;
use App\Features\Workers\Models\Worker;
use App\Shared\Models\User;

class TeamServiceTest extends TestCase
{
    private TeamService $service;
    private Sector $sector;

    protected function setUp(): void
    {
        parent::setUp();

        $head = $this->createUser();
        $this->sector = Sector::factory()->create(['head_id' => $head->id]);
        $this->service = $this->app->make(TeamService::class);
    }

    public function test_create_team_with_responsible_creates_worker_and_assigns_roles(): void
    {
        $user = $this->createUser();

        $team = $this->service->create([
            'sector_id' => $this->sector->id,
            'name' => 'Equipa de Teste',
            'responsible_id' => $user->id,
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'responsible_id' => $user->id,
        ]);
        $this->assertDatabaseHas('workers', [
            'user_id' => $user->id,
            'team_id' => $team->id,
        ]);
        $this->assertTrue($user->roles()->where('name', 'team_manager')->exists());
        $this->assertTrue($user->roles()->where('name', 'worker')->exists());
    }

    public function test_create_team_with_user_already_worker_of_another_team_throws_exception(): void
    {
        $user = $this->createUser();
        $head = $this->createUser();
        $anotherSector = Sector::factory()->create(['head_id' => $head->id]);

        $this->service->create([
            'sector_id' => $anotherSector->id,
            'name' => 'First Team',
            'responsible_id' => $user->id,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage(__('validation.custom.responsible_already_worker'));

        $this->service->create([
            'sector_id' => $this->sector->id,
            'name' => 'Second Team',
            'responsible_id' => $user->id,
        ]);
    }

    public function test_update_team_changes_responsible_and_creates_new_worker(): void
    {
        $oldUser = $this->createUser();
        $newUser = $this->createUser();

        $team = $this->service->create([
            'sector_id' => $this->sector->id,
            'name' => 'Equipa de Teste',
            'responsible_id' => $oldUser->id,
        ]);

        $updated = $this->service->update($team, [
            'responsible_id' => $newUser->id,
        ]);

        $this->assertEquals($newUser->id, $updated->fresh()->responsible_id);
        $this->assertDatabaseHas('workers', ['user_id' => $newUser->id, 'team_id' => $team->id]);
        $this->assertTrue($newUser->roles()->where('name', 'team_manager')->exists());
        // Old worker record is preserved
        $this->assertDatabaseHas('workers', ['user_id' => $oldUser->id, 'team_id' => $team->id]);
    }

    public function test_update_team_with_same_responsible_does_not_duplicate_worker(): void
    {
        $user = $this->createUser();

        $team = $this->service->create([
            'sector_id' => $this->sector->id,
            'name' => 'Equipa de Teste',
            'responsible_id' => $user->id,
        ]);

        $updated = $this->service->update($team, [
            'name' => 'Updated Name',
            'responsible_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $updated->fresh()->responsible_id);
        $this->assertCount(1, Worker::where('user_id', $user->id)->get());
        $this->assertEquals('Updated Name', $updated->fresh()->name);
    }
}

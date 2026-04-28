<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StepFifteenScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_fifteen_plus_screens_render_for_owner(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();
        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('wbs-builder.index', $project))
            ->assertOk()
            ->assertSee('WBS tree');

        $this->actingAs($owner)
            ->get(route('assignments.index', $project))
            ->assertOk()
            ->assertSee('Existing assignments');

        $this->actingAs($owner)
            ->get(route('schedule.show', $project))
            ->assertOk()
            ->assertSee('Detail table');

        $this->actingAs($owner)
            ->get(route('exports.index'))
            ->assertOk()
            ->assertSee('WBS Excel exports');

        $this->actingAs($owner)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Alert and reminder history');

        $this->actingAs($owner)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Localization and display preferences');
    }
}

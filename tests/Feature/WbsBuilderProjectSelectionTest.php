<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WbsBuilderProjectSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_wbs_builder_search_redirects_to_selected_project(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();
        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('wbs-builder.search', ['project_id' => $project->id]))
            ->assertRedirect(route('wbs-builder.index', $project));
    }
}

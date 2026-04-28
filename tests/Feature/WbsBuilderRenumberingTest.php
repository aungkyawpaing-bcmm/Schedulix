<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectWbsItem;
use App\Models\TaskMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WbsBuilderRenumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_root_wbs_item_with_conflicting_sort_order_renumbers_without_unique_collision(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();
        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();
        $taskMaster = TaskMaster::query()->firstOrFail();

        $response = $this->actingAs($owner)->post(route('wbs-builder.store', $project), [
            'parent_id' => null,
            'task_master_id' => $taskMaster->id,
            'item_name' => 'AAA',
            'item_type' => 'task',
            'content_item_type' => 'copy',
            'platform' => 'web',
            'description' => null,
            'is_assignable' => 1,
            'sort_order' => 1,
        ]);

        $response
            ->assertRedirect(route('wbs-builder.index', $project))
            ->assertSessionHas('status', 'WBS item created.');

        $rootNumbers = ProjectWbsItem::query()
            ->where('project_id', $project->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('wbs_number')
            ->all();

        $this->assertSameSize(array_unique($rootNumbers), $rootNumbers);
        $this->assertContains('1.0', $rootNumbers);
        $this->assertContains('2.0', $rootNumbers);
        $this->assertContains('3.0', $rootNumbers);
    }
}

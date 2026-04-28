<?php

namespace Tests\Feature;

use App\Http\Controllers\AssignmentController;
use App\Models\Project;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentLeaderOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_create_view_loads_active_users_as_project_leader_options(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();
        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();
        $leader = User::query()->where('email', 'leader@wbs-generator.test')->firstOrFail();
        $member = User::query()->where('email', 'member@wbs-generator.test')->firstOrFail();
        $this->actingAs($owner);

        $controller = new AssignmentController(app(AssignmentService::class));
        $reflection = new \ReflectionMethod($controller, 'formData');
        $reflection->setAccessible(true);

        $viewData = $reflection->invoke($controller, $project, null);
        $leaderOptions = $viewData['leaderOptions'];

        $this->assertTrue($leaderOptions->contains('id', $leader->id));
        $this->assertTrue($leaderOptions->contains('id', $member->id));
    }
}

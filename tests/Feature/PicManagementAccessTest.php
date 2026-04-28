<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_pic_create_screen(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('pics.create'))
            ->assertOk();
    }

    public function test_non_owner_is_redirected_from_pic_create_screen(): void
    {
        $this->seed();

        $pm = User::query()->where('email', 'pm@wbs-generator.test')->firstOrFail();

        $this->actingAs($pm)
            ->get(route('pics.create'))
            ->assertRedirect(route('dashboard'));
    }
}

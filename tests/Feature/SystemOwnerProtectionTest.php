<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemOwnerProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_owner_account_keeps_owner_role_on_update(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();

        $updated = app(UserManagementService::class)->update($owner, [
            'name' => 'System Owner',
            'email' => 'owner@wbs-generator.test',
            'position' => 'Owner',
            'system_role' => 'project_manager',
            'locale' => 'en',
            'timezone' => 'Asia/Yangon',
            'is_active' => false,
            'is_available' => true,
            'available_from' => null,
            'password' => null,
        ]);

        $this->assertSame('owner', $updated->system_role);
        $this->assertTrue($updated->is_active);
    }
}

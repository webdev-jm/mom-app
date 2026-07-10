<?php

namespace Tests\Feature;

use App\Models\Mom;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MomIndexButtonsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['mom access', 'mom create', 'mom upload', 'mom edit', 'mom print', 'mom delete'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['mom access', 'mom create', 'mom upload', 'mom edit', 'mom print', 'mom delete']);
    }

    /** @test */
    public function mom_list_buttons_are_responsive_with_tooltips(): void
    {
        Mom::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('mom.index'))
            ->assertStatus(200);

        $html = $response->getContent();

        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringContainsString('<span class="d-none d-md-inline">', $html);
        $this->assertStringContainsString('title="' . __('adminlte::utilities.view') . '"', $html);
        $this->assertStringContainsString('title="' . __('adminlte::moms.new_mom') . '"', $html);
    }
}

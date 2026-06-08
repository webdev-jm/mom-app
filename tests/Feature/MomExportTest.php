<?php

namespace Tests\Feature;

use App\Models\Mom;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MomExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $authorizedUser;
    protected User $unauthorizedUser;
    protected Mom $mom;

    const PERMISSION_MOM_ACCESS = 'mom access';
    const PERMISSION_MOM_PRINT  = 'mom print';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate(self::PERMISSION_MOM_ACCESS, 'web');
        Permission::findOrCreate(self::PERMISSION_MOM_PRINT, 'web');

        $this->authorizedUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();

        $this->authorizedUser->givePermissionTo([
            self::PERMISSION_MOM_ACCESS,
            self::PERMISSION_MOM_PRINT,
        ]);

        $this->mom = Mom::factory()->create();
    }

    /** @test */
    public function authorized_user_can_download_excel_export_for_a_mom(): void
    {
        $this->actingAs($this->authorizedUser)
            ->get(route('mom.exportExcel', encrypt($this->mom->id)))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_export(): void
    {
        $this->get(route('mom.exportExcel', encrypt($this->mom->id)))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function user_without_mom_print_permission_cannot_export(): void
    {
        $this->unauthorizedUser->givePermissionTo(self::PERMISSION_MOM_ACCESS);

        $this->actingAs($this->unauthorizedUser)
            ->get(route('mom.exportExcel', encrypt($this->mom->id)))
            ->assertStatus(403);
    }
}

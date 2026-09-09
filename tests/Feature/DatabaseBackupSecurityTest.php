<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemUpdate;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Livewire\Livewire;
use Tests\TestCase;

class DatabaseBackupSecurityTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.system-update.download-db'));

        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(false);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(999);

        $response = $this->actingAs($user)->get(route('admin.system-update.download-db'));

        $response->assertForbidden();
    }

    public function test_admin_can_download_database_backup(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(true);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_backup_');
        file_put_contents($tempFile, 'dummy archive content');

        $mockService = \Mockery::mock(DatabaseBackupService::class);
        $mockService->shouldReceive('generateBackup')
            ->with('tgz')
            ->once()
            ->andReturn([
                'path' => $tempFile,
                'filename' => 'backup_test.tgz',
                'size' => 21,
            ]);

        $this->app->instance(DatabaseBackupService::class, $mockService);

        $response = $this->actingAs($user)->get(route('admin.system-update.download-db', ['format' => 'tgz']));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=backup_test.tgz');
        $response->assertHeader('content-type', 'application/gzip');
    }

    public function test_system_update_page_livewire_action_downloads_backup(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(true);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_livewire_backup_');
        file_put_contents($tempFile, 'dummy livewire content');

        $mockService = \Mockery::mock(DatabaseBackupService::class);
        $mockService->shouldReceive('generateBackup')
            ->with('tgz')
            ->once()
            ->andReturn([
                'path' => $tempFile,
                'filename' => 'backup_livewire.tgz',
                'size' => 22,
            ]);

        $this->app->instance(DatabaseBackupService::class, $mockService);

        Livewire::actingAs($user)
            ->test(SystemUpdate::class)
            ->mountAction('downloadDatabase')
            ->set('mountedActions.0.data.format', 'tgz')
            ->callMountedAction();
    }
}

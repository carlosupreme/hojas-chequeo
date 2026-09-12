<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemUpdate;
use App\Models\User;
use App\Services\SystemConfigurationBackupService;
use Livewire\Livewire;
use Tests\TestCase;

class SystemConfigurationBackupSecurityTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.system-update.download-config'));

        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(false);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(999);

        $response = $this->actingAs($user)->get(route('admin.system-update.download-config'));

        $response->assertForbidden();
    }

    public function test_admin_can_download_configuration_backup_tgz(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(true);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_config_backup_');
        file_put_contents($tempFile, 'dummy archive content');

        $mockService = \Mockery::mock(SystemConfigurationBackupService::class);
        $mockService->shouldReceive('generateBackup')
            ->with('tgz')
            ->once()
            ->andReturn([
                'path' => $tempFile,
                'filename' => 'server_config_test.tgz',
                'size' => 21,
            ]);

        $this->app->instance(SystemConfigurationBackupService::class, $mockService);

        $response = $this->actingAs($user)->get(route('admin.system-update.download-config', ['format' => 'tgz']));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=server_config_test.tgz');
        $response->assertHeader('content-type', 'application/gzip');
    }

    public function test_admin_can_download_configuration_backup_zip(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(true);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_config_backup_zip_');
        file_put_contents($tempFile, 'dummy zip content');

        $mockService = \Mockery::mock(SystemConfigurationBackupService::class);
        $mockService->shouldReceive('generateBackup')
            ->with('zip')
            ->once()
            ->andReturn([
                'path' => $tempFile,
                'filename' => 'server_config_test.zip',
                'size' => 17,
            ]);

        $this->app->instance(SystemConfigurationBackupService::class, $mockService);

        $response = $this->actingAs($user)->get(route('admin.system-update.download-config', ['format' => 'zip']));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=server_config_test.zip');
        $response->assertHeader('content-type', 'application/zip');
    }

    public function test_system_update_page_livewire_action_downloads_configuration(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrador')->andReturn(true);
        $user->shouldReceive('canAccessPanel')->andReturn(true);
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_livewire_config_');
        file_put_contents($tempFile, 'dummy livewire config');

        $mockService = \Mockery::mock(SystemConfigurationBackupService::class);
        $mockService->shouldReceive('generateBackup')
            ->with('tgz')
            ->once()
            ->andReturn([
                'path' => $tempFile,
                'filename' => 'server_config_livewire.tgz',
                'size' => 22,
            ]);

        $this->app->instance(SystemConfigurationBackupService::class, $mockService);

        Livewire::actingAs($user)
            ->test(SystemUpdate::class)
            ->mountAction('downloadConfiguration')
            ->set('mountedActions.0.data.format', 'tgz')
            ->callMountedAction();
    }
}

<?php

namespace Tests\Unit;

use App\Services\BackupService;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    /**
     * Test backup service formats bytes correctly
     */
    public function test_backup_service_formats_bytes(): void
    {
        $this->assertEquals('1 KB', BackupService::formatBytes(1024));
        $this->assertEquals('1 MB', BackupService::formatBytes(1024 * 1024));
        $this->assertEquals('1 GB', BackupService::formatBytes(1024 * 1024 * 1024));
    }

    /**
     * Test backup service gets backup list
     */
    public function test_backup_service_gets_backup_list(): void
    {
        $backups = BackupService::getBackupList();

        $this->assertIsArray($backups);
    }
}

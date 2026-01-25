<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditAbsensiData extends Command
{
    protected $signature = 'app:audit-absensi';
    protected $description = 'Audit data anomalies in absensi table';

    public function handle()
    {
        $this->info('Starting Absensi Audit...');

        // 1. Check Distinct Status Values
        $statuses = DB::table('absensi')->distinct()->pluck('status');
        $this->info('Distinct Status Values found in DB:');
        foreach ($statuses as $status) {
            $this->info("- '{$status}'");
        }

        // 2. Check for NULL status
        $nullStatusCount = DB::table('absensi')->whereNull('status')->count();
        if ($nullStatusCount > 0) {
            $this->error("Found {$nullStatusCount} rows with NULL status!");
        }

        // 3. Check for Empty String status
        $emptyStatusCount = DB::table('absensi')->where('status', '')->count();
        if ($emptyStatusCount > 0) {
            $this->error("Found {$emptyStatusCount} rows with EMPTY STRING status!");
        }

        // 4. Check consistency with Pertemuan
        // Apakah ada absensi yang punya pertemuan_id tapi pertemuannya sudah dihapus?
        $orphanPertemuan = DB::table('absensi')
            ->leftJoin('pertemuan', 'absensi.pertemuan_id', '=', 'pertemuan.id')
            ->whereNotNull('absensi.pertemuan_id')
            ->whereNull('pertemuan.id')
            ->count();
            
        if ($orphanPertemuan > 0) {
            $this->error("Found {$orphanPertemuan} absensi linked to non-existent Pertemuan!");
        }

        $this->info('Audit completed.');
    }
}

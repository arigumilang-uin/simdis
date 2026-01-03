<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;

class SyncUserNamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user names based on their role and assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing user names...');
        
        $updated = 0;
        
        // Sync Kaprodi names
        $this->info('📚 Syncing Kaprodi names...');
        $jurusans = Jurusan::whereNotNull('kaprodi_user_id')->with('kaprodi.role')->get();
        foreach ($jurusans as $jurusan) {
            if ($jurusan->kaprodi) {
                $kaprodi = $jurusan->kaprodi;
                
                // SKIP Developer role
                if ($kaprodi->role && $kaprodi->role->nama_role === 'Developer') {
                    $this->line("  ⏭️  {$kaprodi->username} → Skipped (Developer)");
                    continue;
                }
                
                $newName = "Kaprodi {$jurusan->nama_jurusan}";
                if ($kaprodi->nama !== $newName) {
                    $kaprodi->updateQuietly(['nama' => $newName]);
                    $this->line("  ✅ {$kaprodi->username} → {$newName}");
                    $updated++;
                }
            }
        }
        
        // Sync Wali Kelas names
        $this->info('🏫 Syncing Wali Kelas names...');
        $kelasList = Kelas::whereNotNull('wali_kelas_user_id')->with('waliKelas.role')->get();
        foreach ($kelasList as $kelas) {
            if ($kelas->waliKelas) {
                $waliKelas = $kelas->waliKelas;
                
                // SKIP Developer role
                if ($waliKelas->role && $waliKelas->role->nama_role === 'Developer') {
                    $this->line("  ⏭️  {$waliKelas->username} → Skipped (Developer)");
                    continue;
                }
                
                $newName = "Wali Kelas {$kelas->nama_kelas}";
                if ($waliKelas->nama !== $newName) {
                    $waliKelas->updateQuietly(['nama' => $newName]);
                    $this->line("  ✅ {$waliKelas->username} → {$newName}");
                    $updated++;
                }
            }
        }
        
        $this->newLine();
        $this->info("✨ Done! {$updated} user names updated.");
        
        return Command::SUCCESS;
    }
}

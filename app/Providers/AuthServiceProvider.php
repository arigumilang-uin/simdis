<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Siswa;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Policies\SiswaPolicy;
use App\Policies\RiwayatPelanggaranPolicy;
use App\Policies\TindakLanjutPolicy;

/**
 * Auth Service Provider
 * 
 * Register authorization policies untuk setiap model.
 * Policies define who can perform actions (view, create, update, delete, approve).
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Siswa::class => SiswaPolicy::class,
        RiwayatPelanggaran::class => RiwayatPelanggaranPolicy::class,
        TindakLanjut::class => TindakLanjutPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register policies
        $this->registerPolicies();

        // Additional gates if needed
        // Gate::define('approve-surat-4', function (User $user) {
        //     return $user->hasRole('Kepala Sekolah');
        // });
    }
}

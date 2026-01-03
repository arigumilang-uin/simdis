<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider
 * 
 * This service provider is responsible for binding repository interfaces
 * to their concrete implementations. This enables dependency injection
 * and allows for easy swapping of implementations (e.g., for testing).
 * 
 * All repository bindings should be registered in the register() method.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * This method is called before boot() and should be used to bind
     * interfaces to their implementations in the service container.
     *
     * @return void
     */
    public function register(): void
    {
        // Register User Repository
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        // Register Siswa Repository
        $this->app->bind(
            \App\Repositories\Contracts\SiswaRepositoryInterface::class,
            \App\Repositories\SiswaRepository::class
        );

        // Register Jenis Pelanggaran Repository
        $this->app->bind(
            \App\Repositories\Contracts\JenisPelanggaranRepositoryInterface::class,
            \App\Repositories\JenisPelanggaranRepository::class
        );

        // Register Riwayat Pelanggaran Repository
        $this->app->bind(
            \App\Repositories\Contracts\RiwayatPelanggaranRepositoryInterface::class,
            \App\Repositories\RiwayatPelanggaranRepository::class
        );

        // Register Tindak Lanjut Repository
        $this->app->bind(
            \App\Repositories\Contracts\TindakLanjutRepositoryInterface::class,
            \App\Repositories\TindakLanjutRepository::class
        );

        // TODO: Add more repository bindings as they are created
        // Examples:
        // - KelasRepositoryInterface
        // - JurusanRepositoryInterface
    }

    /**
     * Bootstrap services.
     *
     * This method is called after all service providers have been registered.
     * Use this for any bootstrapping tasks that depend on other services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}

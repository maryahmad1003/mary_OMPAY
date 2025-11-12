<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Repositories\{
    CompteRepositoryInterface,
    EloquentCompteRepository,
    TransactionRepositoryInterface,
    EloquentTransactionRepository,
    UserRepositoryInterface,
    EloquentUserRepository
};
use App\Http\Services\{
    CompteServiceInterface,
    CompteService,
    TransactionServiceInterface,
    TransactionService,
    AuthServiceInterface,
    AuthService,
    SmsServiceInterface,
    TwilioSmsService
};

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Repositories
        $this->app->bind(CompteRepositoryInterface::class, EloquentCompteRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // Services
        $this->app->bind(CompteServiceInterface::class, CompteService::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(SmsServiceInterface::class, TwilioSmsService::class);
    }

    public function boot()
    {
        //
    }
}

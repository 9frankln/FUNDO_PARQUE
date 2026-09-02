<?php

namespace App\Providers;

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureActiveAccountSession;
use App\Http\Middleware\EnsureFundoSelected;
use App\Support\SystemBranding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemBranding::class, fn ($app) => new SystemBranding($app['cache.store']));
        $this->app->singleton(\App\Support\PdfReportConfig::class, fn ($app) => new \App\Support\PdfReportConfig($app['cache.store']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        View::composer('*', function ($view): void {
            $view->with('branding', $this->app->make(SystemBranding::class));
            $view->with('pdfConfig', $this->app->make(\App\Support\PdfReportConfig::class));
        });

        // Revalidate tenancy and route permissions on every Livewire update.
        Livewire::addPersistentMiddleware([
            EnsureFundoSelected::class,
            CheckPermission::class,
            EnsureActiveAccountSession::class,
        ]);
    }
}

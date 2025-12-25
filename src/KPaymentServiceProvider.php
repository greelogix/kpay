<?php

namespace Greelogix\KPayment;

use Illuminate\Support\ServiceProvider;
use Greelogix\KPayment\Services\KnetService;

class KPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/kpayment.php',
            'kpayment'
        );

        $this->app->singleton('kpayment', function ($app) {
            $config = $app['config']->get('kpayment');
            
            return new KnetService(
                $config['tranportal_id'] ?? '',
                $config['tranportal_password'] ?? '',
                $config['resource_key'] ?? '',
                $config['base_url'] ?? 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm',
                $config['test_mode'] ?? true,
                $config['response_url'] ?? '',
                $config['error_url'] ?? '',
                $config['currency'] ?? '414',
                $config['language'] ?? 'EN',
                $config['kfast_enabled'] ?? false,
                $config['apple_pay_enabled'] ?? false
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Load routes (automatically loaded - no need to publish)
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views (automatically loaded - accessible as 'kpayment::view.name')
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'kpayment');

        // Load translations (automatically loaded - accessible as __('kpayment.key'))
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'kpayment');

        // Publishable assets (optional - only if user wants to customize)
        $this->publishes([
            __DIR__ . '/../config/kpayment.php' => config_path('kpayment.php'),
        ], 'kpayment-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'kpayment-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/kpayment'),
        ], 'kpayment-views');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/kpayment'),
        ], 'kpayment-lang');

        // Publish all KNET package assets at once
        $this->publishes([
            __DIR__ . '/../config/kpayment.php' => config_path('kpayment.php'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/kpayment'),
            __DIR__ . '/../lang' => lang_path('vendor/kpayment'),
        ], 'kpayment');
    }
}



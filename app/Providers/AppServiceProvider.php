<?php

namespace App\Providers;

use App\Events\PublishRabbitMQEvent;
use App\Listeners\SendRabbitMQMessageListener;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\RedisBloomFilter::class, function ($app) {
            return new \App\Services\RedisBloomFilter();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Event::listen(PublishRabbitMQEvent::class, SendRabbitMQMessageListener::class);
    }
}

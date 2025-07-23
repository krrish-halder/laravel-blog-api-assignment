<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\User;
use App\Observers\BlogObserver;
use App\Policies\BlogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate::define('update', function (User $user, Blog $blog){
        //     return $user->id === $blog->user_id;
        // });

        Gate::define('update-blog', [BlogPolicy::class, 'update']);
        Gate::define(('delete-blog'), [BlogPolicy::class, 'delete']);
        Blog::observe(BlogObserver::class);
    }
}

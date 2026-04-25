<?php

namespace App\Providers;

use App\Models\ProductReview;
use App\Observers\ProductReviewObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        Model::preventLazyLoading(! $this->app->isProduction());
        ProductReview::observe(ProductReviewObserver::class);

        $this->configureRateLimiting();

        Vite::prefetch(concurrency: 3);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(
            $request->ip().'|'.(string) $request->input('email')
        ));

        RateLimiter::for('register', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('checkout', fn (Request $request): Limit => Limit::perMinute(5)->by(
            $request->user()?->getAuthIdentifier() ?: $request->ip()
        ));

        RateLimiter::for('withdraw', fn (Request $request): Limit => Limit::perMinute(3)->by(
            $request->user()?->getAuthIdentifier() ?: $request->ip()
        ));

        RateLimiter::for('public-catalog', fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('cart-mutation', fn (Request $request): Limit => Limit::perMinute(30)->by(
            $request->user()?->getAuthIdentifier() ?: $request->ip()
        ));

        RateLimiter::for('chat-send', fn (Request $request): Limit => Limit::perMinute(30)->by(
            $request->user()?->getAuthIdentifier() ?: $request->ip()
        ));

        RateLimiter::for('review-submit', fn (Request $request): Limit => Limit::perMinute(10)->by(
            $request->user()?->getAuthIdentifier() ?: $request->ip()
        ));
    }
}

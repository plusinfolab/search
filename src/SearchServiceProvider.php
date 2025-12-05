<?php

namespace PlusInfoLab\Search;

use PlusInfoLab\Search\Observers\SearchableObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SearchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('search')
            ->hasConfigFile()
            ->hasMigration('create_search_history_table');
    }

    public function packageRegistered(): void
    {
        // Register the main search engine
        $this->app->singleton(SearchEngine::class, function ($app) {
            return new SearchEngine($app['config']['search'] ?? []);
        });

        // Register the search facade
        $this->app->alias(SearchEngine::class, 'search');
    }

    public function packageBooted(): void
    {
        // Observer registration is disabled by default
        // Users can manually observe models if needed
        // Example: SearchableObserver::observe(Post::class);
    }
}

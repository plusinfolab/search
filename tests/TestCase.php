<?php

namespace PlusInfoLab\Search\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use PlusInfoLab\Search\SearchServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'PlusInfoLab\\Search\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        // Create posts table for testing
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            SearchServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup search configuration
        $app['config']->set('search', require __DIR__ . '/../config/search.php');
    }
}

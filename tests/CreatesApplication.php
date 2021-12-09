<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

trait CreatesApplication
{
    protected static $hasRunOnce = false;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        if (!static::$hasRunOnce) {
            if (!file_exists(database_path('database.sqlite'))) {
                File::put(database_path('database.sqlite'), null);   

                Artisan::call('migrate', [
                    '--env' => 'testing',
                    '--path' => 'database/migrations/testdb',
                    '--seed' => true
                ]);    
            }

            static::$hasRunOnce = true;
        }

        return $app;
    }
}

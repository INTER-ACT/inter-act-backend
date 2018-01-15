<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\ServiceProvider;
use Mockery\Exception;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('customPaginate', function(int $pageNumber = 1, int $perPage = 10, string $name = 'start', array $columns = ['*']){
            return $this->paginate($perPage, $columns, $name, $pageNumber)->appends(Input::except($name));
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

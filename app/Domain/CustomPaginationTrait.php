<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 14.01.18
 * Time: 12:02
 */

namespace App\Domain;

use Illuminate\Contracts\Pagination\Paginator as IPaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;

trait CustomPaginationTrait
{
    /**
     * @param Paginator $paginator
     * @return Paginator
     */
    protected function updatePagination(IPaginator $paginator)
    {
        return $paginator->appends(Input::except($paginator->getPageName()));
    }

    /**
     * Creates a LengthAwarePaginator from the given collection
     * https://gist.github.com/vluzrmos/3ce756322702331fdf2bf414fea27bcb
     *
     * @param array|Collection $items
     * @param int $perPage
     * @param int $page
     * @param string $pageName
     *
     * @return LengthAwarePaginator
     */
    public function paginate($items, $perPage = 15, $page = null, string $pageName = 'start')
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName
        ]);
    }
}
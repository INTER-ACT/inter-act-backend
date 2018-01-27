<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 29.12.17
 * Time: 09:43
 */

namespace App\Http\Resources\GeneralResources;


use Illuminate\Support\Collection;

class SearchResourceData
{
    /** @var array */
    public $search_results = [];

    /**
     * SearchResourceData constructor.
     * @param array $search_results
     */
    public function __construct(array $search_results)
    {
        $this->search_results = $search_results;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 18.12.17
 * Time: 17:39
 */

namespace App\Domain;


class PageRequest
{
    private $per_page;
    private $page_number;

    /**
     * PageRequest constructor.
     * @param int $per_page
     * @param int $page_number
     */
    public function __construct(int $per_page, int $page_number)
    {
        $this->per_page = $per_page;
        $this->page_number = $page_number;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->page_number;
    }
}
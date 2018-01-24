<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 18.12.17
 * Time: 17:39
 */

namespace App\Domain;


use App\Exceptions\CustomExceptions\InvalidPaginationException;
use App\Exceptions\CustomExceptions\InvalidValueException;
use App\Exceptions\CustomExceptions\PaginationOutOfRangeException;
use App\Exceptions\CustomExceptions\PayloadTooLargeException;

class PageRequest
{
    public const MAX_PER_PAGE = 100;
    public const DEFAULT_PER_PAGE = 20;
    public const DEFAULT_PAGE_NUMBER = 1;

    /** @var int */
    public $perPage;
    /** @var int */
    public $pageNumber;

    /**
     * PageRequest constructor.
     * @param int $per_page
     * @param int $page_number
     * @throws InvalidPaginationException
     * @throws InvalidValueException
     * @throws PayloadTooLargeException
     */
    public function __construct($per_page = null, $page_number = null)
    {
        $per_page = (int)$per_page;
        $page_number = (int)$page_number;
        if(!isset($per_page))
            $this->perPage = self::DEFAULT_PER_PAGE;
        else if(!is_int($per_page))
            throw new InvalidPaginationException("The given count for the pagination has to be an integer.");
        else if($per_page <= 0)
            throw new InvalidPaginationException("The given count for the pagination has to be greater than 0.");
        else if($per_page > self::MAX_PER_PAGE)
            throw new PayloadTooLargeException("The given count for the pagination was too big (max: 100).");
        else
            $this->perPage = $per_page;

        if(!isset($page_number))
            $this->pageNumber = self::DEFAULT_PAGE_NUMBER;
        else if(!is_int($page_number))
            throw new InvalidPaginationException("The given page number for the pagination was not valid");
        else
            $this->pageNumber = $page_number;
    }

    /**
     * @return int
     */
    public function getPerPage() : int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getPageNumber() : int
    {
        return $this->pageNumber;
    }
}
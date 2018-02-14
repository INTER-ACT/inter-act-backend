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
    public const DEFAULT_PER_PAGE = 100;
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
    public function __construct(int $per_page = self::DEFAULT_PER_PAGE, int $page_number = self::DEFAULT_PAGE_NUMBER)
    {
        if($per_page <= 0)
            throw new InvalidPaginationException("The given count for the pagination has to be greater than 0.");
        if($per_page > self::MAX_PER_PAGE)
            throw new PayloadTooLargeException("The given count for the pagination was too big (max: 100).");

        $this->perPage = $per_page;
        $this->pageNumber = $page_number;
    }
}
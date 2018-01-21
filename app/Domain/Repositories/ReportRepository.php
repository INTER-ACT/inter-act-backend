<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:56
 */

namespace App\Domain;


class ReportRepository implements IRestRepository   //TODO: Exceptions missing?
{
    use CustomPaginationTrait;

    /**
     * @return string
     */
    public function getRestResourcePath()
    {
        return "/tags";
    }

    /**
     * @return string
     */
    public function getRestResourceName()
    {
        return "tags";
    }

    /**
     * @return string
     */
    public function getFullRestPath()
    {
        return url($this->getRestResourcePath());
    }
}
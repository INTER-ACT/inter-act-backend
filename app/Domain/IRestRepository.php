<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 29.12.17
 * Time: 11:00
 */

namespace App\Domain;


interface IRestRepository
{
    /**
     * @return string
     */
    function getRestResourcePath();

    /**
     * @return string
     */
    function getRestResourceName();

    /**
     * @return string
     */
    function getFullRestPath();
}
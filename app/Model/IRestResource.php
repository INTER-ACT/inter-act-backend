<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 28.12.17
 * Time: 10:42
 */

namespace App\Model;


interface IRestResourcePrimary extends IRestResource
{
    /**
     * @return string
     */
    function getResourcePath();
}
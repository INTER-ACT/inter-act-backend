<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 18:51
 */

namespace App\Model;


interface IRestResource
{
    /**
     * @return int
     */
    function getId() : int;

    /**
     * @return string
     */
    function getType() : string;

    /**
     * @return string
     */
    function getApiFriendlyType() : string;

    /**
     * @return string
     */
    function getApiFriendlyTypeGer() : string;

    /**
     * @return string
     */
    function getResourcePath() : string;
}
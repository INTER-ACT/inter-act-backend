<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 17.12.17
 * Time: 18:51
 */

namespace App;


interface IModel
{
    /**
     * @return int
     */
    function getIdProperty();   //TODO: maybe change name if accessor is not desired

    /**
     * @return string
     */
    function getType();
}
<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 28.12.17
 * Time: 10:42
 */

namespace App;


interface IRestResourceModel extends IModel
{
    function getResourcePath();
}
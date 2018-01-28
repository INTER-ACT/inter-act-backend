<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 17:47
 */

namespace App\Http\Requests;


interface IRequest
{
    /**
     * Validates the Request and throws an Exception, if the request
     * is ambiguous
     *
     * @return Null
     */
    public function validate();

    /**
     * Returns the data of the request,
     * keys are modified, so that they are autofillable
     * whenever possible
     *
     * @return array
     */
    public function getData();

}
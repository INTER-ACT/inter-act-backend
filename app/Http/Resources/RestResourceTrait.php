<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 28.12.17
 * Time: 10:00
 */

namespace App\Http\Resources;


trait RestResourceTrait
{
    protected $customResourcePath;

    /**
     * @return mixed
     */
    public function getCustomResourcePath()
    {
        return $this->customResourcePath;
    }

    /**
     * @param mixed $resourcePath
     */
    public function setResourcePath($resourcePath)
    {
        $this->resourcePath = $resourcePath;
    }

    protected function getResourcePathIfNotNull(string $value_if_null)
    {
        return (!isset($this->customResourcePath) || trim($this->customResourcePath) === '') ? $value_if_null : $this->customResourcePath;
    }
}
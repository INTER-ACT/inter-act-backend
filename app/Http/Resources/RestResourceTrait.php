<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 27.01.18
 * Time: 14:39
 */

namespace App\Http\Resources;


trait RestResourceTrait
{
    public $customResourcePath;

    /**
     * @return mixed
     */
    public function getCustomResourcePath()
    {
        return $this->customResourcePath;
    }

    /**
     * Deprecated Method for setting custom base urls
     *
     * @param mixed $resourcePath
     */
    public function setCustomResourcePath($resourcePath)
    {
        $this->customResourcePath = $resourcePath;
    }

    /**
     * Deprecated Method for generating Urls from Uris
     *
     * @param string $value_if_null
     * @return string
     */
    protected function getResourcePathIfNotNull(string $value_if_null)
    {
        return (!isset($this->customResourcePath) || trim($this->customResourcePath) === '') ? $value_if_null : $this->customResourcePath;
        //return $this->customResourcePath . $value_if_null;
    }

    /**
     * Method for appending the base Url (as defined in the .env file) to an uri
     *
     * @param string $uri
     * @return string
     */
    public function getUrl(string $uri) : string
    {
        return config('app.url') . $uri;
    }

    /**
     * @param string $uri
     * @return string
     */
    public function getUrlFromBase(string $uri) : string
    {
        return url($uri);
    }
}
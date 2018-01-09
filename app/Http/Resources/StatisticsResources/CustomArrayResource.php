<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 09.01.18
 * Time: 08:15
 */

namespace App\Http\Resources\StatisticsResources;


class CustomArrayResource
{
    /** @var array */
    protected $header;
    /** @var array */
    protected $data;

    /**
     * CustomArrayResource constructor.
     * @param array $header
     * @param array $data
     */
    public function __construct(array $header, array $data)
    {
        $this->header = $header;
        $this->data = $data;
    }

    //region Getters and Setters
    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
    //endregion

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge([$this->header], $this->data);
    }
}
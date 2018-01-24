<?php

namespace App\Http\Resources;

use App\IRestResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Response;

class CreatedResponseResource
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $href;

    /**
     * CreatedResponseResource constructor.
     * @param IRestResource $created_resource
     */
    public function __construct(IRestResource $created_resource)
    {
        $this->id = $created_resource->getIdProperty();
        $this->href = url($created_resource->getResourcePath());
    }

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray() : array
    {
        return [
            'href' => $this->href,
            'id' => $this->id
        ];
    }

    /**
     * @return $this
     */
    public function getResponse()
    {
        return \Response::make()->setStatusCode(201)
            ->header('Content-Type', 'text/json')
            ->setContent($this->toArray());
    }
}

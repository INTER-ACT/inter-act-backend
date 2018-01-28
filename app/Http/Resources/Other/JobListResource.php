<?php

namespace App\Http\Resources;

class JobListResource extends ApiResource
{
    /**
     * JobListResource constructor.
     * @param array $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'href' => $this->getUrl('/job_list'),
            'jobs' => $this->resource
        ];
    }
}

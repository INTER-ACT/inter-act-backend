<?php

namespace App\Http\Resources\AmendmentResources;

use App\Http\Resources\ApiResource;

/**
 * Resource for transforming a SubAmendment into a Json representing the
 * changes to the previous Amendment
 *
 * Class ChangeResource
 * @package App\Http\Resources\AmendmentResources
 */
class ChangeResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * Expected input: Subamendment
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thisUri = url($this->getResourcePathIfNotNull($this->getChangesPath()));
        $thisUri = $this->getUrl($this->getChangesPath());

        return [
            'href' => $thisUri,
            'id' => $this->id,
            'handled_at' => $this->handled_at,      // TODO find out wtf this is a string and not a datetime
            'updated_text' => []        // TODO implement when the versioning system is implemented
        ];
    }
}

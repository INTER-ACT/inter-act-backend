<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 21.01.18
 * Time: 17:34
 */

namespace App\Domain;


use App\Http\Resources\LawCollection;
use App\Http\Resources\LawResource;
use Illuminate\Http\Request;

class LawRepository
{
    use CustomPaginationTrait;

    /**
     * @param PageGetRequest $pageRequest
     * @return LawCollection
     */
    public function getAll(PageGetRequest $pageRequest)// : LawCollection
    {
        //return $this->paginate(OgdRisApiBridge::getAllTexts(), $pageRequest->getPerPage(), $pageRequest->getPageNumber())->toArray($request);
        return new LawCollection($this->paginate(OgdRisApiBridge::getAllTexts($pageRequest), $pageRequest->perPage, $pageRequest->pageNumber));
    }

    /**
     * @param string $id
     * @return LawResource
     */
    public function getOne(string $id) : LawResource
    {
        return new LawResource(OgdRisApiBridge::getParagraphFromId($id));
    }
}
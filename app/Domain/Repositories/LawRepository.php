<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 21.01.18
 * Time: 17:34
 */

namespace App\Domain;


use App\Exceptions\CustomExceptions\CannotResolveDependenciesException;
use App\Exceptions\CustomExceptions\InternalServerError;
use App\Http\Resources\LawCollection;
use App\Http\Resources\LawResource;
use App\LawText;
use Illuminate\Http\Request;

class LawRepository
{
    use CustomPaginationTrait;

    /**
     * @param PageGetRequest $pageRequest
     * @return LawCollection
     */
    public function getAll(PageGetRequest $pageRequest) : LawCollection
    {
        return new LawCollection($this->paginate(LawText::all(), $pageRequest->perPage, $pageRequest->pageNumber));
    }

    /**
     * @param string $id
     * @return LawResource
     * @throws CannotResolveDependenciesException
     */
    public function getOne(string $id) : LawResource
    {
        $law_text = LawText::where('law_id', '=', $id)->first();
        if(!isset($law_text))
            throw new CannotResolveDependenciesException('There was an error with finding the law_text with the given id.');
        return new LawResource($law_text);
    }

    /**
     *
     */
    public static function reloadLawTexts() : void
    {
        $law_texts = OgdRisApiBridge::getAllTexts();
        $final_texts = array();
        foreach ($law_texts as $law_text)
        {
            $content = (OgdRisApiBridge::getParagraphFromId($law_text->id))->content;
            array_push($final_texts, new LawText([
                'law_id' => $law_text->id,
                'articleParagraphUnit' => $law_text->articleParagraphUnit,
                'title' => $law_text->title,
                'content' => $content
            ]));
        }
        if(sizeof($final_texts) > 0)
        {
            LawText::truncate();
            foreach ($final_texts as $text)
                $text->save();
            \Log::info("Law Texts reloaded.");
        }
    }
}
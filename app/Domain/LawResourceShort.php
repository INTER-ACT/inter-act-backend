<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 18.01.18
 * Time: 17:37
 */

namespace App\Domain;


class LawResourceShort
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $url;
    /** @var string */
    protected $title;
    /** @var string */
    protected $articleParagraphUnit;    //je nach typ

    /**
     * LawResourceShort constructor.
     * @param string $id
     * @param string $url
     * @param string $title
     * @param string $articleParagraphUnit
     */
    public function __construct(string $id, string $url, string $title, string $articleParagraphUnit)
    {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->articleParagraphUnit = $articleParagraphUnit;
    }


    //region Getters and Setters

    //endregion

    public function toArray()
    {
        return[
            "id" => $this->id,
            "href" => $this->url,
            "articleOrParagraph" => $this->articleParagraphUnit,
            "title" => $this->title
        ];
    }
}
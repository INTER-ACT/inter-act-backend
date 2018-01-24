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
    protected $shortTitle;
    /** @var string */
    protected $title;
    /** @var string */
    protected $proclamationOrgan;       //Kundmachungsorgan
    /** @var string */
    protected $articleParagraphUnit;    //je nach typ

    /**
     * LawResource constructor.
     * @param string $id
     * @param string $url
     * @param string $shortTitle
     * @param string $title
     * @param string $proclamationOrgan
     * @param string $articleParagraphUnit
     */
    public function __construct(string $id, string $url, string $shortTitle, string $title, string $proclamationOrgan, string $articleParagraphUnit)
    {
        $this->url = $url;
        $this->shortTitle = $shortTitle;
        $this->title = $title;
        $this->proclamationOrgan = $proclamationOrgan;
        $this->articleParagraphUnit = $articleParagraphUnit;
        $this->id = $id;
    }

    //region Getters and Setters
    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }

    /**
     * @param string $shortTitle
     */
    public function setShortTitle(string $shortTitle)
    {
        $this->shortTitle = $shortTitle;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getProclamationOrgan(): string
    {
        return $this->proclamationOrgan;
    }

    /**
     * @param string $proclamationOrgan
     */
    public function setProclamationOrgan(string $proclamationOrgan)
    {
        $this->proclamationOrgan = $proclamationOrgan;
    }

    /**
     * @return string
     */
    public function getArticleParagraphUnit(): string
    {
        return $this->articleParagraphUnit;
    }

    /**
     * @param string $articleParagraphUnit
     */
    public function setArticleParagraphUnit(string $articleParagraphUnit)
    {
        $this->articleParagraphUnit = $articleParagraphUnit;
    }
    //endregion

    public function toArray()
    {
        return[
            "id" => $this->id,
            "href" => $this->url,
            "articleParagraphUnit" => $this->articleParagraphUnit,
        ];
    }
}
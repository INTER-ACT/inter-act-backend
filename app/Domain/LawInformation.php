<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 24.01.18
 * Time: 11:16
 */

namespace App\Domain;


class LawInformation
{
    /** @var string */
    public $id;
    /** @var string */
    public $href;
    /** @var string */
    public $title;
    /** @var string */
    public $content;

    /**
     * LawInformation constructor.
     * @param string $id
     * @param string $href
     * @param $title
     * @param string $content
     */
    public function __construct($id, $href, $title, $content)
    {
        $this->id = $id;
        $this->href = $href;
        $this->title = $title;
        $this->content = $content;
    }
}
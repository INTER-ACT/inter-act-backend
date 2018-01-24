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
    /** @var array */
    public $content;

    /**
     * LawInformation constructor.
     * @param string $law_id
     * @param string $law_href
     * @param array $law
     */
    public function __construct(string $law_id, string $law_href, array $law)
    {
        $this->id = $law_id;
        $this->href = $law_href;
        $this->content = $law;
    }
}
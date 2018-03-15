<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LawText extends Model
{
    protected $fillable = ['law_id', 'articleParagraphUnit', 'title', 'content'];

    protected $hidden = ['id'];

    public function getApiFriendlyType() : string
    {
        return "law_text";
    }

    public function getApiFriendlyTypeGer() : string
    {
        return "Gesetzestext";
    }

    public function getResourcePath() : string
    {
        return '/law_texts/' . $this->law_id;
    }
}

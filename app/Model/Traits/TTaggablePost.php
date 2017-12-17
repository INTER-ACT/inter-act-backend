<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.12.17
 * Time: 08:57
 */

namespace App\Traits;


use Illuminate\Database\Query\Builder;

trait TTaggablePost
{
    use TPost;

    public function scopeOfTag(Builder $query, string $tag_name)
    {
        return $query
            ->join('taggables', function($builder){
                $builder->on('taggables.taggable_id', '=', $this->getIdProperty());
                $builder->on('taggables.taggable_type', '=', get_called_class());
            })
            ->join('tags', function($builder){
                $builder->on('tags.id', '=', 'taggables.tag_id');
            })
            ->where('tags.name', '=', $tag_name);
    }
}
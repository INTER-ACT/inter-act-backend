<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->morphTo('commentable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function rating_sum()
    {
        return $this->belongsToMany(User::class, 'comment_ratings', 'user_id', 'comment_id')
            ->selectRaw('sum(comment_ratings.rating_score) as rating_sum')
            ->groupBy('comment_ratings.comment_id');    //Not sure if this is correct
    }

    // additional helper relation for the count
    public function ordersCount()
    {
        return $this->belongsToMany('Order')
            ->selectRaw('count(orders.id) as aggregate')
            ->groupBy('pivot_product_id');
    }

// accessor for easier fetching the count
    public function getOrdersCountAttribute()
    {
        if ( ! array_key_exists('ordersCount', $this->relations)) $this->load('ordersCount');

        $related = $this->getRelation('ordersCount')->first();

        return ($related) ? $related->aggregate : 0;
    }
}

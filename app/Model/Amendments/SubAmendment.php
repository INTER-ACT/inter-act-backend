<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Illuminate\Database\Eloquent\Model;

class SubAmendment extends Model implements ITaggable, IReportable, IRatable, ICommentable
{
    use TTaggablePost;

    const PENDING_STATUS = 'pending';
    const ACCEPTED_STATUS = 'accepted';
    const REJECTED_STATUS = 'rejected';

    public function getIdProperty()
    {
        return $this->id;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendment()
    {
        return $this->belongsTo(Amendment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function ratings()
    {
        return $this->morphMany(RatableRatingAspect::class, 'ratable');
    }

    public function rating_aspects()
    {
        return $this->morphToMany(RatingAspect::class, 'ratable', 'ratable_rating_aspects');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->with('status', '=', $status);
    }
}

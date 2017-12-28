<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Discussions\Discussion;
use App\IRestResourceModel;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Amendment extends Model implements ITaggable, IReportable, IRatable, ICommentable, IRestResourceModel
{
    use TTaggablePost;

    //region IRestResourceModel
    public function getIdProperty()
    {
        return $this->id;
    }

    public function getResourcePath()
    {
        return '/discussions/' . $this->discussion_id . '/amendments/' . $this->id;
    }
    //endregion

    //region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function sub_amendments()
    {
        return $this->hasMany(SubAmendment::class); //TODO: make sortable by status
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
    //endregion
}

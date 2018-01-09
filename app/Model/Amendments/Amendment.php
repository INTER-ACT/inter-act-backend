<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Discussions\Discussion;
use App\IModel;
use App\IRestResource;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Amendment extends Model implements ITaggable, IReportable, IRatable, ICommentable, IRestResource
{
    use TTaggablePost;

    protected $fillable = ['updated_text', 'explanation'];

    //region IRestResource
    public function getIdProperty()
    {
        $this->getType();
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }

    public function getResourcePath()
    {
        $discussion_id = ($this->discussion_id === null) ? DB::selectOne('SELECT discussion_id from amendments WHERE id = ?', $this->id)->discussion_id : $this->discussion_id;
        return '/discussions/' . $discussion_id . '/amendments/' . $this->id;
    }
    //endregion

    public function getChangesPath(){
        return $this->getResourcePath() . "/changes";
    }

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

    public function changes()
    {
        return $this->sub_amendments()->where('status', '=', SubAmendment::ACCEPTED_STATUS);
    }

    public function rejections()
    {
        return $this->sub_amendments()->where('status', '=', SubAmendment::REJECTED_STATUS);
    }

    public function pending_sub_amendments()
    {
        return $this->sub_amendments()->where('status', '=', SubAmendment::PENDING_STATUS);
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
        return $this->belongsToMany(RatableRatingAspect::class, 'rating_aspect_rating')->withTimestamps();
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

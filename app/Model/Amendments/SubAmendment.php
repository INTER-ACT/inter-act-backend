<?php

namespace App\Amendments;

use App\Comments\Comment;
use App\Comments\ICommentable;
use App\Exceptions\CustomExceptions\NotAcceptedException;
use App\IRestResourceModel;
use App\Reports\IReportable;
use App\Reports\Report;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Illuminate\Database\Eloquent\Model;

class SubAmendment extends Model implements ITaggable, IReportable, IRatable, ICommentable, IRestResourceModel
{
    use TTaggablePost;

    public const PENDING_STATUS = 'pending';
    public const ACCEPTED_STATUS = 'accepted';
    public const REJECTED_STATUS = 'rejected';

    protected $fillable = ['updated_text', 'explanation'];

    //region IRestResourceModel
    public function getIdProperty()
    {
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }

    public function getResourcePath()
    {
        //return (string)$this->amendment_id;
        $amendment = ($this->amendment === null) ? Amendment::find($this->amendment_id)->first(['id', 'discussion_id']) : $this->amendment;
        return $amendment->getResourcePath() . '/subamendments/' . $this->id;
    }

    //endregion

    public function getChangesPath(){
        if($this->status != self::ACCEPTED_STATUS)
            throw new NotAcceptedException();

        return $this->amendment->getResourcePath() . "/changes/" . $this->amendment_version;
    }


    //region relations
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
    //endregion

    public function scopeOfStatus($query, $status)
    {
        return $query->with('status', '=', $status);
    }
}

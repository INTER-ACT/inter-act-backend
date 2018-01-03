<?php

namespace App\Discussions;

use App\Amendments\Amendment;
use App\Comments\Comment;
use App\Comments\ICommentable;
use App\IRestResourceModel;
use App\Tags\ITaggable;
use App\Tags\Tag;
use App\Traits\TTaggablePost;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model implements ITaggable, ICommentable, IRestResourceModel
{
    use TTaggablePost;

    protected $fillable = ['title', 'law_text', 'law_explanation'];

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
        return '/discussions/' . $this->id; //TODO: Invalid if id is not selected
    }
    //endregion

    //region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    //endregion

    public function scopeActive($query)
    {
        return $query->where('status_message', '<>', '');
    }

    public function scopeInactive($query)
    {
        return $query->where('status_message', '<>', '');
    }
}

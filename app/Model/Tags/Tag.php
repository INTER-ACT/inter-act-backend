<?php

namespace App\Tags;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Comments\Comment;
use App\Discussions\Discussion;
use App\IHasActivity;
use App\Model\RestModel;
use Carbon\Carbon;
use function foo\func;
use Illuminate\Support\Collection;

class Tag extends RestModel implements IHasActivity
{
    //region constants
    const NUTZUNG_FREMDER_INHALTE_NAME = "Nutzung fremder Inhalte";
    const SOZIALE_MEDIEN_NAME = "soziale Medien";
    const KULTURELLES_ERBE_NAME = "kuturelles Erbe";
    const BILDUNG_UND_WISSENSCHAFT_NAME = "Bildung & Wissenschaft";
    const FREIHEITEN_DER_NUTZER_NAME = "Freiheiten der Nutzer";
    const RESPEKT_UND_ANERKENNUNG_NAME = "Respekt & Anerkennung";
    const RECHTEINHABERSCHAFT_NAME = "Rechteinhaberschaft";
    const DOWNLOAD_UND_STREAMING_NAME = "Download & Streaming";
    const WIRTSCHAFTLICHE_INTERESSEN_NAME = "wirtschaftliche Interessen";
    const USER_GENERATED_CONTENT_NAME = "User-Generated-Content";
    //endregion

    protected $fillable = [
        'name', 'description'
    ];

    protected $hidden = ['pivot'];

    protected static function boot()
    {
        parent::boot();//TODO: This has to be executed before first request with tags!
        self::createBaseTags();
    }

    //region constant_entries

    /**
     * @return Tag
     */
    public static function getNutzungFremderInhalte() : Tag
    {
        return Tag::where('name', '=', self::NUTZUNG_FREMDER_INHALTE_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getSozialeMedien() : Tag
    {
        return Tag::where('name', '=', self::SOZIALE_MEDIEN_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getKulturellesErbe() : Tag
    {
        return Tag::where('name', '=', self::KULTURELLES_ERBE_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getBildungUndWissenschaft() : Tag
    {
        return Tag::where('name', '=', self::BILDUNG_UND_WISSENSCHAFT_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getFreiheitenDerNutzer() : Tag
    {
        return Tag::where('name', '=', self::FREIHEITEN_DER_NUTZER_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getRespektUndAnerkennung() : Tag
    {
        return Tag::where('name', '=', self::RESPEKT_UND_ANERKENNUNG_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getRechteinhaberschaft() : Tag
    {
        return Tag::where('name', '=', self::RECHTEINHABERSCHAFT_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getDownloadUndStreaming() : Tag
    {
        return Tag::where('name', '=', self::DOWNLOAD_UND_STREAMING_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getWirtschaftlicheInteressen() : Tag
    {
        return Tag::where('name', '=', self::WIRTSCHAFTLICHE_INTERESSEN_NAME)->first();
    }

    /**
     * @return Tag
     */
    public static function getUserGeneratedContent() : Tag
    {
        return Tag::where('name', '=', self::USER_GENERATED_CONTENT_NAME)->first();
    }
    //endregion

    function getApiFriendlyType(): string
    {
        return 'tag';
    }

    function getApiFriendlyTypeGer(): string
    {
        return 'Tag';
    }

    public function getResourcePath() : string
    {
        return '/tags/' . $this->id;
    }

    /**
     * @return int
     */
    function getActivityAttribute(): int
    {
        return $this->getActivity();
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return int
     */
    function getActivity(Carbon $start_date = null, Carbon $end_date = null): int
    {
        if(!isset($start_date)) {
            $start_date = now()->subYears(5);
        }
        if(!isset($end_date)) {
            $end_date = now();
        }
        if($this->created_at > $end_date)
            return 0;
        $relationsToLoad = ['discussions' => function($query){
            return $query->select('id', 'created_at');
        }, 'amendments' => function($query){
            return $query->select('id', 'discussion_id', 'created_at');
        }, 'comments' => function($query){
            return $query->select('id', 'commentable_id', 'commentable_type', 'created_at');
        }, 'sub_amendments' => function($query){
            return $query->select('id', 'amendment_id', 'created_at');
        }];
        foreach ($relationsToLoad as $key => $item)
        {
            if($this->relationLoaded($key))
                unset($relationsToLoad[$key]);
        }
        $this->load($relationsToLoad);

        $amendment_blacklist = [];
        $sub_amendment_blacklist = [];
        $comment_blacklist = [];
        $discussion_sum = $this->getDiscussionsActivity($start_date, $end_date, $amendment_blacklist, $sub_amendment_blacklist, $comment_blacklist);
        $amendment_sum = $this->getAmendmentActivity($start_date, $end_date, $amendment_blacklist, $sub_amendment_blacklist, $comment_blacklist);
        $sub_amendment_sum = $this->getSubAmendmentActivity($start_date, $end_date, $sub_amendment_blacklist, $comment_blacklist);
        $comment_sum = $this->getCommentActivity($start_date, $end_date, $comment_blacklist);
        return (int)($discussion_sum + $amendment_sum + $sub_amendment_sum + $comment_sum);
    }

    protected function getDiscussionsActivity(Carbon $start_date, Carbon $end_date, array &$amendment_blacklist, array &$sub_amendment_blacklist, array &$comment_blacklist) : int
    {
        return $this->discussions->sum(function(Discussion $discussion) use($start_date, $end_date, &$amendment_blacklist, &$sub_amendment_blacklist, &$comment_blacklist){
            return $discussion->getActivityBlacklisted($start_date, $end_date, $amendment_blacklist, $sub_amendment_blacklist, $comment_blacklist);
        });
    }

    protected function getAmendmentActivity(Carbon $start_date, Carbon $end_date, array &$amendment_blacklist, array &$sub_amendment_blacklist, array &$comment_blacklist)
    {
        return $this->amendments->sum(function(Amendment $amendment) use($start_date, $end_date, &$amendment_blacklist, &$sub_amendment_blacklist, &$comment_blacklist){
            return $amendment->getActivityBlacklisted($start_date, $end_date, $amendment_blacklist, $sub_amendment_blacklist, $comment_blacklist);
        });
    }

    protected function getSubAmendmentActivity(Carbon $start_date, Carbon $end_date, array &$sub_amendment_blacklist, array &$comment_blacklist)
    {
        return $this->sub_amendments->sum(function(SubAmendment $subAmendment) use($start_date, $end_date, &$sub_amendment_blacklist, &$comment_blacklist){
            return $subAmendment->getActivityBlacklisted($start_date, $end_date, $sub_amendment_blacklist, $comment_blacklist);
        });
    }

    protected function getCommentActivity(Carbon $start_date, Carbon $end_date, array $comment_blacklist)
    {
        return $this->comments()->whereNotIn('id', $comment_blacklist)->get()->sum(function(Comment $comment) use($start_date, $end_date, &$comment_blacklist){
            return $comment->getActivityBlacklisted($start_date, $end_date, $comment_blacklist);
        });
    }

    protected function getCommentIdsRecursive(Comment $comment)
    {
        return $comment->getAllCommentIdsRecursive();
    }

    //region relations
    public function taggables()
    {
        return $this->with(['discussions', 'amendments', 'sub_amendments']);    //Not sure if this works
    }

    public function discussions()
    {
        return $this->morphedByMany(Discussion::class, 'taggable', 'taggables');
    }

    public function amendments()
    {
        return $this->morphedByMany(Amendment::class, 'taggable', 'taggables');
    }

    public function sub_amendments()
    {
        return $this->morphedByMany(SubAmendment::class, 'taggable', 'taggables');
    }

    public function comments()
    {
        return $this->morphedByMany(Comment::class, 'taggable', 'taggables');
    }
    //endregion

    /**
     * return @void
     */
    private static function createBaseTags() : void
    {
        Tag::firstOrCreate(['name' => Tag::NUTZUNG_FREMDER_INHALTE_NAME, 'description' => 'Alles rund um die Nutzung fremder Inhalte']);
        Tag::firstOrCreate(['name' => Tag::SOZIALE_MEDIEN_NAME, 'description' => 'Alles rund um Soziale Medien']);
        Tag::firstOrCreate(['name' => Tag::KULTURELLES_ERBE_NAME, 'description' => 'Alles rund um kulturelles Erbe']);
        Tag::firstOrCreate(['name' => Tag::BILDUNG_UND_WISSENSCHAFT_NAME, 'description' => 'Alles rund um Bildung und Wissenschaft']);
        Tag::firstOrCreate(['name' => Tag::FREIHEITEN_DER_NUTZER_NAME, 'description' => 'Alles rund um Freiheiten der Nutzer']);
        Tag::firstOrCreate(['name' => Tag::RESPEKT_UND_ANERKENNUNG_NAME, 'description' => 'Alles rund um Respekt und Anerkennung']);
        Tag::firstOrCreate(['name' => Tag::RECHTEINHABERSCHAFT_NAME, 'description' => 'Alles rund um Rechteinhaberschaft']);
        Tag::firstOrCreate(['name' => Tag::DOWNLOAD_UND_STREAMING_NAME, 'description' => 'Alles rund um Downloads und Streaming']);
        Tag::firstOrCreate(['name' => Tag::WIRTSCHAFTLICHE_INTERESSEN_NAME, 'description' => 'Alles rund um wirtschaftliche Interessen']);
        Tag::firstOrCreate(['name' => Tag::USER_GENERATED_CONTENT_NAME, 'description' => 'Alles rund um User Generated Content']);
    }
}

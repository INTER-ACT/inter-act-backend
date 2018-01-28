<?php

namespace App;

use App\Model\RestModel;

class MultiAspectRating extends RestModel
{
    //protected $primaryKey = ['user_id', 'ratable_id', 'ratable_type'];
    //protected $incrementing = false;

    const TABLE_NAME = 'multi_aspect_ratings';

    const ASPECT1_COLUMN = 'aspect1';
    const ASPECT2_COLUMN = 'aspect2';
    const ASPECT3_COLUMN = 'aspect3';
    const ASPECT4_COLUMN = 'aspect4';
    const ASPECT5_COLUMN = 'aspect5';
    const ASPECT6_COLUMN = 'aspect6';
    const ASPECT7_COLUMN = 'aspect7';
    const ASPECT8_COLUMN = 'aspect8';
    const ASPECT9_COLUMN = 'aspect9';
    const ASPECT10_COLUMN = 'aspect10';

    const ASPECT1 = 'aspect1';
    const ASPECT2 = 'aspect2';
    const ASPECT3 = 'aspect3';
    const ASPECT4 = 'aspect4';
    const ASPECT5 = 'aspect5';
    const ASPECT6 = 'aspect6';
    const ASPECT7 = 'aspect7';
    const ASPECT8 = 'aspect8';
    const ASPECT9 = 'aspect9';
    const ASPECT10 = 'aspect10';

    protected $fillable = ['aspect1', 'aspect2', 'aspect3', 'aspect4', 'aspect5', 'aspect6', 'aspect7', 'aspect8', 'aspect9', 'aspect10'];
    protected $hidden = ['user_id', 'ratable_id', 'ratable_type', 'created_at', 'updated_at'];

    public function getId() : int
    {
        return 0;
    }

    public function getType() : string
    {
        return get_class($this);
    }

    public  function getApiFriendlyType(): string
    {
        return 'multi-aspect-rating';
    }

    public function getResourcePath() : string
    {
        return $this->ratable->getResourcePath() . '/rating';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ratable()
    {
        return $this->morphTo('ratable');
    }

    public function getSum()
    {
        return $this->aspect1 + $this->aspect2 + $this->aspect3 + $this->aspect4 + $this->aspect5 + $this->aspect6 + $this->aspect7 + $this->aspect8 + $this->aspect9 + $this->aspect10;
    }

    /**
     * @return array
     */
    public function getRatingArray() : array
    {
        return [
            self::ASPECT1_COLUMN => $this->getAttribute(self::ASPECT1_COLUMN),
            self::ASPECT2_COLUMN => $this->getAttribute(self::ASPECT2_COLUMN),
            self::ASPECT3_COLUMN => $this->getAttribute(self::ASPECT3_COLUMN),
            self::ASPECT4_COLUMN => $this->getAttribute(self::ASPECT4_COLUMN),
            self::ASPECT5_COLUMN => $this->getAttribute(self::ASPECT5_COLUMN),
            self::ASPECT6_COLUMN => $this->getAttribute(self::ASPECT6_COLUMN),
            self::ASPECT7_COLUMN => $this->getAttribute(self::ASPECT7_COLUMN),
            self::ASPECT8_COLUMN => $this->getAttribute(self::ASPECT8_COLUMN),
            self::ASPECT9_COLUMN => $this->getAttribute(self::ASPECT9_COLUMN),
            self::ASPECT10_COLUMN => $this->getAttribute(self::ASPECT10_COLUMN),
        ];
    }

    /**
     * @return array
     */
    public function getRatedAspects() : array
    {
        $aspects = $this->getRatingArray();
        $aspects = array_map(function($key,  $item){
            return ($item) ? $key : null;
        }, array_keys($aspects), $aspects);
        return array_filter($aspects, function($item){
            return $item != null;
        });
    }

    /**
     * @return array
     */
    public static function getEmptyRatingArray() : array
    {
        return [
            self::ASPECT1_COLUMN => false,
            self::ASPECT2_COLUMN => false,
            self::ASPECT3_COLUMN => false,
            self::ASPECT4_COLUMN => false,
            self::ASPECT5_COLUMN => false,
            self::ASPECT6_COLUMN => false,
            self::ASPECT7_COLUMN => false,
            self::ASPECT8_COLUMN => false,
            self::ASPECT9_COLUMN => false,
            self::ASPECT10_COLUMN => false,
        ];
    }
}

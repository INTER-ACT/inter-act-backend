<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 10.01.18
 * Time: 17:10
 */

namespace App\Http\Resources\StatisticsResources;


class CommentRatingStatisticsResourceData
{
    /** @var string */
    protected $creation_date;
    /** @var string */
    protected $comment_path;
    /** @var int */
    protected $positive_rating_count;
    /** @var int */
    protected $negative_rating_count;
    /** @var float */
    protected $age_q1_pos;
    /** @var float */
    protected $age_q2_pos;
    /** @var float */
    protected $age_q3_pos;
    /** @var float */
    protected $age_q1_neg;
    /** @var float */
    protected $age_q2_neg;
    /** @var float */
    protected $age_q3_neg;
    /** @var int */
    protected $sentiment;

    /**
     * CommentRatingStatisticsResourceData constructor.
     * @param string $creation_date
     * @param string $comment_path
     * @param int $positive_rating_count
     * @param int $negative_rating_count
     * @param int $sentiment
     * @param float $age_q1_pos
     * @param float $age_q2_pos
     * @param float $age_q3_pos
     * @param float $age_q1_neg
     * @param float $age_q2_neg
     * @param float $age_q3_neg
     */
    public function __construct(string $comment_path, string $creation_date, int $positive_rating_count, int $negative_rating_count, int $sentiment, float $age_q1_pos, float $age_q2_pos, float $age_q3_pos, float $age_q1_neg, float $age_q2_neg, float $age_q3_neg)
    {
        $this->comment_path = $comment_path;
        $this->creation_date = $creation_date;
        $this->positive_rating_count = $positive_rating_count;
        $this->negative_rating_count = $negative_rating_count;
        $this->sentiment = $sentiment;
        $this->age_q1_pos = $age_q1_pos;
        $this->age_q2_pos = $age_q2_pos;
        $this->age_q3_pos = $age_q3_pos;
        $this->age_q1_neg = $age_q1_neg;
        $this->age_q2_neg = $age_q2_neg;
        $this->age_q3_neg = $age_q3_neg;
    }

    public function toArray()
    {
        return[
            $this->comment_path,
            $this->creation_date,
            $this->positive_rating_count,
            $this->negative_rating_count,
            $this->sentiment,
            $this->age_q1_pos,
            $this->age_q2_pos,
            $this->age_q3_pos,
            $this->age_q1_neg,
            $this->age_q2_neg,
            $this->age_q3_neg
        ];
    }

    public function toArrayFull()
    {
        return[
            'comment_path' => $this->comment_path,
            'creation_date' => $this->creation_date,
            'positive_rating_count' => $this->positive_rating_count,
            'negative_rating_count' => $this->negative_rating_count,
            'sentiment' => $this->sentiment,
            'age_q1_pos' => $this->age_q1_pos,
            'age_q2_pos' => $this->age_q2_pos,
            'age_q3_pos' => $this->age_q3_pos,
            'age_q1_neg' => $this->age_q1_neg,
            'age_q2_neg' => $this->age_q2_neg,
            'age_q3_neg' => $this->age_q3_neg
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \GuzzleHttp\json_encode($this->toArray());
    }
}
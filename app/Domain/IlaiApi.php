<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:09
 */

namespace App\Domain;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class IlaiApi
{
    /**
     * @param string $text
     * @return array
     */
    public static function getTagsForText(string $text) : array
    {
        $client = new Client();
        $res = $client->get('https://ilai.inter-act.at/getTags?text=' . $text);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        return $responseData;
    }

    /**
     * @param string $text
     * @param array $tags
     * @return void
     */
    public static function sendTags(string $text, array $tags) : void
    {
        $request = new Request('POST', 'https://ilai.inter-act.at/postTags');
        $client = new Client();
        $client->send($request, ['text' => $text, 'tags' => $tags]);
    }

    /**
     * @param string $text
     * @return int
     */
    public static function getSentimentForText(string $text) : int
    {
        $client = new Client();
        $res = $client->get('https://ilai.inter-act.at/getSentiment?text=' . $text);
        return \GuzzleHttp\json_decode($res->getBody(), true)['sentiment'];
    }
}
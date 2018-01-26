<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 20.01.18
 * Time: 19:09
 */

namespace App\Domain;


use App\Tags\Tag;
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
        return [Tag::getDownloadUndStreaming(), Tag::getSozialeMedien()];
        $id = 1;
        $client = new Client();
        $inputData = [
            "auth_token" => env('ILAI_TOKEN'),
            "texts" => [
                [
                    'text_id' => $id,
                    'text' => $text
                ]
            ],
            "threshold" => 60
        ];
        $res = $client->request('POST', 'https://ilai.inter-act.at/tagging/predict', $inputData);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        return $responseData[0]['tags'];    //TODO: convert tag-strings to Tags
    }

    /**
     * @param string $text
     * @param array $tags
     * @return void
     */
    public static function sendTags(string $text, array $tags) : void
    {
        $request = new Request('POST', 'https://ilai.inter-act.at/datasets');
        $data = [
            'name' => 'tagging_dataset',
            'service' => 'tagging',
            'data' => [
                [
                    'text' => $text,
                    'tags' => collect($tags)->pluck('name')
                ]
            ]
        ];
        $client = new Client();
        $client->send($request, $data);
    }

    /**
     * @param string $text
     * @return int
     */
    public static function getSentimentForText(string $text) : int
    {
        return 1;
        $id = 1;
        $client = new Client();
        $inputData = [
            "auth_token" => env('ILAI_TOKEN'),
            "texts" => [
                [
                    'text_id' => $id,
                    'text' => $text
                ]
            ],
            "threshold" => 60
        ];
        $res = $client->request('POST', 'https://ilai.inter-act.at/sentiment/predict', $inputData);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        return $responseData[0]['tags'][0]; //TODO: convert string to int
    }
}
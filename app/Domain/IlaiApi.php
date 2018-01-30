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

class IlaiApi //TODO: add timeout for response
{
    const PRO_STRING = 'PRO';
    const CONTRA_STRING = 'CONTRA';

    /**
     * @param string $text
     * @return array
     */
    public static function getTagsForText(string $text) : array
    {
        //return [Tag::getDownloadUndStreaming(), Tag::getSozialeMedien()];
        $id = 1;

        $inputData = [
            "texts" => [
                [
                    'text_id' => $id,
                    'text' => $text
                ]
            ],
            "threshold" => 60
        ];
        $res = self::getResponseForRequest('POST', 'https://ilai.inter-act.at/tagging/predict', $inputData);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        return $responseData[0]['tags'];
    }

    /**
     * @param string $text
     * @param array $tags
     * @return void
     */
    public static function sendTags(string $text, array $tags) : void
    {
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
        $res = self::getResponseForRequest('POST', 'https://ilai.inter-act.at/datasets', $data);
    }

    /**
     * @param string $text
     * @return int
     */
    public static function getSentimentForText(string $text) : int
    {
        $id = 1;
        $inputData = [
            "texts" => [
                [
                    'text_id' => $id,
                    'text' => $text
                ]
            ],
            "threshold" => 60
        ];
        $res = self::getResponseForRequest('POST', 'https://ilai.inter-act.at/sentiment/predict', $inputData);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        $sentiment_string = $responseData[0]['tags'][0];
        return (int)($sentiment_string == self::PRO_STRING) ? 1 : ($sentiment_string == self::CONTRA_STRING) ? -1 : 0;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $inputData
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected static function getResponseForRequest(string $method, string $url, array $inputData = [])
    {
        $client = new Client();
        $token = config('app.ilai_token');
        $request = new Request($method, $url, ['content-type' => 'application/json', 'Authorization' => 'Token ' . $token], $inputData);
        return $client->send($request);
    }
}
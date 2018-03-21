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
    const PRO_STRING = 'PRO';
    const CONTRA_STRING = 'CONTRA';

    /**
     * @param string $text
     * @return array
     */
    public static function getTagsForText(string $text) : array
    {
        $id = 3;
        $inputData = [
            "texts" => [
                [
                    "text_id" => $id,
                    "text" => $text
                ]
            ],
            "threshold" => 60
        ];
        $res = self::getResponseForRequest('POST', self::getIlaiPath('/tagging/predict'), $inputData);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        $tag_array = $responseData[0]['tags'];
        return array_map(function($item){
            return TagRepository::getByName($item);
        }, $tag_array);
    }

    /**
     * @param string $text
     * @param array $tag_names
     * @return void
     */
    public static function sendTags(string $text, array $tag_names) : void
    {
        $data = [
            "name" => "tagging_dataset",
            "service" => "tagging",
            "data" => [
                [
                    "text" => $text,
                    "tags" => $tag_names
                ]
            ]
        ];
        self::getResponseForRequest('POST', self::getIlaiPath('/datasets/'), $data);
    }

    /**
     * @param string $text
     * @return int
     */
    public static function getSentimentForText(string $text) : int
    {
        $id = 4;
        $inputData = [
            "texts" => [
                [
                    "text_id" => $id,
                    "text" => $text
                ]
            ],
            "threshold" => 60
        ];
        $res = self::getResponseForRequest('POST', self::getIlaiPath('/sentiment/predict'), $inputData);
        $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
        $sentiment_string = $responseData[0]['tags'][0];
        return (int)($sentiment_string == self::PRO_STRING) ? 1 : (($sentiment_string == self::CONTRA_STRING) ? -1 : 0);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $inputData
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected static function getResponseForRequest(string $method, string $url, array $inputData = null)
    {
        $client = new Client();
        $token = config('app.ilai_token');
        $request = new Request($method, $url, ['content-type' => 'application/json', 'Authorization' => 'Token ' . $token], json_encode($inputData));
        return $client->send($request, ['timeout' => 15, 'connect_timeout' => 5]);
    }

    /**
     * @param string $path
     * @return string
     */
    protected static function getIlaiPath(string $path) : string
    {
        return config('app.ilai_path') . $path;
    }
}
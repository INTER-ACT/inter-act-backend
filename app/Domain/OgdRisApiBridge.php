<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.01.18
 * Time: 09:49
 */

namespace App\Domain;


use GuzzleHttp\Client;
use \Orchestra\Parser\Xml\Facade as XmlParser;

class OgdRisApiBridge
{
    public static function getAllTexts()
    {
        $client = new Client();
        //https://data.bka.gv.at/ris/api/v2.5/help
        //https://data.bka.gv.at/ris/api/v2.5/applications/bundesgesetzblaetter
        $res = $client->get('http://www.ris.bka.gv.at/Dokumente/BgblAuth/BGBLA_2015_I_99/BGBLA_2015_I_99.xml');
        return $res;
        $xml = XmlParser::extract($res->getBody());
        return $xml->getContent();
    }

    public static function getTextFromUrl(string $url)
    {
        $client = new Client();
        $res = $client->get($url);
        return $res;
    }
}
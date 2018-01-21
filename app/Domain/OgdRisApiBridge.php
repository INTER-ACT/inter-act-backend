<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.01.18
 * Time: 09:49
 */

namespace App\Domain;


use GuzzleHttp\Client;
use Mockery\Exception;
use \Orchestra\Parser\Xml\Facade as XmlParser;

class OgdRisApiBridge
{
    public static function getAllTexts()
    {
        $client = new Client();
        //https://data.bka.gv.at/ris/api/v2.5/help
        //https://data.bka.gv.at/ris/api/v2.5/applications/bundesgesetzblaetter
        $documents = [];
        $pageNumber = 0;
        $totalFetched = 0;
        do {
            $pageNumber++;
            $res = $client->get('https://data.bka.gv.at/ris/api/v2.5/bundesnormen?Gesetzesnummer=10001848&DokumenteProSeite=OneHundred&Seitennummer=' . $pageNumber);
            $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
            $hits = $responseData["OgdSearchResult"]["OgdDocumentResults"]["Hits"];
            $pageNumber = (int)$hits["@pageNumber"];
            $pageSize = (int)$hits["@pageSize"];
            $total = (int)$hits["#text"];
            $documents = array_merge($documents, $responseData["OgdSearchResult"]["OgdDocumentResults"]["OgdDocumentReference"]);
            $docsFetched = $pageNumber * $pageSize;
            $totalFetched += $docsFetched;
        }while($docsFetched < $total and $totalFetched < 10000);    //TODO: remove anti-endless-loop if not necessary

        $law_resources = self::mapDocumentsToLawResources($documents);
        array_walk($law_resources, function(&$item, $key){
            $item = $item->toArray();
        });
        return $law_resources;

        $xml = XmlParser::extract($res->getBody());
        return $xml->getContent();
    }

    private static function mapDocumentsToLawResources(array $documents) : array
    {
        $law_resources = [];
        foreach ($documents as $document)
        {
            $metadata = $document["Data"]["Metadaten"]["Bundes-Landesnormen"];
            if(array_key_exists("Ausserkrafttretedatum", $metadata))
                continue;

            $url = $document["Data"]["Dokumentliste"]["ContentReference"]["Urls"]["ContentUrl"][0]["Url"];  //TODO: take xml/html/both?
            $shortTitle = $metadata["Kurztitel"];
            $title = (array_key_exists("Langtitel", $metadata)) ? $metadata["Langtitel"] : "";
            $proclamationOrgan = $metadata["Kundmachungsorgan"];
            $articleParagraphUnit = $metadata["ArtikelParagraphAnlage"];
            $documentType = $metadata["Dokumenttyp"];
            //TODO? change type to Artikel/Paragraph depending on if "Artikelnummer" or "Paragraphnummer" exists and also include article-/paragraph-number
            $dateOfComingIntoEffect = $metadata["Inkrafttretedatum"];

            array_push($law_resources, new LawResource($url, $shortTitle, $title, $proclamationOrgan, $articleParagraphUnit, $documentType, $dateOfComingIntoEffect));
        }
        return $law_resources;
    }

    public static function getParagraphFromUrl(string $url) //TODO: take sth as parameter that identifies the paragraph (like ID)
    {
        $client = new Client();
        $res = $client->get($url);
        return $res;
    }
}
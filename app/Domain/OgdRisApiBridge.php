<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 16.01.18
 * Time: 09:49
 */

namespace App\Domain;


use GuzzleHttp\Client;
use SimpleXMLElement;

class OgdRisApiBridge
{
    public const NORM_FETCH_PATH = 'https://data.bka.gv.at/ris/api/v2.5/bundesnormen';
    public const DOC_FETCH_PATH = 'https://www.ris.bka.gv.at/Dokumente/Bundesnormen';

    public static function getAllTexts(PageRequest $pageRequest) : array
    {
        //TODO if time left: fetch only as much as needed for pagination (care though)
        $client = new Client();
        $documents = [];
        $pageNumber = 0;
        $totalFetched = 0;
        do {
            $pageNumber++;
            $res = $client->get(self::NORM_FETCH_PATH . '?Gesetzesnummer=10001848&DokumenteProSeite=OneHundred&Seitennummer=' . $pageNumber);
            $responseData = \GuzzleHttp\json_decode($res->getBody(), true);
            $hits = $responseData["OgdSearchResult"]["OgdDocumentResults"]["Hits"];
            $pageNumber = (int)$hits["@pageNumber"];
            $pageSize = (int)$hits["@pageSize"];
            $total = (int)$hits["#text"];
            $documents = array_merge($documents, $responseData["OgdSearchResult"]["OgdDocumentResults"]["OgdDocumentReference"]);
            $docsFetched = $pageNumber * $pageSize;
            $totalFetched += $docsFetched;
        }while($docsFetched < $total and $totalFetched < 10000);

        $law_resources = self::mapDocumentsToLawResources($documents);
        return $law_resources;
    }

    /**
     * @param string $id
     * @return LawInformation
     */
    public static function getParagraphFromId(string $id) : LawInformation
    {
        $client = new Client();
        $fetch_path = self::DOC_FETCH_PATH. '/' . $id . '/' . $id . '.xml';
        $res = $client->get($fetch_path);
        $xmlString = $res->getBody()->getContents();
        $doc = new SimpleXMLElement($xmlString);
        $doc->registerXPathNamespace('a', 'http://www.bka.gv.at');
        $headerElements = $doc->xpath('//a:ueberschrift[@typ="para"]');
        if(sizeof($headerElements) > 0)
            $header = (string)$headerElements[0];
        else
            $header = '-';
        $contentElements = $doc->xpath('//a:absatz[@typ="abs"]');
        $content = '';
        foreach ($contentElements as $element)
        {
            $content .= (string)$element . "\r\n ";
        }
        /*$content = [];
        $xmlElements = $doc->xpath('//a:*[@ct="text"]');
        foreach ($xmlElements as $element)
        {
            $contentString .= (string)$element . "\r\n";
            $identifier = "content";
            if($element->getName() == 'ueberschrift')
                $identifier = 'header';
            array_push($content, [$identifier => (string)$element]);
            //$content[$identifier] = (string)$element;
        }*/
        return new LawInformation($id, url('/law_texts/' . $id), $header, $content);
    }

    /**
     * @param array $documents
     * @return array
     */
    private static function mapDocumentsToLawResources(array $documents) : array
    {
        $law_resources = [];
        foreach ($documents as $document)
        {
            if(array_key_exists("Ausserkrafttretedatum", $document["Data"]["Metadaten"]["Bundes-Landesnormen"]))
                continue;
            array_push($law_resources, self::mapDocumentToLawResource($document));
        }
        return $law_resources;
    }

    /**
     * @param array $document
     * @return LawResourceShort
     */
    private static function mapDocumentToLawResource(array $document) : LawResourceShort
    {
        $metadata = $document["Data"]["Metadaten"]["Bundes-Landesnormen"];
        $id = $document["Data"]["Metadaten"]["Technisch"]["ID"];
        //$url = $document["Data"]["Dokumentliste"]["ContentReference"]["Urls"]["ContentUrl"][1]["Url"];
        $url = url('/law_texts/' . $id);
        $articleParagraphUnit = $metadata["ArtikelParagraphAnlage"];
        $paragraph = self::getParagraphFromId($id);

        return new LawResourceShort($id, $url, $paragraph->title, $articleParagraphUnit);
    }
}
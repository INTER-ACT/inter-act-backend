<?php

namespace App\Tags;

use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Discussions\Discussion;
use App\IModel;
use App\IRestResource;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model implements IRestResource
{
    //region constants
    const NUTZUNG_FREMDER_INHALTE_NAME = "Lizenz";
    const SOZIALE_MEDIEN_NAME = "Soziale Medien";
    const KULTURELLES_ERBE_NAME = "Kuturelles Erbe";
    const BILDUNG_UND_WISSENSCHAFT_NAME = "Bildung & Wissenschaft";
    const FREIHEITEN_DER_NUTZER_NAME = "Freiheiten der Nutzer";
    const RESPEKT_UND_ANERKENNUNG_NAME = "Respekt und Anerkennung";
    const RECHTEINHABERSCHAFT_NAME = "Rechteinhaberschaft";
    const DOWNLOAD_UND_STREAMING_NAME = "Download und Streaming";
    const WIRTSCHAFTLICHE_INTERESSEN_NAME = "Wirtschaftliche Interessen";
    const USER_GENERATED_CONTENT_NAME = "User Generated Content";
    //endregion

    protected $fillable = [
        'name', 'description'
    ];

    protected $hidden = ['pivot'];

    //region constant_entries
    public static function getNutzungFremderInhalte()
    {
        return Tag::firstOrCreate(['name' => Tag::NUTZUNG_FREMDER_INHALTE_NAME, 'description' => 'Alles rund um die Nutzung fremder Inhalte']);
    }

    public static function getSozialeMedien()
    {
        return Tag::firstOrCreate(['name' => Tag::SOZIALE_MEDIEN_NAME, 'description' => 'Alles rund um Soziale Medien']);
    }

    public static function getKulturellesErbe()
    {
        return Tag::firstOrCreate(['name' => Tag::KULTURELLES_ERBE_NAME, 'description' => 'Alles rund um kulturelles Erbe']);
    }

    public static function getBildungUndWissenschaft()
    {
        return Tag::firstOrCreate(['name' => Tag::BILDUNG_UND_WISSENSCHAFT_NAME, 'description' => 'Alles rund um Bildung und Wissenschaft']);
    }

    public static function getFreiheitenDerNutzer()
    {
        return Tag::firstOrCreate(['name' => Tag::FREIHEITEN_DER_NUTZER_NAME, 'description' => 'Alles rund um Freiheiten der Nutzer']);
    }

    public static function getRespektUndAnerkennung()
    {
        return Tag::firstOrCreate(['name' => Tag::RESPEKT_UND_ANERKENNUNG_NAME, 'description' => 'Alles rund um Respekt und Anerkennung']);
    }

    public static function getRechteinhaberschaft()
    {
        return Tag::firstOrCreate(['name' => Tag::RECHTEINHABERSCHAFT_NAME, 'description' => 'Alles rund um Rechteinhaberschaft']);
    }

    public static function getDownloadUndStreaming()
    {
        return Tag::firstOrCreate(['name' => Tag::DOWNLOAD_UND_STREAMING_NAME, 'description' => 'Alles rund um Downloads und Streaming']);
    }

    public static function getWirtschaftlicheInteressen()
    {
        return Tag::firstOrCreate(['name' => Tag::WIRTSCHAFTLICHE_INTERESSEN_NAME, 'description' => 'Alles rund um wirtschaftliche Interessen']);
    }

    public static function getUserGeneratedContent()
    {
        return Tag::firstOrCreate(['name' => Tag::USER_GENERATED_CONTENT_NAME, 'description' => 'Alles rund um User Generated Content']);
    }
    //endregion

    //region IRestResource
    function getIdProperty()
    {
        return $this->id;
    }

    public function getType()
    {
        return get_class($this);
    }

    public function getResourcePath()
    {
        return '/tags/' . $this->id;
    }
    //endregion

    //region relations
    public function taggables()
    {
        return $this->with(['discussions', 'amendments', 'sub_amendments']);    //Not sure if this works
    }

    public function discussions()
    {
        return $this->morphedByMany(Discussion::class, 'taggable');
    }

    public function amendments()
    {
        return $this->morphedByMany(Amendment::class, 'taggable');
    }

    public function sub_amendments()
    {
        return $this->morphedByMany(SubAmendment::class, 'taggable');
    }
    //endregion
}

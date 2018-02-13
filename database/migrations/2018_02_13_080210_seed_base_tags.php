<?php

use App\Tags\Tag;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedBaseTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $models = [
            ['name' => Tag::NUTZUNG_FREMDER_INHALTE_NAME, 'description' => 'Alles rund um die Nutzung fremder Inhalte'],
            ['name' => Tag::SOZIALE_MEDIEN_NAME, 'description' => 'Alles rund um Soziale Medien'],
            ['name' => Tag::KULTURELLES_ERBE_NAME, 'description' => 'Alles rund um kulturelles Erbe'],
            ['name' => Tag::BILDUNG_UND_WISSENSCHAFT_NAME, 'description' => 'Alles rund um Bildung und Wissenschaft'],
            ['name' => Tag::FREIHEITEN_DER_NUTZER_NAME, 'description' => 'Alles rund um Freiheiten der Nutzer'],
            ['name' => Tag::RESPEKT_UND_ANERKENNUNG_NAME, 'description' => 'Alles rund um Respekt und Anerkennung'],
            ['name' => Tag::RECHTEINHABERSCHAFT_NAME, 'description' => 'Alles rund um Rechteinhaberschaft'],
            ['name' => Tag::DOWNLOAD_UND_STREAMING_NAME, 'description' => 'Alles rund um Downloads und Streaming'],
            ['name' => Tag::WIRTSCHAFTLICHE_INTERESSEN_NAME, 'description' => 'Alles rund um wirtschaftliche Interessen'],
            ['name' => Tag::USER_GENERATED_CONTENT_NAME, 'description' => 'Alles rund um User Generated Content']
        ];
        DB::table('tags')->insert($models);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tag_names = [
            Tag::NUTZUNG_FREMDER_INHALTE_NAME,
            Tag::SOZIALE_MEDIEN_NAME,
            Tag::KULTURELLES_ERBE_NAME,
            Tag::BILDUNG_UND_WISSENSCHAFT_NAME,
            Tag::FREIHEITEN_DER_NUTZER_NAME,
            Tag::RESPEKT_UND_ANERKENNUNG_NAME,
            Tag::RECHTEINHABERSCHAFT_NAME,
            Tag::DOWNLOAD_UND_STREAMING_NAME,
            Tag::WIRTSCHAFTLICHE_INTERESSEN_NAME,
            Tag::USER_GENERATED_CONTENT_NAME
        ];
        DB::table('tags')->whereIn('name', $tag_names)->delete();
    }
}

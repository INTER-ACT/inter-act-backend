<?php

use App\Model\ModelFactory;
use Carbon\Carbon;
use Faker\Generator as Faker;
use App\Role;
use Illuminate\Database\Seeder;
use App\Tags\Tag;
use App\Amendments\SubAmendment;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param Faker $faker
     * @return void
     */
    public function run(Faker $faker)
    {
        //region Roles
        $admin = Role::getAdmin();
        $expert = Role::getExpert();
        $analyst = Role::getScientist();
        $standard_user = Role::getStandardUser();
        $guest = Role::getGuest();

        //endregion

        //region Tags
        $fremdeInhalte = Tag::getNutzungFremderInhalte();
        $socialMedia = Tag::getSozialeMedien();
        $kultErbe = Tag::getKulturellesErbe();
        $bildungWissenschaft = Tag::getBildungUndWissenschaft();
        $freiheitenNutzer = Tag::getFreiheitenDerNutzer();
        $respektAnerkennung = Tag::getRespektUndAnerkennung();
        $rechteInhaberschaft = Tag::getRechteinhaberschaft();
        $downloadStreaming = Tag::getDownloadUndStreaming();
        $wirtschaftlicheInteressen = Tag::getWirtschaftlicheInteressen();
        $userGeneratedContent = Tag::getUserGeneratedContent();

        //endregion

        //region UserResource
        $users = [
            ModelFactory::CreateUser($admin),
            ModelFactory::CreateUser($admin),
            ModelFactory::CreateUser($expert),
            ModelFactory::CreateUser($expert),
            ModelFactory::CreateUser($analyst),
            ModelFactory::CreateUser($analyst),
            ModelFactory::CreateUser($standard_user),
            ModelFactory::CreateUser($standard_user),
            ModelFactory::CreateUser($standard_user),
            ModelFactory::CreateUser($guest),
            ModelFactory::CreateUser($guest)
        ];

        //endregion

        //region Discussions
        $discussions = [
            ModelFactory::CreateDiscussion($users[0], null, [$fremdeInhalte, $socialMedia], Carbon::createFromDate(2017, 1, 1, 2)),
            ModelFactory::CreateDiscussion($users[0], null, null, Carbon::createFromDate(2017, 3, 3, 2)),
            ModelFactory::CreateDiscussion($users[1], null, [$bildungWissenschaft, $freiheitenNutzer, $respektAnerkennung], Carbon::createFromDate(2017, 1, 2, 2)),
            ModelFactory::CreateDiscussion($users[1], null, [$rechteInhaberschaft, $downloadStreaming]),
            ModelFactory::CreateDiscussion($users[0], $faker->dateTimeBetween(), [$userGeneratedContent]),
            ModelFactory::CreateDiscussion($users[1], $faker->dateTimeBetween(), [$socialMedia, $bildungWissenschaft, $respektAnerkennung, $downloadStreaming])
            ];

        //endregion

        /*//region Rating-Aspects
        $aspects = ModelFactory::CreateRatingAspects([
            'fair', 'unfair', 'zielführend', 'nicht zielführend', 'perfekt', 'kompliziert', 'benachteiligend', 'blödsinning', 'interessant', 'unwichtig'
        ]);
        //endregion*/

        //region Amendments
        $amendments = [
            ModelFactory::CreateAmendment($users[0], $discussions[0], [$fremdeInhalte, $socialMedia]),
            ModelFactory::CreateAmendment($users[2], $discussions[0], [$fremdeInhalte]),
            ModelFactory::CreateAmendment($users[4], $discussions[1], [$kultErbe]),
            ModelFactory::CreateAmendment($users[6], $discussions[2], [$bildungWissenschaft, $userGeneratedContent]),
            ModelFactory::CreateAmendment($users[6], $discussions[3], []),
            ModelFactory::CreateAmendment($users[7], $discussions[4], [$kultErbe, $userGeneratedContent])
        ];
        $amendments[0]->created_at = Carbon::createFromDate(2017, 10, 10, 2);
        $amendments[0]->save();
        $amendments[1]->created_at = Carbon::createFromDate(2016, 10, 10, 2);
        $amendments[1]->save();
        $amendments[2]->created_at = Carbon::createFromDate(2018, 1, 1, 2);
        $amendments[2]->save();

        //endregion

        //region SubAmendments
        $subamendments = [
            ModelFactory::CreateSubAmendment($users[1], $amendments[0], [$fremdeInhalte, $socialMedia], SubAmendment::ACCEPTED_STATUS),
            ModelFactory::CreateSubAmendment($users[2], $amendments[0], [$fremdeInhalte], SubAmendment::ACCEPTED_STATUS),
            ModelFactory::CreateSubAmendment($users[3], $amendments[1], [], SubAmendment::REJECTED_STATUS),
            ModelFactory::CreateSubAmendment($users[5], $amendments[2], [$kultErbe, $downloadStreaming], SubAmendment::PENDING_STATUS),
            ModelFactory::CreateSubAmendment($users[6], $amendments[2], [], SubAmendment::PENDING_STATUS),
            ModelFactory::CreateSubAmendment($users[8], $amendments[3], [$kultErbe, $userGeneratedContent], SubAmendment::PENDING_STATUS),
            ModelFactory::CreateSubAmendment($users[8], $amendments[4], [], SubAmendment::PENDING_STATUS),
            ModelFactory::CreateSubAmendment($users[8], $amendments[4], [$freiheitenNutzer], SubAmendment::PENDING_STATUS),
        ];
        $subamendments[0]->created_at = Carbon::createFromDate(2017, 10, 10, 2);
        $subamendments[0]->save();
        $subamendments[1]->created_at = Carbon::createFromDate(2016, 10, 10, 2);
        $subamendments[1]->save();
        $subamendments[2]->created_at = Carbon::createFromDate(2018, 1, 1, 2);
        $subamendments[2]->save();

        //endregion

        //region MA-Ratings
        ModelFactory::CreateMultiAspectRating($users[0], $discussions[0]);
        ModelFactory::CreateMultiAspectRating($users[1], $discussions[0]);
        ModelFactory::CreateMultiAspectRating($users[2], $discussions[0]);
        ModelFactory::CreateMultiAspectRating($users[3], $discussions[0]);
        ModelFactory::CreateMultiAspectRating($users[0], $discussions[1]);
        ModelFactory::CreateMultiAspectRating($users[0], $amendments[0]);
        ModelFactory::CreateMultiAspectRating($users[1], $amendments[0]);
        ModelFactory::CreateMultiAspectRating($users[2], $amendments[0]);
        ModelFactory::CreateMultiAspectRating($users[3], $amendments[0]);
        ModelFactory::CreateMultiAspectRating($users[0], $amendments[1]);
        ModelFactory::CreateMultiAspectRating($users[0], $subamendments[0]);
        ModelFactory::CreateMultiAspectRating($users[1], $subamendments[0]);
        ModelFactory::CreateMultiAspectRating($users[2], $subamendments[0]);
        ModelFactory::CreateMultiAspectRating($users[3], $subamendments[0]);
        ModelFactory::CreateMultiAspectRating($users[0], $subamendments[1]);

        /*foreach ($amendments as $amendment)
            $amendment->rating_aspects()->attach(array_map(function($item){return $item->id;}, $aspects));
        foreach ($subamendments as $subamendment)
            $subamendment->rating_aspects()->attach(array_map(function($item){return $item->id;}, $aspects));
        ModelFactory::CreateRating($users[0], $amendments[0], $aspects[0]);
        ModelFactory::CreateRating($users[2], $amendments[0], $aspects[0]);
        ModelFactory::CreateRating($users[4], $amendments[0], $aspects[1]);
        ModelFactory::CreateRating($users[6], $amendments[0], $aspects[2]);
        ModelFactory::CreateRating($users[6], $subamendments[0], $aspects[3]);
        ModelFactory::CreateRating($users[7], $subamendments[1], $aspects[4]);
        ModelFactory::CreateRating($users[7], $subamendments[2], $aspects[8]);
        ModelFactory::CreateRating($users[8], $amendments[0], $aspects[0]);*/
        //endregion

        //region Comments
        $comments = [
            ModelFactory::CreateComment($users[0], $discussions[1], [$fremdeInhalte, $socialMedia]),
            ModelFactory::CreateComment($users[0], $discussions[1], [$fremdeInhalte])
        ];
        array_push($comments, ModelFactory::CreateComment($users[1], $comments[0], [$socialMedia]));
        array_push($comments, ModelFactory::CreateComment($users[2], $comments[2], [$socialMedia]));
        array_push($comments, ModelFactory::CreateComment($users[3], $discussions[1], [$kultErbe]));
        array_push($comments, ModelFactory::CreateComment($users[3], $amendments[0], [$bildungWissenschaft]));
        array_push($comments, ModelFactory::CreateComment($users[3], $comments[5], [$freiheitenNutzer]));
        array_push($comments, ModelFactory::CreateComment($users[5], $comments[6], [$respektAnerkennung]));
        array_push($comments, ModelFactory::CreateComment($users[6], $amendments[1], [$rechteInhaberschaft]));
        array_push($comments, ModelFactory::CreateComment($users[7], $subamendments[0], [$bildungWissenschaft]));
        array_push($comments, ModelFactory::CreateComment($users[8], $comments[9], [$socialMedia]));
        array_push($comments, ModelFactory::CreateComment($users[9], $comments[10], [$fremdeInhalte]));
        array_push($comments, ModelFactory::CreateComment($users[10], $subamendments[1], [$fremdeInhalte]));
        array_push($comments, ModelFactory::CreateComment($users[10], $comments[12], [$socialMedia]));
        $comments[0]->created_at = Carbon::createFromDate(2017, 10, 10, 2);
        $comments[0]->save();
        $comments[1]->created_at = Carbon::createFromDate(2016, 10, 10, 2);
        $comments[1]->save();
        $comments[2]->created_at = Carbon::createFromDate(2018, 1, 1, 2);
        $comments[2]->save();


        ModelFactory::CreateCommentRating($users[0], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[2], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[4], $comments[0], -1);
        ModelFactory::CreateCommentRating($users[6], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[6], $comments[1], -1);
        ModelFactory::CreateCommentRating($users[7], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[7], $comments[1], 1);
        ModelFactory::CreateCommentRating($users[8], $comments[1], -1);

        //endregion

        //region Reports
        $reports = [
            ModelFactory::CreateReport($users[6], $amendments[0]),
            ModelFactory::CreateReport($users[6], $amendments[1]),
            ModelFactory::CreateReport($users[7], $amendments[0]),
            ModelFactory::CreateReport($users[7], $subamendments[0]),
            ModelFactory::CreateReport($users[8], $comments[0]),
        ];

        //endregion
    }
}

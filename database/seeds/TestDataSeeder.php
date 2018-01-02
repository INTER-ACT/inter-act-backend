<?php

use App\Model\ModelFactory;
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
        //$output = new \Symfony\Component\Console\Output\ConsoleOutput(2);   //TODO: fix output or remove if not necessary anymore
        //$output->writeln('run-start');

        //region Roles
        $admin = Role::getAdmin();
        $expert = Role::getExpert();
        $analyst = Role::getScientist();
        $standard_user = Role::getStandardUser();
        $guest = Role::getGuest();

        //$output->writeln('roles seeded');
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

        //$output->writeln('tags seeded');
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

        //$output->writeln('users seeded');
        //endregion

        //region Discussions
        $discussions = [
            ModelFactory::CreateDiscussion($users[0], null, [$fremdeInhalte, $socialMedia]),
            ModelFactory::CreateDiscussion($users[0], null, null),
            ModelFactory::CreateDiscussion($users[1], null, [$bildungWissenschaft, $freiheitenNutzer, $respektAnerkennung]),
            ModelFactory::CreateDiscussion($users[1], null, [$rechteInhaberschaft, $downloadStreaming]),
            ModelFactory::CreateDiscussion($users[0], $faker->dateTimeBetween(), [$userGeneratedContent]),
            ModelFactory::CreateDiscussion($users[1], $faker->dateTimeBetween(), [$socialMedia, $bildungWissenschaft, $respektAnerkennung, $downloadStreaming])
            ];

        //$output->writeln('discussions seeded');
        //endregion

        //region Rating-Aspects
        $aspects = ModelFactory::CreateRatingAspects([
            'fair', 'unfair', 'zielführend', 'nicht zielführend', 'perfekt', 'kompliziert', 'benachteiligend', 'blödsinning', 'interessant', 'unwichtig'
        ]);
        /*$aspects = [ModelFactory::CreateRatingAspect('fair'),
            ModelFactory::CreateRatingAspect('unfair'),
            ModelFactory::CreateRatingAspect('zielführend'),
            ModelFactory::CreateRatingAspect('nicht zielführend'),
            ModelFactory::CreateRatingAspect('perfekt'),
            ModelFactory::CreateRatingAspect('kompliziert'),
            ModelFactory::CreateRatingAspect('benachteiligend'),
            ModelFactory::CreateRatingAspect('blödsinnig'),
            ModelFactory::CreateRatingAspect('interessant'),
            ModelFactory::CreateRatingAspect('unwichtig')];*/

        //$output->writeln('ratingAspects seeded');
        //endregion

        //region Amendments
        $amendments = [
            ModelFactory::CreateAmendment($users[0], $discussions[0], [$fremdeInhalte, $socialMedia]),
            ModelFactory::CreateAmendment($users[2], $discussions[0], [$fremdeInhalte]),
            ModelFactory::CreateAmendment($users[4], $discussions[1], [$kultErbe]),
            ModelFactory::CreateAmendment($users[6], $discussions[2], [$bildungWissenschaft, $userGeneratedContent]),
            ModelFactory::CreateAmendment($users[6], $discussions[3], []),
            ModelFactory::CreateAmendment($users[7], $discussions[4], [$kultErbe, $userGeneratedContent])
        ];

        //$output->writeln('amendments seeded');
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

        //$output->writeln('subamendments seeded');
        //endregion

        //region MA-Ratings
        foreach ($amendments as $amendment)
            $amendment->rating_aspects()->attach(array_map(function($item){return $item->id;}, $aspects));
        foreach ($subamendments as $subamendment)
            $subamendment->rating_aspects()->attach(array_map(function($item){return $item->id;}, $aspects));

        //$output->writeln('ratingAspects to ratables seeded');

        ModelFactory::CreateRating($users[0], $amendments[0], $aspects[0]);
        ModelFactory::CreateRating($users[2], $amendments[0], $aspects[0]);
        ModelFactory::CreateRating($users[4], $amendments[0], $aspects[1]);
        ModelFactory::CreateRating($users[6], $amendments[0], $aspects[2]);
        ModelFactory::CreateRating($users[6], $subamendments[0], $aspects[3]);
        ModelFactory::CreateRating($users[7], $subamendments[1], $aspects[4]);
        ModelFactory::CreateRating($users[7], $subamendments[2], $aspects[8]);
        ModelFactory::CreateRating($users[8], $amendments[0], $aspects[0]);

        //$output->writeln('ratings seeded');
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

        //$output->writeln('comments seeded');

        ModelFactory::CreateCommentRating($users[0], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[2], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[4], $comments[0], -1);
        ModelFactory::CreateCommentRating($users[6], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[6], $comments[1], -1);
        ModelFactory::CreateCommentRating($users[7], $comments[0], 1);
        ModelFactory::CreateCommentRating($users[7], $comments[1], 1);
        ModelFactory::CreateCommentRating($users[8], $comments[1], -1);

        //$output->writeln('comment_ratings seeded');
        //endregion

        //region Reports
        $reports = [
            ModelFactory::CreateReport($users[6], $amendments[0]),
            ModelFactory::CreateReport($users[6], $amendments[1]),
            ModelFactory::CreateReport($users[7], $amendments[0]),
            ModelFactory::CreateReport($users[7], $subamendments[0]),
            ModelFactory::CreateReport($users[8], $comments[0]),
        ];

        //$output->writeln('reports seeded');
        //endregion
    }
}

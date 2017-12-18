<?php

use Faker\Generator as Faker;
use App\Role;
use Illuminate\Database\Seeder;
use App\User;
use App\Discussions\Discussion;
use App\Tags\Tag;
use App\Amendments\RatingAspect;
use App\Amendments\Amendment;
use App\Amendments\SubAmendment;
use App\Amendments\RatableRatingAspect;
use App\Comments\Comment;
use App\Reports\Report;

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

        $admin = Role::getAdmin();
        $expert = Role::getExpert();
        $analyst = Role::getAnalyst();
        $standard_user = Role::getStandardUser();
        $guest = Role::getGuest();

        //$output->writeln('roles seeded');

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

        $users = [
            $this->CreateUser($admin),
            $this->CreateUser($admin),
            $this->CreateUser($expert),
            $this->CreateUser($expert),
            $this->CreateUser($analyst),
            $this->CreateUser($analyst),
            $this->CreateUser($standard_user),
            $this->CreateUser($standard_user),
            $this->CreateUser($standard_user),
            $this->CreateUser($guest),
            $this->CreateUser($guest)
        ];

        //$output->writeln('users seeded');

        $discussions = [
            $this->CreateDiscussion($users[0], null, [$fremdeInhalte, $socialMedia]),
            $this->CreateDiscussion($users[0], null, null),
            $this->CreateDiscussion($users[1], null, [$bildungWissenschaft, $freiheitenNutzer, $respektAnerkennung]),
            $this->CreateDiscussion($users[1], null, [$rechteInhaberschaft, $downloadStreaming]),
            $this->CreateDiscussion($users[0], $faker->dateTimeBetween(), [$userGeneratedContent]),
            $this->CreateDiscussion($users[1], $faker->dateTimeBetween(), [$socialMedia, $bildungWissenschaft, $respektAnerkennung, $downloadStreaming])
            ];

        //$output->writeln('discussions seeded');

        $aspects = [$this->CreateRatingAspect('fair'),
            $this->CreateRatingAspect('unfair'),
            $this->CreateRatingAspect('zielführend'),
            $this->CreateRatingAspect('nicht zielführend'),
            $this->CreateRatingAspect('perfekt'),
            $this->CreateRatingAspect('kompliziert'),
            $this->CreateRatingAspect('benachteiligend'),
            $this->CreateRatingAspect('blödsinnig'),
            $this->CreateRatingAspect('interessant'),
            $this->CreateRatingAspect('unwichtig')];

        //$output->writeln('ratingAspects seeded');

        $amendments = [
            $this->CreateAmendment($users[0], $discussions[0], [$fremdeInhalte, $socialMedia]),
            $this->CreateAmendment($users[2], $discussions[0], [$fremdeInhalte]),
            $this->CreateAmendment($users[4], $discussions[1], [$kultErbe]),
            $this->CreateAmendment($users[6], $discussions[2], [$bildungWissenschaft, $userGeneratedContent]),
            $this->CreateAmendment($users[6], $discussions[3], []),
            $this->CreateAmendment($users[7], $discussions[4], [$kultErbe, $userGeneratedContent])
        ];

        //$output->writeln('amendments seeded');

        $subamendments = [
            $this->CreateSubAmendment($users[1], $amendments[0], [$fremdeInhalte, $socialMedia], SubAmendment::ACCEPTED_STATUS),
            $this->CreateSubAmendment($users[2], $amendments[0], [$fremdeInhalte], SubAmendment::ACCEPTED_STATUS),
            $this->CreateSubAmendment($users[3], $amendments[1], [], SubAmendment::REJECTED_STATUS),
            $this->CreateSubAmendment($users[5], $amendments[2], [$kultErbe, $downloadStreaming], SubAmendment::PENDING_STATUS),
            $this->CreateSubAmendment($users[6], $amendments[2], [], SubAmendment::PENDING_STATUS),
            $this->CreateSubAmendment($users[8], $amendments[3], [$kultErbe, $userGeneratedContent], SubAmendment::PENDING_STATUS),
            $this->CreateSubAmendment($users[8], $amendments[4], [], SubAmendment::PENDING_STATUS),
            $this->CreateSubAmendment($users[8], $amendments[4], [$freiheitenNutzer], SubAmendment::PENDING_STATUS),
        ];

        //$output->writeln('subamendments seeded');

        foreach ($amendments as $amendment)
            $amendment->rating_aspects()->attach(array_map(function($item){return $item->id;}, $aspects));
        foreach ($subamendments as $subamendment)
            $subamendment->rating_aspects()->attach(array_map(function($item){return $item->id;}, $aspects));

        //$output->writeln('ratingAspects to ratables seeded');

        $this->CreateRating($users[0], $amendments[0], $aspects[0]);
        $this->CreateRating($users[2], $amendments[0], $aspects[0]);
        $this->CreateRating($users[4], $amendments[0], $aspects[1]);
        $this->CreateRating($users[6], $amendments[0], $aspects[2]);
        $this->CreateRating($users[6], $subamendments[0], $aspects[3]);
        $this->CreateRating($users[7], $subamendments[1], $aspects[4]);
        $this->CreateRating($users[7], $subamendments[2], $aspects[8]);
        $this->CreateRating($users[8], $amendments[0], $aspects[0]);

        //$output->writeln('ratings seeded');

        $comments = [
            $this->CreateComment($users[0], $discussions[1]),
            $this->CreateComment($users[0], $discussions[1])
        ];
        array_push($comments, $this->CreateComment($users[1], $comments[0]));
        array_push($comments, $this->CreateComment($users[2], $comments[2]));
        array_push($comments, $this->CreateComment($users[3], $discussions[1]));
        array_push($comments, $this->CreateComment($users[3], $amendments[0]));
        array_push($comments, $this->CreateComment($users[3], $comments[5]));
        array_push($comments, $this->CreateComment($users[5], $comments[6]));
        array_push($comments, $this->CreateComment($users[6], $amendments[1]));
        array_push($comments, $this->CreateComment($users[7], $subamendments[0]));
        array_push($comments, $this->CreateComment($users[8], $comments[9]));
        array_push($comments, $this->CreateComment($users[9], $comments[10]));
        array_push($comments, $this->CreateComment($users[10], $subamendments[1]));
        array_push($comments, $this->CreateComment($users[10], $comments[12]));

        //$output->writeln('comments seeded');

        $this->CreateCommentRating($users[0], $comments[0], 1);
        $this->CreateCommentRating($users[2], $comments[0], 1);
        $this->CreateCommentRating($users[4], $comments[0], -1);
        $this->CreateCommentRating($users[6], $comments[0], 1);
        $this->CreateCommentRating($users[6], $comments[1], -1);
        $this->CreateCommentRating($users[7], $comments[0], 1);
        $this->CreateCommentRating($users[7], $comments[1], 1);
        $this->CreateCommentRating($users[8], $comments[1], -1);

        //$output->writeln('comment_ratings seeded');

        $reports = [
            $this->CreateReport($users[6], $amendments[0]),
            $this->CreateReport($users[6], $amendments[1]),
            $this->CreateReport($users[7], $amendments[0]),
            $this->CreateReport($users[7], $subamendments[0]),
            $this->CreateReport($users[8], $comments[0]),
        ];

        //$output->writeln('reports seeded');
    }

    private function CreateUser(Role $role)
    {
        return factory(User::class)->create([
           'role_id' => $role->id
        ]);
    }

    private function CreateDiscussion(User $user, DateTime $archived_at = null, array $tags = null)
    {
        $discussion = factory(Discussion::class)->create([
            'user_id' => $user->id,
            'archived_at' => $archived_at
        ]);
        if($tags and isset($tags))
            $discussion->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $discussion;
    }

    private function CreateRatingAspect(string $name)
    {
        return RatingAspect::create(['name' => $name]);
    }

    private function CreateAmendment(User $user, Discussion $discussion, array $tags)
    {
        $amendment = factory(Amendment::class)->create([
            'user_id' => $user->id,
            'discussion_id' => $discussion->id
        ]);
        if($tags and isset($tags))
            $amendment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $amendment;
    }

    private function CreateSubAmendment(User $user, Amendment $amendment, array $tags, string $status)  //TODO: make sure that status cannot be wrong (enum or so)
    {
        $subAmendment = factory(SubAmendment::class)->create([
            'user_id' => $user->id,
            'amendment_id' => $amendment->id,
            'status' => $status
        ]);
        if($tags and isset($tags))
            $subAmendment->tags()->attach(array_map(function($item){return $item->id;}, $tags));
        return $subAmendment;
    }

    private function CreateComment(User $user, \App\Comments\ICommentable $parent)
    {
        $comment = factory(Comment::class)->create([
            'user_id' => $user->id,
            'commentable_id' => $parent->getIdProperty(),
            'commentable_type' => get_class($parent)
        ]);
        return $comment;
    }

    private function CreateCommentRating(User $user, Comment $comment, int $rating)
    {
        $comment->rating_users()->attach($user->id, ['rating_score' => $rating]);
    }

    private function CreateRating(User $user, \App\Amendments\IRatable $ratable, RatingAspect $ratingAspect)
    {
        $rating = RatableRatingAspect::where([['ratable_id', '=', $ratable->getIdProperty()], ['ratable_type', '=', get_class($ratable)], ['rating_aspect_id', '=', $ratingAspect->id]])->first();
        if($rating === null)
            return null;
        $rating->user_ratings()->attach($user->id);
        return $rating;
    }

    private function CreateReport(User $user, \App\Reports\IReportable $reportable)
    {
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'reportable_id' => $reportable->getIdProperty(),
            'reportable_type' => get_class($reportable)
        ]);
        return $report;
    }

    function getId($item)
    {
        return $item->id;
    }
}

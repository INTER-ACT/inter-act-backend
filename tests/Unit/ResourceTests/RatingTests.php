<?php

namespace Tests\Unit\ResourceTests;

use App\Amendments\Amendment;
use App\Comments\Comment;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RatingTests extends TestCase
{
    /** @test */
    public function testCommentRatingResource()
    {
        $comment = factory(Comment::class)->create();
        $user = factory(User::class)->create();

    }

    /** @test */
    public function testMultiAspectRatingResource()
    {
        $amendment = factory(Amendment::class)->states('user', 'discussion')->create();
        $user = factory(User::class)->create();

    }
}

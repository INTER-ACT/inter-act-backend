<?php

namespace App\Providers;

use App\Comments\Comment;
use App\Discussions\Discussion;
use App\Http\Controllers\Auth\AuthenticationRoutes;
use App\Policies\CommentPolicy;
use App\Policies\DiscussionPolicy;
use App\Policies\ReportPolicy;
use App\Reports\Report;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        Discussion::class => DiscussionPolicy::class,
        Comment::class => CommentPolicy::class,
        Report::class => ReportPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        AuthenticationRoutes::routes();


        Passport::tokensExpireIn(now()->addDays(15));

        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}

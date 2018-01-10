<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Routing\Registrar as Router;


/**
 * Implements Routes For Oauth2 Authentication with Passport
 * Including:
 *
 * Class AuthenticationRoutes
 * @package App\Http\Controllers\Auth
 */
class AuthenticationRoutes
{
    /**
     * The router implementation.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function all()
    {
        $this->forAuthorization();
        $this->forAccessTokens();
        $this->forTransientTokens();
        $this->forClients();
        $this->forPersonalAccessTokens();
    }

    /**
     * Register the routes needed for authorization.
     *
     * @return void
     */
    public function forAuthorization()
    {
        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->get('/authorize', [
                'uses' => 'AuthorizationController@authorize',
            ]);

            $router->post('/authorize', [
                'uses' => 'ApproveAuthorizationController@approve',
            ]);

            $router->delete('/authorize', [
                'uses' => 'DenyAuthorizationController@deny',
            ]);
        });
    }

    /**
     * Register the routes for retrieving and issuing access tokens.
     *
     * @return void
     */
    public function forAccessTokens()
    {
        $this->router->post('/login', [
            'uses' => 'AccessTokenController@issueToken',
            'middleware' => 'throttle',
        ]);

        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->get('/tokens', [
                'uses' => 'AuthorizedAccessTokenController@forUser',
            ]);

            $router->delete('/logout/{token_id}', [
                'uses' => 'AuthorizedAccessTokenController@destroy',
            ]);
        });
    }

    /**
     * Register the routes needed for refreshing transient tokens.
     *
     * @return void
     */
    public function forTransientTokens()
    {
        $this->router->post('/token/refresh', [
            'middleware' => ['web', 'auth'],
            'uses' => 'TransientTokenController@refresh',
        ]);
    }


    // TODO remove client and personal acces tokens, if possible; or at least deny access to them


    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forClients()
    {
        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->get('/clients', [
                'uses' => 'ClientController@forUser',
            ]);

            $router->post('/clients', [
                'uses' => 'ClientController@store',
            ]);

            $router->put('/clients/{client_id}', [
                'uses' => 'ClientController@update',
            ]);

            $router->delete('/clients/{client_id}', [
                'uses' => 'ClientController@destroy',
            ]);
        });
    }

    /**
     * Register the routes needed for managing personal access tokens.
     *
     * @return void
     */
    public function forPersonalAccessTokens()
    {
        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->get('/scopes', [
                'uses' => 'ScopeController@all',
            ]);

            $router->get('/personal-access-tokens', [
                'uses' => 'PersonalAccessTokenController@forUser',
            ]);

            $router->post('/personal-access-tokens', [
                'uses' => 'PersonalAccessTokenController@store',
            ]);

            $router->delete('/personal-access-tokens/{token_id}', [
                'uses' => 'PersonalAccessTokenController@destroy',
            ]);
        });
    }

    /**
     * Binds the Passport routes into the controller.
     *
     * @param  callable|null  $callback
     * @param  array  $options
     * @return void
     */
    public static function routes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $callback(new AuthenticationRoutes($router));
        });
    }
}
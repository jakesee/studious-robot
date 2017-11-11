<?php

namespace VUOX\Middlewares;

use \VUOX\Models\User;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SessionMiddleware extends Middleware
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct($container, $settings = [])
    {
        parent::__construct($container);

        $defaults = [
            'lifetime'    => '20 minutes',
            'path'        => '/',
            'domain'      => null,
            'secure'      => false,
            'httponly'    => false,
            'name'        => 'slim_session',
            'autorefresh' => false,
        ];
        $settings = array_merge($defaults, $settings);

        if (is_string($lifetime = $settings['lifetime'])) {
            $settings['lifetime'] = strtotime($lifetime) - time();
        }
        $this->settings = $settings;

        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1);
        ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
    }

    /**
     * Called when middleware needs to be executed.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $this->startSession();

        // generate csrf token
        $_SESSION['csrf_token'] = $this->generateCsrf();

        // clear the errors for the next session
        unset($_SESSION['error']);
        unset($_SESSION['warning']);
        unset($_SESSION['danger']);

        // run the route, add things to session, render things
        $response = $next($request, $response);
        
        return $response;
    }

    /**
     * Start session
     */
    protected function startSession()
    {
        $settings = $this->settings;
        $name = $settings['name'];

        session_set_cookie_params(
            $settings['lifetime'],
            $settings['path'],
            $settings['domain'],
            $settings['secure'],
            $settings['httponly']
        );

        $inactive = session_status() === PHP_SESSION_NONE;

        if ($inactive) {
            // Refresh session cookie when "inactive",
            // else PHP won't know we want this to refresh
            if ($settings['autorefresh'] && isset($_COOKIE[$name])) {
                setcookie(
                    $name,
                    $_COOKIE[$name],
                    time() + $settings['lifetime'],
                    $settings['path'],
                    $settings['domain'],
                    $settings['secure'],
                    $settings['httponly']
                );
            }
        }

        session_name($name);
        session_cache_limiter(false);
        if ($inactive) {
            session_start();
        }
    }

    // Expose session as twig extension globals
    // This is the most elegant way to expose $_SESSION to twig
    // All other way will be too early.
    public function getGlobals()
    {
        return ['s' => $_SESSION];
    }

    protected function generateCsrf()
    {
        // prepare CSRF tokens
        $nameKey = $this->container->csrf->getTokenNameKey();
        $valueKey = $this->container->csrf->getTokenValueKey();
        // $name = $request->getAttribute($nameKey);
        // $value = $request->getAttribute($valueKey);
        $name = $this->container->csrf->getTokenName();
        $value = $this->container->csrf->getTokenValue();

        $csrf_token = '<input type="hidden" name="'.$nameKey.'" value="'.$name.'">';
        $csrf_token .= '<input type="hidden" name="'.$valueKey.'" value="'.$value.'">';

        //$this->add('csrf_token', $csrf_token);
        return $csrf_token;
    }

    public function authenticate($email, $password)
    {
        $user = User::where('email', $email)->first(); //null or user

        if(!$user) return false;

        if(!password_verify($password, $user->password)) return false;
        
        $_SESSION['user'] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        return true;
    }

    public function logout()
    {
        unset($_SESSION['user']);
    }

    // check if session is valid, i.e. user signed in
    public function isValid()
    {
        return isset($_SESSION['user']);
    }

    public function getUser()
    {
        $user = User::find($_SESSION['user']['id']);

        // update the session while we are here
        $_SESSION['user'] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        return $user;
    }

    public function get($property)
    {
        if(isset($_SESSION[$property]))
            return $_SESSION[$property];
    }

    public function getOrAdd($property, $data)
    {
        if($this->get($property) === null)
        {
            $this->add($property, $data);
        }
        return $this->get($property);
    }

    public function push($key, $data)
    {
        $_SESSION[$key][] = $data;
    }

    public function add($key, $data)
    {
        $_SESSION[$key] = $data;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function fillForm($params = [])
    {
        $_SESSION['form'] = $params;
    }
}

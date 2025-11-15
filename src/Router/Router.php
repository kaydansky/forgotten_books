<?php

namespace ForgottenBooks\Router;

use ForgottenBooks\Output\OutputBuilder;
use ForgottenBooks\DI\DiResolver;
use ForgottenBooks\DB\DBInstance;
use ForgottenBooks\Helpers\UserInfo;
use ForgottenBooks\Helpers\ResponseCode;
use Delight\Auth\Auth;

class Router
{

    protected $builder;
    protected $resolver;
    protected $auth;
    protected $pathStart = 0;
    protected $namespace = 'ForgottenBooks\Domain\\';
    protected $defaultPage = 'Queue\QueueController';
    protected $loginPage = 'Login\LoginController';
    protected $template = 'index.html';
    protected $publicPages = [null, 'Login', 'Register', 'Confirmation'];
    protected $placeholders = [];
    protected $loggedIn = null;
    
    public $path = [];
    public $output;

    public function __construct(OutputBuilder $builder, DiResolver $resolver)
    {
        $this->builder = $builder;
        $this->resolver = $resolver;
        $this->placeholders = require PATH_TEMPLATES_CONFIG_FILE;
        $this->auth = new Auth(DBInstance::dsn(), null, null, false);
        $this->loggedIn = $this->auth->isLoggedIn();
    }

    public function request()
    {
        $className = null;
        $class = null;

        $uri = filter_input(INPUT_GET, 'uri', FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '/';

        if (isset($uri)) {
            $this->path = explode('/', (string) $uri);
            
            if ($this->pathStart < 1)  {
                array_unshift($this->path, '');
            }

            $className = ucfirst($this->path[1]);
            $class = $this->namespace . $className . '\\' . $className . 'Controller';
        }

        if (empty($className) && $this->loggedIn) {
            $class = $this->namespace . $this->defaultPage;
        } elseif (empty($className) && ! $this->loggedIn) {
            $class = $this->namespace . $this->loginPage;
        } elseif (! class_exists($class)) {
            $this->notFound();
        }

        if (! in_array($className, $this->publicPages)) {
            if (! $this->loggedIn) {
                $class = $this->namespace . $this->loginPage;
            } elseif (! UserInfo::checkSession($this->auth->getUserId())) {
                $this->auth->logOutEverywhere();
                $class = $this->namespace . $this->loginPage;
            }
        }

        $controller = $this->resolver->resolve($class);
        
        if (method_exists($controller, 'inject')) {
            $controller->inject($this->path, $this->auth, $this->builder, $this->resolver);
        }
        
        if (method_exists($controller, 'action')) {
            $controller->action();
        }
        
        return $this->output = $controller->output();
    }

    public function response($output)
    {
        if ($this->loggedIn) {
            if (! isset($_SESSION['user_info'])) {
                UserInfo::setSession($this->auth->getUserId());
            }

            $output['LOGGED_ROLE'] = $_SESSION['user_info']['role'];
            $output['LOGGED_FULL_NAME'] = $_SESSION['user_info']['first_name'] . '&nbsp;' . $_SESSION['user_info']['last_name'];
        }

        $page = $this->builder
                ->setTemplate($this->template)
                ->addBrackets(array_replace($this->placeholders, $output))
                ->build();

        die($page->result);
    }

    private function notFound()
    {
        ResponseCode::code404();
    }
}

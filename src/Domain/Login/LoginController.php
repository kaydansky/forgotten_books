<?php

namespace ForgottenBooks\Domain\Login;

use ForgottenBooks\Helpers\TableEmpty;
use ForgottenBooks\Helpers\Sanitizer;

class LoginController
{
    private $templateLogin = 'Login/Container.html';
    private $templateRegister = 'Register/Container.html';
    private $templateNotification = 'Notification.html';
    private $modelName = 'ForgottenBooks\Domain\Login\LoginModel';
    private $model;
    private $template;
    private $builder;
    private $resolver;
    private $notice;
    private $path = [1,2,3];
    private $auth;

    public function __construct()
    {
        $this->template = TableEmpty::tableContent('users') === null
                ? $this->templateRegister 
                : $this->templateLogin;
    }
    
    public function inject($path, $auth, $builder, $resolver)
    {
        $this->path = array_replace($this->path, $path);
        $this->auth = $auth;
        $this->builder = $builder;
        $this->resolver = $resolver;
        $this->model = $this->resolver->resolve($this->modelName);
        $this->model->inject($auth);
    }
    
    public function action()
    {   
        $email = Sanitizer::sanitize(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $password = Sanitizer::sanitize(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $remember = Sanitizer::sanitize(filter_input(INPUT_POST, 'remember'));
        
        if ($email && $password) {
            $this->notice = $this->model->signIn($email, $password, $remember);
            
            if (! $this->notice) {
                header('location: /');
            }
        }
        
        if ($this->path[2] === 'logout') {
            $this->notice = $this->model->logout();
            
            if (! $this->notice) {
                header('location: /?loggedout=true');
            }
        }
        
        if ($this->path[2] === 'superadmin') {
            $this->notice = 'You are registered as SuperAdmin. '
                    . 'This is one time action allowed if there is no user in '
                    . 'database only. Please sign in.';
        }
        
        if ($this->path[2] === 'reset') {
            $this->notice = 'Password has been created.<br>Please sign in.';
        }
    }

    public function output()
    {
        $container = $this->builder->setTemplate($this->template);

        return [
            'CONTAINER' => $container->template,
            'BODY_STYLE' => ' style="background-color: #686868;"',
            'NOTIFICATION' => $this->notice ? $this->builder->setTemplate($this->templateNotification)->addBrackets(['CONTENT' => $this->notice])->build()->result : null,
        ];
    }
}

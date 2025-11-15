<?php

namespace ForgottenBooks\Domain\Users;

use Delight\Auth\Role;
use ForgottenBooks\Binder\BinderFactory;
use ForgottenBooks\Helpers\Sanitizer;
use ForgottenBooks\Domain\Password\Password;

class UsersController
{
    private $template = 'Users/Container.html';
    private $modelName = 'ForgottenBooks\Domain\Users\UsersModel';
    private $model;
    private $createUserTemplate = 'Users/CreateUser.html';
    private $templateNotification = 'Notification.html';
    private $templateNavigation = 'Users/Navigation.html';
    private $templateFilesDestinations = 'Users/FilesDestinations.html';
    private $pageTitle = 'Manage Workers';
    private $builder;
    private $resolver;
    private $path = [];
    private $auth;
    private $notice;
    private $createUserWidget;
    private $active = 'ACTIVE_USERS';

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
        if (! $this->auth->hasRole(Role::COORDINATOR)) {
            header('location: /');
            die();
        }

        $first_name = Sanitizer::sanitize(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $last_name = Sanitizer::sanitize(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $email = Sanitizer::sanitize(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $level = Sanitizer::sanitize(filter_input(INPUT_POST, 'level', FILTER_SANITIZE_NUMBER_INT));
        $base_pay_rate = filter_input(INPUT_POST, 'base_pay_rate', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $password_current = Sanitizer::sanitize(filter_input(INPUT_POST, 'password_current', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $password = Sanitizer::sanitize(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $password_c = Sanitizer::sanitize(filter_input(INPUT_POST, 'password_c', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $delete_user_id = filter_input(INPUT_POST, 'delete_user_id', FILTER_SANITIZE_NUMBER_INT);
        $ajax_users = filter_input(INPUT_GET, 'ajax_users', FILTER_SANITIZE_NUMBER_INT);
        $ajax_user_id = filter_input(INPUT_POST, 'ajax_user_id', FILTER_SANITIZE_NUMBER_INT);
        $ajax_base_pay_rate = filter_input(INPUT_POST, 'ajax_base_pay_rate', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $ajax_role = filter_input(INPUT_POST, 'ajax_role', FILTER_SANITIZE_NUMBER_INT);
        $reassign_user_id = filter_input(INPUT_POST, 'reassign_user_id', FILTER_SANITIZE_NUMBER_INT);
        
        if (($password_current && $password && $password_c) && $password === $password_c) {
            $this->notice = (new Password($this->model, $this->auth, $password_current, $password))->change();
        }

        if ($first_name && $last_name && $email && $level) {
            $this->notice = $this->model->createNewUser($first_name, $last_name, $email, $level, $base_pay_rate);

            if (! $this->notice) {
                header('location: /users/user_created/');
            }
        }

        if ($this->path[2] === 'user_created') {
            $this->notice = 'Worker has been added.<br>Invitation sent to their email.';
        }

        $this->createUserWidget = $this->builder->setTemplate($this->createUserTemplate)->template;

        if ($delete_user_id) {
            $this->model->deleteUser($delete_user_id);
            die();
        }

        if ($reassign_user_id) {
            $list = '';
            $destinations = $this->model->reassignUser($reassign_user_id);

            foreach ($destinations as $item) {
                $list .= $this->builder->setTemplate($this->templateFilesDestinations)->addBrackets(['SOURCE' => $item['source'], 'DESTINATION' => $item['destination']])->build()->result;
            }

            die($list);
        }

        if ($ajax_user_id && $ajax_base_pay_rate) {
            $this->model->updatePayRare($ajax_user_id, $ajax_base_pay_rate);
            die($ajax_base_pay_rate);
        }

        if ($ajax_user_id && $ajax_role) {
            $this->model->updateRole($ajax_user_id, $ajax_role);
            die($ajax_role);
        }

        if ($ajax_users) {
            die((new BinderFactory())->users($this->model, $this->builder)->json);
        }
    }

    public function output()
    {
        return [
            'CONTAINER' => $this->builder->setTemplate($this->template)->template,
            'PAGE_TITLE' => $this->pageTitle,
            'NOTIFICATION' => $this->notice ? $this->builder->setTemplate($this->templateNotification)->addBrackets(['CONTENT' => $this->notice])->build()->result : null,
            'CREATE_USER' => $this->createUserWidget,
            'NAVIGATION' => $this->builder->setTemplate($this->templateNavigation)->addBrackets([$this->active => ' active'])->build()->result,
        ];
    }
}
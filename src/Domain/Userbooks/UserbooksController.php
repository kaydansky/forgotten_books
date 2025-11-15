<?php
/**
 * @author: AlexK
 * Date: 28-Jan-19
 * Time: 6:16 PM
 */

namespace ForgottenBooks\Domain\Userbooks;

use Delight\Auth\Role;
use ForgottenBooks\Binder\BinderFactory;
use ForgottenBooks\Domain\Queue\BindComments;

class UserbooksController
{
    private $template = 'Userbooks/Container.html';
    private $modelName = 'ForgottenBooks\Domain\Queue\QueueModel';
    private $model;
    private $modelUsersName = 'ForgottenBooks\Domain\Users\UsersModel';
    private $modelUsers;
    private $templateNavigation = 'Users/Navigation.html';
    private $builder;
    private $resolver;
    private $path = [];
    private $auth;
    private $active = 'ACTIVE_USERS';
    private $workerData;

    public function inject($path, $auth, $builder, $resolver)
    {
        $this->path = array_replace($this->path, $path);
        $this->auth = $auth;
        $this->builder = $builder;
        $this->resolver = $resolver;
        $this->model = $this->resolver->resolve($this->modelName);
        $this->modelUsers = $this->resolver->resolve($this->modelUsersName);
    }

    public function action()
    {
        if ($this->auth->hasRole(Role::COORDINATOR)) {
            $this->templateNavigation = 'Users/Navigation.html';
        } else {
            header('location: /');
            die();
        }

        $this->workerData = $this->modelUsers->getUserById($this->path[2]);

        $ajax_user_books = filter_input(INPUT_GET, 'ajax_user_books', FILTER_SANITIZE_NUMBER_INT);
        $ajax_pibn = filter_input(INPUT_POST, 'ajax_pibn', FILTER_SANITIZE_NUMBER_INT);

        if ($ajax_user_books) {
            die((new BinderFactory())->userBooks($this->model, $this->path[2])->json);
        }

        if ($ajax_pibn) {
            die((new BindComments($this->builder))->bind($this->model->getBookComments($ajax_pibn)));
        }
    }

    public function output()
    {
        return [
            'CONTAINER' => $this->builder->setTemplate($this->template)->template,
            'WORKER_NAME' => $this->workerData['first_name'] . ' ' . $this->workerData['last_name'],
            'WORKER_ID' => $this->path[2],
            'NAVIGATION' => $this->builder->setTemplate($this->templateNavigation)->addBrackets([$this->active => ' active'])->build()->result,
        ];
    }
}
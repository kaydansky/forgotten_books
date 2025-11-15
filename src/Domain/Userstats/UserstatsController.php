<?php
/**
 * @author: AlexK
 * Date: 30-Jan-19
 * Time: 11:21 PM
 */

namespace ForgottenBooks\Domain\Userstats;

use Delight\Auth\Role;

class UserstatsController
{
    private $template = 'Userstats/Container.html';
    private $modelUsers = 'ForgottenBooks\Domain\Users\UsersModel';
    private $templateNavigation = 'Users/Navigation.html';
    private $model;
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
        $this->modelUsers = $this->resolver->resolve($this->modelUsers);
        $this->model = new UserstatsModel();
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
    }

    public function output()
    {
        return [
            'CONTAINER' => $this->builder->setTemplate($this->template)->template,
            'WORKER_NAME' => $this->workerData['first_name'] . ' ' . $this->workerData['last_name'],
            'WORKER_ID' => $this->path[2],
            'NAVIGATION' => $this->builder->setTemplate($this->templateNavigation)->addBrackets([$this->active => ' active'])->build()->result,
            'BOOKS_LAST_WEEK' => $this->model->booksLastWeek($this->path[2]),
            'BOOKS_LAST_MONTH' => $this->model->booksLastMonth($this->path[2]),
            'WORDS_LAST_WEEK' => $this->model->wordsLastWeek($this->path[2]),
            'WORDS_LAST_MONTH' => $this->model->wordsLastMonth($this->path[2]),
            'REJECTED_LAST_WEEK' => $this->model->rejectedLastWeek($this->path[2]),
            'REJECTED_LAST_MONTH' => $this->model->rejectedLastMonth($this->path[2]),
        ];
    }
}
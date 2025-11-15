<?php
/**
 * @author: AlexK
 * Date: 02-Feb-19
 * Time: 9:29 PM
 */

namespace ForgottenBooks\Domain\Profile;

use ForgottenBooks\Helpers\Sanitizer;
use ForgottenBooks\Domain\Password\Password;

class ProfileController
{
    private $template = 'Profile/Container.html';
    private $modelName = 'ForgottenBooks\Domain\Users\UsersModel';
    private $model;
    private $templateNavigation = 'Queue/Navigation.html';
    private $templateNotification = 'Notification.html';
    private $builder;
    private $resolver;
    private $path = [];
    private $auth;
    private $notice;
    private $active = 'ACTIVE_PROFILE';

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
        $password_current = Sanitizer::sanitize(filter_input(INPUT_POST, 'password_current', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $password = Sanitizer::sanitize(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $password_c = Sanitizer::sanitize(filter_input(INPUT_POST, 'password_c', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if (($password_current && $password && $password_c) && $password === $password_c) {
            $this->notice = (new Password($this->model, $this->auth, $password_current, $password))->change();
        }
    }

    public function output()
    {
        return [
            'CONTAINER' => $this->builder->setTemplate($this->template)->template,
            'NOTIFICATION' => $this->notice ? $this->builder->setTemplate($this->templateNotification)->addBrackets(['CONTENT' => $this->notice])->build()->result : null,
            'NAVIGATION' => $this->builder->setTemplate($this->templateNavigation)->addBrackets([$this->active => ' active'])->build()->result,
        ];
    }
}
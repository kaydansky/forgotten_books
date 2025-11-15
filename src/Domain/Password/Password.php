<?php
/**
 * @author: AlexK
 * Date: 02-Feb-19
 * Time: 9:14 PM
 */

namespace ForgottenBooks\Domain\Password;


class Password
{
    private $model;
    private $auth;
    private $passwordCurrent;
    private $passwordNew;

    public function __construct($model, $auth, $passwordCurrent, $passwordNew)
    {
        $this->model = $model;
        $this->auth = $auth;
        $this->passwordCurrent = $passwordCurrent;
        $this->passwordNew = $passwordNew;
    }

    public function change()
    {
        $notice = $this->model->changeCurrentPassword(
            $this->passwordCurrent,
            $this->passwordNew,
            'change_password_subject',
            'change_password_body',
            'change_password_alt_body');

        if (! $notice) {
            $this->auth->logOut();
            header('location: /login/reset/');
            die();
        }

        return $notice;
    }
}
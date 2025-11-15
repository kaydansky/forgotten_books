<?php

namespace ForgottenBooks\Domain\Login;

use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\TooManyRequestsException;
use ForgottenBooks\Helpers\UserInfo;

class LoginModel
{
    private $auth;
    
    public function inject($auth)
    {
        $this->auth = $auth;
    }
    
    public function signIn($email, $password, $remember)
    {
        $message = '';
        $rememberDuration = $remember === 'on' ? (int) (60 * 60 * 24 * 30) : null;
        
        try {
            $this->auth->login($email, $password, $rememberDuration);
            
            if ($this->auth->isLoggedIn()) {
                UserInfo::setSession($this->auth->getUserId());
            }
        }
        catch (InvalidEmailException $e) {
            $message = 'Wrong email address';
        }
        catch (InvalidPasswordException $e) {
            $message = 'Wrong password';
        }
        catch (EmailNotVerifiedException $e) {
            $message = 'Email not verified';
        }
        catch (TooManyRequestsException $e) {
            $message = 'Too many requests';
        }
        
        return $message ?: false;
    }
    
    public function logout()
    {
        $message = '';
        
        try {
            $this->auth->logOutEverywhere();
        }
        catch (NotLoggedInException $e) {
            $message = 'Not logged in';
        }
        
        $this->auth->destroySession();
        
        return $message ?: false;
    }
}

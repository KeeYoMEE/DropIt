<?php

namespace Dt\Inc\Pages;

use Dt\Inc\Fe\Environment;
use Dt\Inc\Fe\Page;
use Dt\Inc\Fe\User;

class RegisterPage extends Page
{
    function __construct(Environment &$env)
    {
        parent::__construct($env);
        $this->setTemplate('registerPage');
        $this->make();
    }

    public function make()
    {
        if (isset($this->env->args['registerName'])
            && isset($this->env->args['registerPass'])) {
            $user = new User($this->env);
            if ($user->createUser($this->env->args['registerName'], $this->env->args['registerPass'], 'test')) {
                $this->env->setAction('login');
                $this->env->notice('USER CREATED');
            } else {
                $this->env->error('ERROR USER NOT CREATED');
            }
        } else {
            $this->env->error('FORM IS NOT COMPLETE');
        }
    }
}

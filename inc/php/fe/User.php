<?php

namespace Dt\Inc\Fe;

class User
{
    protected $env;
    protected $db;
    public $session = false;
    public $id = 0;

    function __construct(Environment &$env)
    {
        $this->env = $env;
        $this->db = $this->env->db;
        if (!$this->isUserLogged()) {
            $this->logIn();
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function logIn()
    {
        if (isset($this->env->args['loginName']) && isset($this->env->args['loginPass'])) {
            $username = $this->env->args['loginName'];
            $password = $this->env->args['loginPass'];

            $data = normalizeRow($this->db->select('users', ['id', 'password'], ['username' => $username]));
            if (isset($data['password']) && $data['password'] === hash('sha256', $password)) {

                $hash = hash('sha256', uniqid());
                if ($this->db->update('users', ['session' => $hash], ['username' => $username])) {
                    setcookie('userSession', $hash, time() + (86400 * $this->env->cfg['general']['cookie_lifetime']), "/"); // 86400 = 1 day
                    $this->id = $data['id'];
                    $this->session = $hash;
                    $this->env->notice('User LOGGED in');
                    return true;
                }

                $this->env->error('Some mistake with db');
                return false;
            } else {
                $this->env->error('User WRONG login');
                return false;
            }
        }
        return false;
    }

    public static function logOut()
    {
        setcookie('userSession', '', time() - 3600, "/");
    }

    public function isUserLogged()
    {
        $session = isset($this->env->args['userSession']) ? true : false;
        if ($session) {

            $sess = normalizeRow($this->db->select('users', ['session', 'id'], ['session' => $this->env->args['userSession']]));
            if (empty($sess)) {
                return false;
            } else {
                $this->id = $sess['id'];
                $this->session = $sess['session'];
                return true;
            }
        }
        return false;
    }

    public function createUser($username, $password, $key)
    {
        if (isset($username) && isset($password) && isset($key)) {
            if ($this->db->insert('users', [
                'username' => $username,
                'password' => hash('sha256', $password),
                'hash_key' => $key,
                'session' => '',
            ])) {
                return true;
            }
        }
        return false;
    }
}
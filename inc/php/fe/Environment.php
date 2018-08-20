<?php

namespace Dt\Inc\Fe;

include DIR_LIB . '/tntsearch/TNTSearch.php';

use TeamTNT\TNTSearch\TNTSearch;


class Environment
{
    public $user;
    public $db;
    public $args;
    protected $error;
    protected $notice;
    private $tools = array();
    public $cfg;
    public $action = false;
    public $api;

    function __construct()
    {
        $this->setConfig();
        $this->collectArgs();
        $this->action = $this->getAction();
        $this->setEnv();
        $this->setDb();
        $this->setUser();
    }

    private function setConfig()
    {
        $cfg = new \Dt\Cfg\Config();
        $this->cfg = $cfg->getAll();
        if (!$this->cfg) $this->error('Config not loaded');
    }

    private function setEnv()
    {
        if ($this->cfg['debug']['errors'] === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
        if ($this->cfg['debug']['deepErrors'] === true) {
            ini_set('xdebug.collect_vars', 'on');
            ini_set('xdebug.collect_params', '4');
            ini_set('xdebug.dump_globals', 'on');
            ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
            ini_set('xdebug.show_local_vars', 'on');
        }
        if (isset($this->args['log']) && $this->args['log'] === 'out') {
            User::logOut();
            $this->notice('User Logged Out');
            $this->setAction('login');
        }
        if (isset($this->args['api'])) {
            $this->api = true;
        }
        if (!file_exists(DIR_TMP)) {
            $oldmask = umask(0);  // helpful when used in linux server
            mkdir(DIR_TMP, 0744);
        }
    }

    public function error($msg)
    {
        $this->error[] = $msg;
    }

    public function getTool($name)
    {
        if (isset($this->tools[$name])) {
            return $this->tools[$name];
        } else {
            $this->setTool($name);
            return isset($this->tools[$name]) ? $this->tools[$name] : false;
        }
    }

    private function setTool($name)
    {
        switch ($name) {
            case 'tntsearch':
                $tnt = new TNTSearch;
                $tnt->loadConfig([
                    'driver' => $this->cfg['db']['database_type'],
                    'host' => $this->cfg['db']['server'],
                    'database' => $this->cfg['db']['database_name'],
                    'username' => $this->cfg['db']['username'],
                    'password' => $this->cfg['db']['password'],
                    'storage' => DIR_TMP,
                ]);
                $this->addTool('tntsearch', $tnt);
                break;
        }
    }

    private function addTool($name, $object)
    {
        $this->tools += [$name => $object];
    }

    private function setDb()
    {
        $db = new \Dt\Db\Database($this->cfg['db']);
        if (!$db) $this->error('Db not loaded');
        $this->db = $db->getInstance();
    }

    public function setUser()
    {
        $this->user = new User($this);
        if ($this->user->getId() === 0 && $this->action !== 'register') {
            if ($this->api == false) {
                $this->setAction('login');
            } else {
                exit;
            }
        }
    }

    public function notice($msg)
    {
        $this->notice[] = $msg;
    }

    public function getInfo()
    {
        return array(
            'error' => $this->error,
            'notice' => $this->notice,
        );
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return isset($this->args['a']) ? $this->args['a'] : $this->action;
    }

    public function collectArgs()
    {
        $myGetArgs = filter_input_array(INPUT_GET);
        $myPostArgs = filter_input_array(INPUT_POST);
        $myServerArgs = filter_input_array(INPUT_SERVER);
        $myCookieArgs = filter_input_array(INPUT_COOKIE);

        $arr = array_merge(array($myGetArgs), array($myPostArgs), array($myServerArgs), array($myCookieArgs));

        $this->args = groupArray($arr);

        foreach ($this->args as &$item) {
            $item = $item[0];
        }


        if ($this->cfg['debug']['args'] === true) {
            $this->error(var_export($this->args, true));
        }
    }

    public function __destruct()
    {
        $log = '';

        if ($this->cfg['debug']['log-db-all'])
            $log .= var_export($this->db->log(), true);
        if ($this->cfg['debug']['log-db-err'])
            $log .= var_export($this->db->error(), true);
        $log .= var_export($this->db->problem, true);
        if ($this->cfg['debug']['log-env-notice'])
            $log .= var_export($this->notice, true);
        if ($this->cfg['debug']['log-env-err'])
            $log .= var_export($this->error, true);

        if (!empty($log))
            file_put_contents(DIR_LOG . '/log-' . time() . '.log', $log);
    }
}
<?php

namespace Dt\Inc\Fe;


abstract class Api
{
    public $output = array();
    public $args;
    public $json;
    public $response;
    protected $db;
    protected $env;

    function __construct(Environment &$env)
    {
        $this->env = $env;
        $this->db = $env->db;
        $this->args = $env->args;
    }

    abstract function choose();

    public function getPage()
    {
        if ($this->json === true) {
            $this->output += ['response' => $this->response];
            return json_encode($this->output);
        } else {
            return is_array($this->output) ? 'OUTPUT IS ARRAY' . var_export($this->output, true) : $this->output;
        }
    }

    public function response($response)
    {
        if ($this->json === true) {
            $this->response = $response;
        }
    }
}
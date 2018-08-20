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
    public $header = true;

    function __construct(Environment &$env)
    {
        $this->env = $env;
        $this->db = $env->db;
        $this->args = $env->args;
    }

    abstract function choose();

    public function getPage()
    {
        if (is_array($this->output) && empty($this->output) && $this->header !== 'empty') $this->response = NO_CONTENT;
        !$this->header ?: $this->playHeader($this->response);
        if ($this->json === true) {
            header('Content-Type: application/json');
            return json_encode($this->output);
        } else {
            return is_array($this->output) ? 'OUTPUT IS ARRAY' . var_export($this->output, true) : $this->output;
        }
    }

    public function playHeader($header)
    {
        switch ($header) {
            case 200: //OK
                $code = 200;
                break;
            case 404: //NOT_FOUND
                $code = 404;
                break;
            case 403: //FORBIDDEN
                $code = 403;
                break;
            case 400: //KO
                $code = 400;
                break;
            case 204: //NO_CONTENT
                $code = 204;
                break;
            case 406: //NOT_ACCEPTABLE
                $code = 406;
                break;
        }
        isset($code) ?: $code = CONFLICT; //Conflict
        http_response_code($code);
    }
}
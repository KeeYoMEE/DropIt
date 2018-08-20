<?php

namespace Dt\Inc\Fe;

use Dt\Inc\Pages\HomePage;
use Dt\Inc\Pages\LoginPage;
use Dt\Inc\Pages\RegisterPage;
use Dt\Inc\Pages\HomeApi;
use Dt\Inc\Pages\SearchApi;

class PageFactory
{
	public $page;
	protected $args;
	private $env;

	public function __construct(Environment $env)
	{
		$this->env = $env;
		$this->getPageOutput();
		$this->printPage();
	}

	public function getPageOutput()
	{
        $action = $this->env->action;
		switch ($this->env->action) {
			case 'login':
				$page = new LoginPage($this->env);
				break;
            case 'register':
                $page = new RegisterPage($this->env);
                break;
            case 'homeApi':
                $page = new HomeApi($this->env);
                break;
            case 'searchApi':
                $page = new SearchApi($this->env);
                break;
			default:
				$page = new HomePage($this->env);
		}
		if ($this->env->action !== $action) {
		    $this->getPageOutput();
        } else {
            if ($this->env->api == false) {
                $this->page = $page->getFullPage();
            } else {
                $this->page = $page->getPage();
            }
        }
	}

	public function printPage()
	{
		echo $this->page;
	}
}
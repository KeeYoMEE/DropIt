<?php

namespace Dt\Inc\Pages;

use Dt\Inc\Fe\Environment;
use Dt\Inc\Fe\Page;

class LoginPage extends Page
{
	function __construct(Environment &$env)
	{
		parent::__construct($env);
		$this->make();
	}

	public function make()
	{
        $this->setTemplate('loginPage');
	}
}

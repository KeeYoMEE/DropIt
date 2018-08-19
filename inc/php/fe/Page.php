<?php

namespace Dt\Inc\Fe;

use Dt\Lib\Tpl;

abstract class Page
{
	public $output;
	public $args;
	public $template;
	public $header;
	public $footer;
    public $nav;
	protected $env;
    protected $db;

	function __construct(Environment &$env)
	{
		$this->env = $env;
        $this->db = $env->db;
		$this->args = $env->args;
	}

	abstract function make();

	public function setTemplate($template, $header = 'header', $footer = 'footer')
	{
		$this->template = new Tpl;
		$this->template->setTemplate(DIR_TPL . '/' . $template . '.html');
		$this->header = $header;
		$this->footer = $footer;
	}

	public function setTplVar($array)
	{
		if (isset($this->template)) {
			$this->template->setVars($array);
		} else $this->env->error('Template not set');
	}


	public function getFullPage()
	{
		return $this->getHeader() . $this->getPage() . $this->getFooter();
	}

    public function getFooter()
    {
        $name = $this->footer ? $this->footer : 'footer';
        $footer = new Tpl;
        $footer->setTemplate(DIR_TPL . '/' . $name . '.html');
        $footer->compile();
        return $footer->getCompiled();
	}

	public function getPage()
	{
        if (isset($this->template)) {
			$this->template->compile();
            $this->output .= $this->template->getCompiled();
			return $this->output;
		}
		if (isset($this->output)) {
			return $this->output;
		}
	}

    public function makeNav($name, $params)
    {
        $this->nav .= "<h6 class=\"sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 nav-name\"><span>$name</span><a class=\"d-flex align-items-center\" href=\"#\"><span data-feather=\"plus-circle\"></span></a></h6><ul class=\"nav flex-column mb-2\">";
        foreach ($params as $item) {
            $href = isset($item['href']) ? $item['href'] : '#';
            $id = isset($item['id']) ? 'id="' . $item['id'] . '"' : '';
            $onclick = isset($item['onclick']) ? 'onclick="' . $item['onclick'] . '"' : '';
            $name = $item['name'];
            $this->nav .= "<li class=\"nav-item\"><a class=\"nav-link\" href=\"$href\" $id $onclick><span data-feather=\"file-text\">$name</span></a></li>";
        }
        $this->nav .= "</ul>";
    }

    public function getHeader()
    {
        $name = $this->header ? $this->header : 'header';
        $header = new Tpl;
        $header->setTemplate(DIR_TPL . '/' . $name . '.html');
		$info = $this->env->getInfo();
        $header->setVars(array(
			'errors' => var_export($info['error'], true),
			'notice' => var_export($info['notice'], true),
            'nav' => !empty($this->nav) ? $this->nav : '',
		));
        $header->setVars(array('siteName' => $this->env->cfg['site']['siteName']));
        $header->compile();
        return $header->getCompiled();
	}

}
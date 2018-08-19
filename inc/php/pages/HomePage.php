<?php

namespace Dt\Inc\Pages;

use Dt\Inc\Fe\Environment;
use Dt\Inc\Fe\Page;
use Dt\Inc\Items\Note;

class HomePage extends Page
{
	function __construct(Environment &$env)
	{
		parent::__construct($env);
        $this->make();
	}

	public function make()
	{
        $this->setTemplate('NoteForm');
        $note = new Note($this->env);

        $pinned = $note->getPinned();
        $pins = array();
        foreach ($pinned as $pin) {
            $pins[] = array(
                'name' => $pin['name'],
                'onclick' => 'getNote(' . $pin['id'] . ');'
            );
        }
        $this->makeNav('Pinned Notes', $pins);

        $pinned = $note->getLatelyUsed();
        $pins = array();
        foreach ($pinned as $pin) {
            $pins[] = array(
                'name' => $pin['name'],
                'onclick' => 'getNote(' . $pin['id'] . ');'
            );
        }
        $this->makeNav('Lately Used', $pins);
	}
}

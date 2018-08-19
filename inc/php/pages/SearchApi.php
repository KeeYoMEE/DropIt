<?php

namespace Dt\Inc\Pages;

use Dt\Inc\Fe\Environment;
use Dt\Inc\Fe\Api;
use Dt\Inc\Items\Note;


class SearchApi extends Api
{
    function __construct(Environment &$env)
    {
        parent::__construct($env);
        $this->choose();
    }

    public function choose()
    {
        if (isset($this->args['api'])) {
            switch ($this->args['api']) {
                case 'getnotesbyhashtag':
                    $this->getNotesListByHashtag();
                    break;
                case 'getallhashtags':
                    $this->getAllHashtagsList();
                    break;
                case 'getallnotes':
                    $this->getAllNotesList();
                    break;
                case 'getnotesbyfulltext':
                    $this->getFulltextList();
                    break;
                case 'getnotesbyname':
                    $this->getNotesListByName();
                    break;
                default:
                    return;
            }
        }
    }

    public function getNotesListByHashtag()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        if (isset($this->args['json_string'])) {
            $search = json_decode($this->args['json_string'], true)['whatIWant'];
            if (!empty($search)) {
                if ($notes = $noteClass->getNotesByHashtags($search)) {
                    foreach ($notes as $note) {
                        $data[] = $noteClass->getNote($note);
                    }
                    $args = array('loop' => ['lines' => $data]);
                    $this->output = array('content' => getTemplated('searchNoteList', $args, true));
                    $this->response('OK');
                } else {
                    $this->response('NOT FOUND');
                }
            } else {
                $this->response('KO');
            }
        } else {
            $this->response('KO');
        }
    }

    public function getAllHashtagsList()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        $hashtags = $noteClass->getAllHashtags();
        $args = array('loop' => ['lines' => $hashtags]);
        if ($this->output = array('content' => getTemplated('searchHashtagsList', $args, true))) {
            $this->response('OK');
        } else {
            $this->response('KO');
        }
    }

    public function getAllNotesList()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        if ($notes = $noteClass->getAllNotes()) {
            $args = array('loop' => ['lines' => $notes]);
            if ($this->output = array('content' => getTemplated('searchNoteList', $args, true))) {
                $this->response('OK');
            } else {
                $this->response('KO');
            }
        } else {
            $this->response('NOT FOUND');
        }
    }

    public function getFulltextList()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        if (isset($this->args['json_string'])) {
            $search = json_decode($this->args['json_string'], true)['whatIWant'];
            if (!empty($search)) {
                if ($notes = $noteClass->getNotesByFulltext($search)) {
                    foreach ($notes['ids'] as $id) {
                        $data[] = $noteClass->getNote($id);
                    }
                    if (isset($data)) {
                        $args = array('loop' => ['lines' => $data]);
                        $this->output = array('content' => getTemplated('searchNoteList', $args, true));
                        $this->response('OK');
                    } else $this->response('NOT FOUND');
                } else $this->response('NOT FOUND');
            } else $this->response('KO');
        } else $this->response('KO');
    }

    public function getNotesListByName()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        if (isset($this->args['json_string'])) {
            $search = json_decode($this->args['json_string'], true)['whatIWant'];
            if (!empty($search)) {
                if ($notes = $noteClass->getNotesByName($search)) {
                    foreach ($notes as $note) {
                        $data[] = $noteClass->getNote($note);
                    }
                    $args = array('loop' => ['lines' => $data]);
                    $this->output = array('content' => getTemplated('searchNoteList', $args, true));
                    $this->response('OK');
                } else {
                    $this->response('NOT FOUND');
                }
            } else {
                $this->response('KO');
            }
        } else {
            $this->response('KO');
        }
    }
}
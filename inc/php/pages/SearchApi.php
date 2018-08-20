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
                case 'getNotesByHashtag':
                    $this->getNotesListByHashtag();
                    break;
                case 'getAllHashtags':
                    $this->getAllHashtagsList();
                    break;
                case 'getAllNotes':
                    $this->getAllNotesList();
                    break;
                case 'getNotesByFulltext':
                    $this->getFulltextList();
                    break;
                case 'getNotesByName':
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
        if (isset($this->args['j_data']) || true) {
            //$search = json_decode($this->args['j_data'], true);
            $search['whatIWant'] = 'cool';
            if (isset($search['whatIWant']) && !empty($search['whatIWant'])) {

                $notes = $noteClass->getNotesByHashtags($search['whatIWant']);
                if (is_array($notes) && !empty($notes)) {
                    foreach ($notes as $note) {
                        $data[] = $noteClass->getNote($note);
                    }
                    if (!isset($data)) {
                        $this->response = NOT_FOUND;
                        return;
                    }
                    $args = array('loop' => ['lines' => $data]);

                    $this->output = array('content' => getTemplated('searchNoteList', $args, true));
                    $this->response = OK;

                } else $this->response = NOT_FOUND;
            } else $this->response = NOT_ACCEPTABLE;
        } else $this->response = NOT_ACCEPTABLE;
    }

    public function getAllHashtagsList()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        $hashtags = $noteClass->getAllHashtags();
        $args = array('loop' => ['lines' => $hashtags]);
        if ($this->output = array('content' => getTemplated('searchHashtagsList', $args, true))) {

            $this->response = OK;

        } else $this->response = KO;
    }

    public function getAllNotesList()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        $notes = $noteClass->getAllNotes();
        if ($notes) {
            $args = array('loop' => ['lines' => $notes]);

            $this->output = array('content' => getTemplated('searchNoteList', $args, true));
            $this->response = !empty($this->output) ? OK : KO;

        } else $this->response = NOT_FOUND;
    }

    public function getFulltextList()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        if (isset($this->args['j_data'])) {
            $search = json_decode($this->args['j_data'], true);
            if (isset($search['whatIWant']) && !empty($search['whatIWant'])) {

                $notes = $noteClass->getNotesByFulltext($search['whatIWant']);
                if ($notes) {
                    foreach ($notes['ids'] as $id) {
                        $data[] = $noteClass->getNote($id);
                    }
                    $args = array('loop' => ['lines' => $data]);

                    $this->output = array('content' => getTemplated('searchNoteList', $args, true));
                    $this->response = OK;

                } else $this->response = NOT_FOUND;
            } else $this->response = NOT_ACCEPTABLE;
        } else $this->response = NOT_ACCEPTABLE;
    }

    public function getNotesListByName()
    {
        $this->json = true;
        $noteClass = new Note($this->env);
        if (isset($this->args['j_data'])) {
            $search = json_decode($this->args['j_data'], true)['whatIWant'];
            if (!empty($search)) {

                $notes = $noteClass->getNotesByName($search);
                if ($notes) {
                    foreach ($notes as $note) {
                        $data[] = $noteClass->getNote($note);
                    }
                    $args = array('loop' => ['lines' => $data]);

                    $this->output = array('content' => getTemplated('searchNoteList', $args, true));
                    $this->response = OK;

                } else $this->response = NOT_FOUND;
            } else $this->response = NOT_ACCEPTABLE;
        } else $this->response = NOT_ACCEPTABLE;
    }
}
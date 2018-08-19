<?php

namespace Dt\Inc\Pages;

use Dt\Inc\Fe\Environment;
use Dt\Inc\Fe\Api;
use Dt\Inc\Items\Note;

class HomeApi extends Api
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
                case 'getnote':
                    $this->getNote();
                    break;
                case 'setnote':
                    $this->setNote();
                    break;
                case 'setpinned':
                    $this->setPinned();
                    break;
                case 'setarchived':
                    $this->setArchived();
                    break;
                default:
                    return;
            }
        }
    }

    public function getNote()
    {
        $this->json = true;
        $note = new Note($this->env);
        if (isset($this->args['id'])) {
            $this->output = $note->getNote($this->args['id']);
            $note->tapNote($this->args['id']);
        } else {
            $this->output = array(
                'hashtags' => 'NULL',
                'name' => 'NULL',
                'content' => 'NULL',
                'id' => 0,
            );
        }
    }

    public function setPinned()
    {
        $this->json = true;
        $note = new Note($this->env);
        if (isset($this->args['id']) && !empty($this->args['id'])) {
            if ($note->setPinned($this->args['id'])) {
                $pinned = $this->db->select('home_notes', ['pinned'], ['id' => $this->args['id'], 'created_by' => $this->env->user->getId()]);
                $this->output = array('pinned' => $pinned[0]['pinned']);
                $this->response('OK');
            } else {
                $this->response('KO');
            }
        } else {
            $this->response('KO');
        }
    }

    public function setArchived()
    {
        $this->json = true;
        $note = new Note($this->env);
        if (isset($this->args['id']) && !empty($this->args['id'])) {
            if ($note->setArchived($this->args['id'])) {
                $pinned = $this->db->select('home_notes', ['archived'], ['id' => $this->args['id'], 'created_by' => $this->env->user->getId()]);
                $this->output = array('archived' => $pinned[0]['archived']);
                $this->response('OK');
            } else {
                $this->response('KO');
            }
        } else {
            $this->response('KO');
        }
    }

    public function setNote()
    {
        $this->json = true;
        $note = new Note($this->env);
        if (isset($this->args['json_string'])) {
            $data = json_decode($this->args['json_string'], true);
            if ($note->setNote($data['name'], $data['content'], $data['hashtags'], $data['id'])) {
                if ($tnt = $this->env->getTool('tntsearch')) {
                    $indexer = $tnt->createIndex($this->env->user->getId() . 'note.index');
                    $indexer->query('SELECT id, content FROM home_notes WHERE archived = 0 AND created_by = ' . $this->env->user->getId() . ';');
                    $indexer->run();
                }
                $this->response('OK');
                return;
            }
        }
        $this->response('KO');
    }
}

<?php

namespace Dt\Inc\Items;

use Dt\Inc\Fe\Environment;

class Note
{
    public $content;
    public $createdBy;
    public $hashtags;
    public $hashtagArray;
    protected $env;
    private $db;

    function __construct(Environment &$env)
    {
        $this->env = $env;
        $this->db = $env->db;
        $this->args = $env->args;
    }

    public function setNote($name, $content, $hashtags, $id = null)
    {
        if (!empty($content) && !empty($name) && !empty($hashtags) && $name !== null && $content !== null && $hashtags !== null) {
            if ($id !== null && !empty($id)) {
                $row = normalizeRow($this->db->select('home_notes', ['id'], ['id' => $id, 'name' => $name, 'created_by' => $this->env->user->getId(),]));
                if (!empty($row) && $this->updateNote($id, $content, $hashtags)) {
                    return true;
                }
            }
            if ($this->insertNote($name, $content, $hashtags)) {
                return true;
            }
        }
        return false;
    }

    public function getNote($id)
    {
        if ($arr = $this->db->select('home_notes', ['name', 'content', 'id'], ['id' => $id, 'archived' => 0, 'created_by' => $this->env->user->getId()])) {
            $arr = array_merge(array('hashtags' => $this->getHashtagsByNote($id, true)), $arr[0]);
            return $arr;
        }
        return array(
            'hashtags' => 'NULL',
            'name' => 'NULL',
            'content' => 'NULL',
            'id' => 0,
        );
    }

    public function tapNote($id)
    {
        if ($arr = $this->db->select('home_notes', ['name', 'content', 'id'], ['id' => $id, 'created_by' => $this->env->user->getId()])) {
            return $this->db->update('home_notes', ['last_view' => date('Y-m-d H:i:s')], ['id' => $id]) ? true : false;
        }
        return false;
    }

    public function setPinned($id)
    {
        $pinned = $this->db->select('home_notes', ['pinned'], ['id' => $id, 'created_by' => $this->env->user->getId()]);
        if (!empty($pinned)) {
            if ($pinned[0]['pinned'] == 1) {
                return $this->db->update(
                    'home_notes',
                    ['pinned' => 0]
                    , ['id' => $id,
                    'created_by' => $this->env->user->getId()
                ]) ? true : false;
            } else {
                return $this->db->update(
                    'home_notes',
                    ['pinned' => 1]
                    , ['id' => $id,
                    'created_by' => $this->env->user->getId()
                ]) ? true : false;
            }
        }
        return false;
    }

    public function setArchived($id)
    {
        $pinned = $this->db->select('home_notes', ['archived'], ['id' => $id, 'created_by' => $this->env->user->getId()]);
        if (!empty($pinned)) {
            if ($pinned[0]['archived'] == 1) {
                return $this->db->update(
                    'home_notes',
                    ['archived' => 0]
                    , ['id' => $id,
                    'created_by' => $this->env->user->getId()
                ]) ? true : false;
            } else {
                return $this->db->update(
                    'home_notes',
                    ['archived' => 1]
                    , ['id' => $id,
                    'created_by' => $this->env->user->getId()
                ]) ? true : false;
            }
        }
        return false;
    }

    public function getPinned()
    {
        return $this->db->select(
            'home_notes',
            ['id', 'name'],
            [
                'pinned' => 1,
                'archived' => 0,
                'created_by' => $this->env->user->getId(),
                "ORDER" => ["name" => "DESC"],
            ]);
    }

    public function getLatelyUsed()
    {
        return $this->db->select(
            'home_notes',
            ['id', 'name'],
            [
                'created_by' => $this->env->user->getId(),
                'archived' => 0,
                "LIMIT" => 8,
                "ORDER" => ["last_view" => "DESC", "name" => "DESC"],
            ]);
    }

    public function getHashtagsByNote($id, $string = false)
    {
        $hashtags = normalizeOneList($this->db->query("
SELECT h.name 
FROM home_hashtags h 
INNER JOIN home_notes_to_hashtags nh 
  ON nh.id_hashtags = h.id 
INNER JOIN home_notes n 
  ON nh.id_note = n.id 
WHERE n.id IN ('$id')")->fetchAll());
        if ($string) {
            $text = '';
            foreach ($hashtags as $tag) {
                $text .= '#' . $tag . ' ';
            }
            return $text;
        }
        return $hashtags;
    }

    public function getNotesByHashtagId($id)
    {
        $notes = normalizeOneList($this->db->query("
SELECT n.id 
FROM home_hashtags h 
INNER JOIN home_notes_to_hashtags nh 
  ON nh.id_hashtags = h.id 
INNER JOIN home_notes n 
  ON nh.id_note = n.id 
  AND archived = 0
WHERE h.id IN ('$id') AND n.created_by = " . $this->env->user->getId())->fetchAll());

        return $notes;
    }

    public function setHashtag($id, $hashtag)
    {
        $hashtag = strtolower($hashtag);
        $row = normalizeRow($this->db->select('home_hashtags', ['id'], ['name' => $hashtag]));
        if (empty($row)) {
            $this->db->insert('home_hashtags', [
                'name' => $hashtag,
            ]);
            $row = normalizeRow($this->db->select(
                'home_hashtags',
                ['id'],
                ['name' => $hashtag]));
            $this->db->insert('home_notes_to_hashtags',
                ['id_hashtags' => $row['id'], 'id_note' => $id]);
        } else {
            $row2 = normalizeRow($this->db->select(
                'home_notes_to_hashtags',
                ['id_note'],
                ['id_hashtags' => $hashtag, 'id_note' => $id]));

            if (empty($row2)) {
                $this->db->insert('home_notes_to_hashtags', [
                    'id_hashtags' => $row['id'],
                    'id_note' => $id
                ]);
            }
        }
    }

    public function updateNote($id, $content)
    {
        $this->tapNote($id);
        return $this->db->update(
            'home_notes',
            [
                'content' => $content,
                'last_view' => date('Y-m-d H:i:s'),
            ], [
            'id' => $id,
            'created_by' => $this->env->user->getId()
        ]) ? true : false;
    }

    public function insertNote($name, $content, $hashtags)
    {
        $row = normalizeRow($this->db->select(
            'home_notes',
            ['id'],
            ['name' => $name, 'created_by' => $this->env->user->getId()]));

        if (empty($row)) {

            if ($this->db->insert('home_notes', [
                'created_by' => $this->env->user->getId(),
                'name' => strtolower($name),
                'content' => $content,
                'pinned' => 0,
                'archived' => 0,
            ])) {
                $row = normalizeRow($this->db->select(
                    'home_notes',
                    ['id'],
                    ['name' => $name, 'created_by' => $this->env->user->getId()]));
                $hashtags = $hashtags[0] === '#' ? substr($hashtags, 1) : $hashtags;
                foreach (explode('#', preg_replace('/\s+/', '', $hashtags)) as $tag) {
                    $this->setHashtag($row['id'], $tag);
                }
                return true;
            }
        } else {
            if ($this->updateNote($row['id'], $content)) {
                return true;
            }
        }
        return false;
    }

    public function getNotesByHashtags($word = '', $like = true)
    {
        //TODO: upravit pro usera
        if (empty($word)) {
            $data = $this->db->select('home_hashtags', ['id', 'name']);
        } else if ($like) {
            $data = $this->db->select('home_hashtags', ['id', 'name'], ['name[~]' => strtolower($word)]);
        } else {
            $data = $this->db->select('home_hashtags', ['id', 'name'], ['name' => strtolower($word)]);
        }
        foreach ($data as $name) {
            $id = $this->getNotesByHashtagId($name['id']);
            if (!empty($id)) {
                $ids[] = $id;
            }
        }
        return isset($ids) ? normalizeArray($ids) : false;
    }

    public function getAllHashtags()
    {
        $hashtags = $this->db->query("
SELECT h.name 
FROM home_hashtags h 
INNER JOIN home_notes_to_hashtags nh 
  ON nh.id_hashtags = h.id 
INNER JOIN home_notes n 
  ON nh.id_note = n.id 
  AND archived = 0
WHERE n.created_by IN ('" . $this->env->user->getId() . "')")->fetchAll();

        return $hashtags;
    }

    public function getAllNotes()
    {
        $data = $this->db->select('home_notes', ['id', 'name', 'content'], ['created_by' => $this->env->user->getId(), "ORDER" => ["last_view" => "DESC"]]);
        foreach ($data as $dat) {
            $note = $this->getNote($dat['id']);
            if (!empty($note)) {
                $notes[] = $note;
            }
        }
        return isset($notes) ? $notes : false;
    }

    public function getNotesByFulltext($word)
    {
        if ($tnt = $this->env->getTool('tntsearch')) {
            $tnt->selectIndex($this->env->user->getId() . "note.index");
            $tnt->fuzziness = true;

            return $tnt->search($word);
        }
    }

    public function getNotesByName($word)
    {
        return normalizeOneList($this->db->select('home_notes', ['id'], ['archived' => 0, 'name[~]' => strtolower($word), 'created_by' => $this->env->user->getId()]), 'id');
    }
}

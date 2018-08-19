<?php

use Dt\Lib\Tpl;

function normalizeRow($array)
{
    $output = array();
    if (is_array($array) && !empty($array)) $output = $array[0];
    return $output;
}

function normalizeOneList($array, $id = null)
{
    $output = array();
    if (is_array($array)) {
        foreach ($array as $ar) {
            if ($id !== null) $output[] = $ar[$id];
            else $output[] = $ar[0];
        }
    }
    return $output;
}

function makeList($arr, $class, $type, $custom = null)
{
    $list = '';
    foreach ($arr as $a) {
        if ($custom == null) $list .= "<$type id=\"$a\" class=\"$class\">" . $a . "</$type>";
        else $list .= "<$type id=\"$a\" $custom class=\"$class\">" . $a . "</$type>";
    }
    $list .= '';
    return $list;
}

function makeIdList($arr, $class, $type, $custom = null)
{
    $list = '';
    foreach ($arr as $a) {
        if ($custom == null) $list .= "<$type id=\"$a[0]\" class=\"$class\">" . $a[1] . "</$type>";
        else $list .= "<$type id=\"$a[0]\" $custom class=\"$class\">" . $a[1] . "</$type>";
    }
    $list .= '';
    return $list;
}

function getTemplated($templateName, $args, $loop = false)
{
    $template = new Tpl;
    $template->setTemplate(DIR_TPL . '/' . $templateName . '.html');
    if ($loop) {
        $loop = $args['loop'];
        unset($args['loop']);
        $template->setVars($args);
        $template->setLoops($loop);
    } else {
        $template->setVars($args);
    }
    $template->compile();
    return $template->getCompiled();
}

function groupArray($arr)
{
    foreach ($arr as $subarr) {
        foreach ((array)$subarr as $id => $value) {
            if (!isset($processed[$id])) {
                $processed[$id] = array();
            }
            $processed[$id][] = $value;
        }
    }
    return $processed;
}

function normalizeArray($arr)
{
    foreach ($arr as $subarr) {
        foreach ((array)$subarr as $id => $value) {
            $processed[] = $value;
        }
    }
    return $processed;
}

function checkPermissions($dir)
{
    $perms = fileperms($dir);

    switch ($perms & 0xF000) {
        case 0xC000: // socket
            $info = 's';
            break;
        case 0xA000: // symbolic link
            $info = 'l';
            break;
        case 0x8000: // regular
            $info = 'r';
            break;
        case 0x6000: // block special
            $info = 'b';
            break;
        case 0x4000: // directory
            $info = 'd';
            break;
        case 0x2000: // character special
            $info = 'c';
            break;
        case 0x1000: // FIFO pipe
            $info = 'p';
            break;
        default: // unknown
            $info = 'u';
    }

// Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
        (($perms & 0x0800) ? 's' : 'x') :
        (($perms & 0x0800) ? 'S' : '-'));

// Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
        (($perms & 0x0400) ? 's' : 'x') :
        (($perms & 0x0400) ? 'S' : '-'));

// World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
        (($perms & 0x0200) ? 't' : 'x') :
        (($perms & 0x0200) ? 'T' : '-'));

    echo $info;
}
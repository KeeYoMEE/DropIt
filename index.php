<?php

try {

    require './lib/autoLoader.php';
    include './inc/php/functions.php';
    $autoloadManager = new autoloadManager(null, autoloadManager::SCAN_ONCE);
    $autoloadManager->addFolder('./cfg/');
    $autoloadManager->addFolder('./inc/');
    $autoloadManager->addFolder('./lib');
    $autoloadManager->register();


    $env = new \Dt\Inc\Fe\Environment();
    $pf = new \Dt\Inc\Fe\PageFactory($env);

} catch (Exception $e) {
    echo '<br><pre>';
    echo $e->getMessage();
    echo '<br>';
    var_dump($e->getTrace());
    echo '</pre>';
}


<?php

function Flovidy_init($file) {

    require_once('Flovidy_Plugin.php');
    $plugin = new Flovidy_Plugin();

    if (!$plugin->isInstalled()) {
        $plugin->install();
    }
    else {
        $plugin->upgrade();
    }
    $plugin->addActionsAndFilters();

    if (!$file) {
        $file = __FILE__;
    }
    register_activation_hook($file, array(&$plugin, 'activate'));
    register_deactivation_hook($file, array(&$plugin, 'deactivate'));
}

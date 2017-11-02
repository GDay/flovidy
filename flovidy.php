<?php
/*
   Plugin Name: Flovidy
   Plugin URI: https://flovidy.com
   Version: 0.3
   Author: <a href="https://flovidy.com">flovidy.com</a>
   Description: Localize amazon affiliate links to optimize affiliate commision income
   Text Domain: flovidy
   License: BSD 3-Clause
  */

$Flovidy_minimalRequiredPhpVersion = '5.0';
function Flovidy_noticePhpVersionWrong() {
    global $Flovidy_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Flovidy" requires a newer version of PHP to be running.',  'flovidy').
            '<br/>' . __('Minimal version of PHP required: ', 'flovidy') . '<strong>' . $Flovidy_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'flovidy') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function Flovidy_PhpVersionCheck() {
    global $Flovidy_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $Flovidy_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'Flovidy_noticePhpVersionWrong');
        return false;
    }
    return true;
}
function Flovidy_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('flovidy', false, $pluginDir . '/languages/');
}
add_action('plugins_loadedi','Flovidy_i18n_init');
if (Flovidy_PhpVersionCheck()) {
    include_once('flovidy_init.php');
    Flovidy_init(__FILE__);
}
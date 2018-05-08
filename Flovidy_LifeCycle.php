<?php
include_once('Flovidy_InstallIndicator.php');

class Flovidy_LifeCycle extends Flovidy_InstallIndicator {

    public function install() {

        // Initialize Plugin Options
        $this->initOptions();

        // Initialize DB Tables used by the plugin
        $this->installDatabaseTables();

        // Record the installed version
        $this->saveInstalledVersion();

        // To avoid running install() more then once
        $this->markAsInstalled();
    }

    public function uninstall() {
        $this->unInstallDatabaseTables();
        $this->deleteSavedOptions();
        $this->markAsUnInstalled();
    }

    public function upgrade() {
    }

    public function activate() {
    }

    public function deactivate() {
    }

    protected function initOptions() {
    }

    public function addActionsAndFilters() {
    }

    protected function installDatabaseTables() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_name = $wpdb->prefix . 'ai_link';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          us_link TEXT(1400),
          in_link TEXT(1400),
          jp_link TEXT(1400),
          fr_link TEXT(1400),
          de_link TEXT(1400),
          it_link TEXT(1400),
          es_link TEXT(1400),
          uk_link TEXT(1400),
          ca_link TEXT(1400),
          br_link TEXT(1400),
          cn_link TEXT(1400),
          au_link TEXT(1400),
          PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql );
    }

    protected function unInstallDatabaseTables() {
        global $wpdb;
        $table_name_links = $wpdb->prefix . 'ai_link';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name_links;" );
    }

    public function addSettingsSubMenuPage() {
        $this->requireExtraPluginFiles();
        $displayName = "Flovidy";
        add_options_page($displayName, $displayName, 'manage_options', get_class($this) . 'Settings', array(&$this, 'settingsPage'));
    }

    protected function requireExtraPluginFiles() {
        require_once(ABSPATH . 'wp-includes/pluggable.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    protected function prefixTableName($name) {
        global $wpdb;
        return $wpdb->prefix . strtolower($this->prefix($name));
    }

}

<?php
class Flovidy_OptionsManager {

    public function getOptionNamePrefix() {
        return get_class($this) . '_';
    }

    public function getOptionMetaData() {
        return array();
    }

    public function getOptionNames() {
        return array_keys($this->getOptionMetaData());
    }

    protected function initOptions() {
    }

    protected function deleteSavedOptions() {
        $optionMetaData = $this->getOptionMetaData();
        if (is_array($optionMetaData)) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey);
                delete_option($prefixedOptionName);
            }
        }
    }

    public function getPluginDisplayName() {
        return get_class($this);
    }

    public function prefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return $name; 
        }
        return $optionNamePrefix . $name;
    }

    public function &unPrefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return substr($name, strlen($optionNamePrefix));
        }
        return $name;
    }

    public function getOption($optionName, $default = null) {
        $prefixedOptionName = $this->prefix($optionName);
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }
        return $retVal;
    }

    public function addOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName);
        return add_option($prefixedOptionName, $value);
    }
    public function updateOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName);
        return update_option($prefixedOptionName, $value);
    }

    public function createSettingsMenu() {
        $pluginName = $this->getPluginDisplayName();
        add_menu_page($pluginName . ' Plugin Settings',
                      $pluginName,
                      'administrator',
                      get_class($this),
                      array(&$this, 'settingsPage')
        /*,plugins_url('/images/icon.png', __FILE__)*/); // if you call 'plugins_url; be sure to "require_once" it

        add_action('admin_init', array(&$this, 'registerSettings'));
    }

    public function registerSettings() {
        $settingsGroup = get_class($this) . '-settings-group';
        $optionMetaData = $this->getOptionMetaData();
        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
            register_setting($settingsGroup, $aOptionMeta);
        }
    }

    public function settingsPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'flovidy'));
        }

        $optionMetaData = $this->getOptionMetaData();

        // Save Posted Options
        if ($optionMetaData != null) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                if (isset($_POST[$aOptionKey])) {
                    $this->updateOption($aOptionKey, $_POST[$aOptionKey]);
                }
            }
        }

        // HTML for the page
        $settingsGroup = get_class($this) . '-settings-group';
        ?>
        <div class="wrap">
            <?php
            if (version_compare('5.2', phpversion()) > 0) {
                echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
                _e('(WARNING: This plugin may not work properly with versions earlier than PHP 5.2)', 'flovidy');
                echo '</span>';
            }
            echo '&nbsp;&nbsp;&nbsp;<span style="background-color: #ffcc00;">';
            if (version_compare('5.0', $this->getMySqlVersion()) > 0) {
                _e('(WARNING: This plugin may not work properly with versions earlier than MySQL 5.0)', 'flovidy');
            }
            echo '</span>';
            $links_array = array(
            'us_ref' => "<a target='_blank' href='https://affiliate-program.amazon.com/'>Apply for a code here<a>",
            'in_ref' => "<a target='_blank' href='https://affiliate-program.amazon.in/'>Apply for a code here<a>",
            'jp_ref' => "<a target='_blank' href='https://affiliate.amazon.co.jp/'>Apply for a code here<a>",
            'fr_ref' => "<a target='_blank' href='https://partenaires.amazon.fr/'>Apply for a code here<a>",
            'de_ref' => "<a target='_blank' href='https://partnernet.amazon.de/'>Apply for a code here<a>",
            'it_ref' => "<a target='_blank' href='https://programma-affiliazione.amazon.it/'>Apply for a code here<a>",
            'es_ref' => "<a target='_blank' href='https://afiliados.amazon.es/'>Apply for a code here<a>",
            'uk_ref' => "<a target='_blank' href='https://affiliate-program.amazon.co.uk/'>Apply for a code here<a>",
            'ca_ref' => "<a target='_blank' href='https://associates.amazon.ca/'>Apply for a code here<a>",
            'br_ref' => "<a target='_blank' href='https://associados.amazon.com.br/'>Apply for a code here<a>",
            'cn_ref' => "<a target='_blank' href='https://associates.amazon.cn/'>Apply for a code here<a>",
            'au_ref' => "<a target='_blank' href='https://associates.amazon.con.au/'>Apply for a code here<a>",
            'license' => "",
            'bitly_access_token' => "Only required when using amzn.to links. Signup at <a target='_blank' href='https://bitly.com/a/sign_up'>bitly.com</a> (do NOT use social login) and then get your key by clicking <a target='_blank' href='https://app.bitly.com/bitlinks/?actions=accountMain&actions=profile&actions=accessToken'>this link</a>. Enter your password and hit 'GENERATE TOKEN'. Paste the token here.",
            )
            ?>


            <h2>Flovidy Settings</h2>

            <form method="post" action="">
            <?php settings_fields($settingsGroup); ?>
                <style type="text/css">
                    table.plugin-options-table {width: 100%; padding: 0;}
                    table.plugin-options-table tr:nth-child(even) {background: #f9f9f9}
                    table.plugin-options-table tr:nth-child(odd) {background: #FFF}
                    table.plugin-options-table tr:first-child {width: 35%;}
                    table.plugin-options-table td {vertical-align: middle;}
                    table.plugin-options-table td+td {width: auto}
                    table.plugin-options-table td > p {margin-top: 0; margin-bottom: 0;}
                </style>
                <table class="plugin-options-table"><tbody>
                <?php
                if ($optionMetaData != null) {
                    foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                        $displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
                        ?>
                            <tr valign="top">
                                <th scope="row" style="max-width: 100px"><p><label for="<?php echo $aOptionKey ?>"><?php echo $displayText ?> <?php if ($links_array[$aOptionKey]){echo '('. $links_array[$aOptionKey] . ')';} ?></label></p></th>
                                <td>
                                <?php $this->createFormControl($aOptionKey, $aOptionMeta, $this->getOption($aOptionKey)); ?>
                                </td>
                            </tr>
                        <?php
                    }
                }
                ?>
                </tbody></table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'flovidy') ?>"/>
                </p>
            </form>
        </div>
        <?php

    }
    protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {
        
            ?>

            <p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
                      value="<?php echo esc_attr($savedOptionValue) ?>" size="50"/></p>
                     
            <?php
            
    }
    protected function getOptionValueI18nString($optionValue) {
        switch ($optionValue) {
            case 'true':
                return __('true', 'flovidy');
            case 'false':
                return __('false', 'flovidy');
        }
        return $optionValue;
    }
    protected function getMySqlVersion() {
        global $wpdb;
        $rows = $wpdb->get_results('select version() as mysqlversion');
        if (!empty($rows)) {
            return $rows[0]->mysqlversion;
        }
        return false;
    }
    

}


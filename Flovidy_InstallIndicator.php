<?php
include_once('Flovidy_OptionsManager.php');

class Flovidy_InstallIndicator extends Flovidy_OptionsManager {

    const optionInstalled = '_installed';
    const optionVersion = '_version';

    public function isInstalled() {
        return $this->getOption(self::optionInstalled) == true;
    }

    protected function markAsInstalled() {
        return $this->updateOption(self::optionInstalled, true);
    }

    protected function markAsUnInstalled() {
        return $this->deleteOption(self::optionInstalled);
    }

    protected function getVersionSaved() {
        return $this->getOption(self::optionVersion);
    }

    protected function setVersionSaved($version) {
        return $this->updateOption(self::optionVersion, $version);
    }
    protected function getMainPluginFileName() {
        return basename(dirname(__FILE__)) . 'php';
    }

    public function getPluginHeaderValue($key) {
        // Read the string from the comment header of the main plugin file
        $data = file_get_contents($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName());
        $match = array();
        preg_match('/' . $key . ':\s*(\S+)/', $data, $match);
        if (count($match) >= 1) {
            return $match[1];
        }
        return null;
    }

    protected function getPluginDir() {
        return dirname(__FILE__);
    }

    public function getVersion() {
        return $this->getPluginHeaderValue('Version');
    }
    public function isInstalledCodeAnUpgrade() {
        return $this->isSavedVersionLessThan($this->getVersion());
    }
    public function isSavedVersionLessThan($aVersion) {
        return $this->isVersionLessThan($this->getVersionSaved(), $aVersion);
    }

    public function isSavedVersionLessThanEqual($aVersion) {
        return $this->isVersionLessThanEqual($this->getVersionSaved(), $aVersion);
    }

    public function isVersionLessThanEqual($version1, $version2) {
        return (version_compare($version1, $version2) <= 0);
    }

    public function isVersionLessThan($version1, $version2) {
        return (version_compare($version1, $version2) < 0);
    }

    protected function saveInstalledVersion() {
        $this->setVersionSaved($this->getVersion());
    }
}

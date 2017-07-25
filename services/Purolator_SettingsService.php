<?php
namespace Craft;

/**
 * Get the plugin settings
 */
class Purolator_SettingsService extends BaseApplicationComponent
{
    private $settings;

    public function getSettings()
    {
        if ($this->settings) {
            return $this->settings;
        }
        return $this->settings = craft()->plugins->getPlugin('purolator')->getSettings();
    }

    public function getKey()
    {
        return $this->getSettings()->testMode ? $this->getSettings()->developmentKey : $this->getSettings()->productionKey;
    }

    public function getKeyPassword()
    {
        return $this->getSettings()->testMode ? $this->getSettings()->developmentKeyPassword : $this->getSettings()->productionKeyPassword;
    }

    public function getCourierAccountNumber()
    {
        return $this->getSettings()->testMode ? $this->getSettings()->developmentCourierAccountNumber : $this->getSettings()->courierAccountNumber;
    }

    public function getSenderPostalCode()
    {
        return $this->getSettings()->senderPostalCode;
    }
}
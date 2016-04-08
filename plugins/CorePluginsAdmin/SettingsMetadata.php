<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\Settings;
use Exception;

class SettingsMetadata
{

    /**
     * @param Settings[]  $settingsInstances
     * @param array $settingValues   array('pluginName' => array('settingName' => 'settingValue'))
     * @throws Exception;
     */
    public function setPluginSettings($settingsInstances, $settingValues)
    {
        try {
            foreach ($settingsInstances as $pluginName => $pluginSetting) {
                foreach ($pluginSetting->getSettingsWritableByCurrentUser() as $setting) {

                    $value = $this->findSettingValueFromRequest($settingValues, $pluginName, $setting->getName());

                    if (isset($value)) {
                        $setting->setValue($value);
                    }
                }
            }

        } catch (Exception $e) {
            $message = $e->getMessage();

            if (!empty($setting)) {
                $title = Piwik::translate(strip_tags($setting->configureField()->title));
                throw new Exception($title . ': ' . $message);
            }
        }
    }

    private function findSettingValueFromRequest($settingValues, $pluginName, $settingName)
    {
        if (!array_key_exists($pluginName, $settingValues)) {
            return;
        }

        foreach ($settingValues[$pluginName] as $setting) {
            if ($setting['name'] === $settingName) {
                $value = null;
                if (array_key_exists('value', $setting)) {
                    $value = $setting['value'];
                }

                if (is_string($value)) {
                    return Common::unsanitizeInputValue($value);
                }

                return $value;
            }
        }
    }


    /**
     * @param Settings[] $allSettings A list of Settings instead by pluginname
     * @return array
     */
    public function formatSettings($allSettings)
    {
        $metadata = array();
        foreach ($allSettings as $pluginName => $settings) {
            $writableSettings = $settings->getSettingsWritableByCurrentUser();

            if (empty($writableSettings)) {
                continue;
            }

            $plugin = array(
                'pluginName' => $pluginName,
                'settings' => array()
            );

            foreach ($writableSettings as $writableSetting) {
                $plugin['settings'][] = $this->formatMetadata($writableSetting);
            }

            $metadata[] = $plugin;
        }

        return $metadata;
    }

    private function formatMetadata(Setting $setting)
    {
        $config = $setting->configureField();

        $availableValues = $config->availableValues;

        if (is_array($availableValues)) {
            $availableValues = (object) $availableValues;
        }

        return array(
            'name' => $setting->getName(),
            'title' => $config->title,
            'value' => $setting->getValue(),
            'defaultValue' => $setting->getDefaultValue(),
            'type' => $setting->getType(),
            'uiControl' => $config->uiControl,
            'uiControlAttributes' => $config->uiControlAttributes,
            'availableValues' => $availableValues,
            'description' => $config->description,
            'inlineHelp' => $config->inlineHelp,
            'introduction' => $config->introduction,
            'condition' => $config->condition,
        );
    }

}
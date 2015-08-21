<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\ArchiveProcessor;
use Piwik\Piwik;
use Piwik\Tracker\Cache;
use Piwik\Tracker;

class CustomVariables extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Live.getAllVisitorDetails' => 'extendVisitorDetails'
        );
    }

    public function install()
    {
        Model::install();
    }

    public function uninstall()
    {
        Model::uninstall();
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $customVariables = array();

        $maxCustomVariables = self::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (!empty($details['custom_var_k' . $i])) {
                $customVariables[$i] = array(
                    'customVariableName' .  $i => $details['custom_var_k' . $i],
                    'customVariableValue' . $i => $details['custom_var_v' . $i],
                );
            }
        }

        $visitor['customVariables'] = $customVariables;
    }

    /**
     * There are also some hardcoded places in JavaScript
     * @return int
     */
    public static function getMaxLengthCustomVariables()
    {
        return 200;
    }

    public static function getMaxCustomVariables()
    {
        $cache    = Cache::getCacheGeneral();
        $cacheKey = 'CustomVariables.MaxNumCustomVariables';

        if (!array_key_exists($cacheKey, $cache)) {

            $maxCustomVar = 0;

            foreach (Model::getScopes() as $scope) {
                $model = new Model($scope);
                $highestIndex = $model->getHighestCustomVarIndex();

                if ($highestIndex > $maxCustomVar) {
                    $maxCustomVar = $highestIndex;
                }
            }

            $cache[$cacheKey] = $maxCustomVar;
            Cache::setCacheGeneral($cache);
        }

        return $cache[$cacheKey];
    }

}

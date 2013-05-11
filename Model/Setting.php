<?php
App::uses('AppModel', 'Model');

/**
 * Setting Model
 *
 */
class Setting extends AppModel {
    public $displayField = 'key';

    public $actsAs = array('Setting.Settable');

    /**
     * setSetting
     *
     */
    public static function setSetting($key, $value = null){
        $setting = ClassRegistry::init('Setting');
        return SettableBehavior::setSetting($setting, $key, $value);
    }

    /**
     * getSetting
     *
     */
    public static function getSetting($key = null, $force = false){
        $setting = ClassRegistry::init('Setting');
        return SettableBehavior::getSetting($setting, $key, $force);
    }

    /**
     * clearCache
     *
     */
    public static function clearCache(){
        $setting = ClassRegistry::init('Setting');
        return SettableBehavior::clearCache($setting);
    }
}

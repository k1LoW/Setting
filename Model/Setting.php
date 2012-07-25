<?php
App::uses('AppModel', 'Model');

/**
 * Setting Model
 *
 */
class Setting extends AppModel {
    public $displayField = 'key';

    /**
     * setSetting
     *
     */
    public static function setSetting($key, $value = null){
        $setting = ClassRegistry::init('Setting');
        if (!$setting->setSettingToDatasource($key, $value)) {
            return false;
        }
        return Cache::write('Setting.cache', $setting->getSettingFromDatasource());
    }

    /**
     * getSetting
     *
     */
    public static function getSetting($key = null, $force = false){
        if ($force) {
            $setting = ClassRegistry::init('Setting');
            return $setting->getSettingFromDatasource($key);
        }
        $cache = Cache::read('Setting.cache');
        if ($cache === false) {
            $setting = ClassRegistry::init('Setting');
            Cache::write('Setting.cache', $setting->getSettingFromDatasource());
            $cache = Cache::read('Setting.cache');
        }
        if ($cache) {
            $settings = Configure::read('Setting.settings');
            if ($key === null) {
                return $cache;
            } else {
                $keys = array();
                foreach (array_intersect((array)$key, array_keys($settings)) as $k) {
                    if (!array_key_exists($k, $cache)) {
                        $setting = ClassRegistry::init('Setting');
                        return $setting->getSettingFromDatasource($key);
                    }
                    $keys[$k] = $cache[$k];
                }
                if ($key !== null && count($keys) === 1) {
                    return array_shift($keys);
                }
                return $keys;
            }
        }
        return null;
    }

    /**
     * setSettingToDatasource
     *
     * @param $key, $value = null
     */
    public function setSettingToDatasource($key, $value = null){
        $data = $key;
        if (is_string($key)) {
            $data = array($key => $value);
        }
        $settings = Configure::read('Setting.settings');
        $this->validate = $settings;
        $this->set($data);
        if(!$this->validates()) {
            return false;
        }
        $this->begin();
        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($settings))) {
                $d = $this->find('first', array('conditions' => array('Setting.key' => $k)));
                if (empty($d)) {
                    $d = array('Setting' => array('key' => $k,
                                                  'value' => $v));
                } else {
                    $d['Setting']['value'] = $v;
                    unset($d['Setting']['modified']);
                }
                $this->create();
                $this->set($d);
                if (!$this->save($d, false)) {
                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

    /**
     * getSettingFromDatasource
     *
     * @param $key = null
     */
    public function getSettingFromDatasource($key = null){
        $settings = Configure::read('Setting.settings');
        if ($key === null) {
            $s = $this->find('all');
        } else {
            $keys = array_intersect((array)$key, array_keys($settings));
            $s = $this->find('all', array('conditions' => array('Setting.key' => $keys)));
        }
        if (empty($s)) {
            return null;
        }
        $keys = array();
        foreach ($s as $d) {
            $keys[$d['Setting']['key']] = $d['Setting']['value'];
        }
        if ($key !== null && count($keys) === 1) {
            return array_shift($keys);
        }
        return $keys;
    }

    /**
     * deleteSetting
     *
     * @param $key
     */
    public function deleteSetting($key){
        $keys = array_intersect((array)$key, array_keys($settings));
        $s = $this->find('all', array('conditions' => array('Setting.key' => $keys)));
        if (empty($s)) {
            return true;
        }
        foreach ($s as $d) {
            if (!$this->delete($d['Setting']['id'])) {
                return false;
            }
        }
        return true;
    }
}

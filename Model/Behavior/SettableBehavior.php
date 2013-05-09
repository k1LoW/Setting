<?php

/**
 *
 *
 *
 * @params
 */
class SettableBehavior extends ModelBehavior {

    /**
     * setSetting
     *
     */
    public static function setSetting(Model $model, $key, $value = null){
        $setting = $model;
        $prefix = Configure::read('Setting.prefix');
        if (!$setting->setSettingToDatasource($key, $value)) {
            return false;
        }
        Cache::write($prefix . 'Setting.cache', $setting->getSettingFromDatasource());
        return Cache::write($prefix . 'Setting.cache', $setting->getSettingFromDatasource());
    }

    /**
     * getSetting
     *
     */
    public static function getSetting(Model $model, $key = null, $force = false){
        $prefix = Configure::read('Setting.prefix');
        if ($force) {
            $setting = $model;
            return $setting->getSettingFromDatasource($key);
        }
        $cache = Cache::read($prefix . 'Setting.cache');
        if ($cache === false || empty($cache[$key])) {
            Cache::delete($prefix . 'Setting.cache');
            $setting = $model;
            Cache::write($prefix . 'Setting.cache', $setting->getSettingFromDatasource());
            $cache = Cache::read($prefix . 'Setting.cache');
        }
        if ($cache) {
            $settings = Configure::read('Setting.settings');
            if ($key === null) {
                return $cache;
            } else {
                $keys = array();
                foreach (array_intersect((array)$key, array_keys($settings)) as $k) {
                    if (!array_key_exists($k, $cache) || $cache[$k] === null) {
                        $setting = $model;
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
    public function setSettingToDatasource(Model $model, $key, $value = null){
        $data = $key;
        if (is_string($key)) {
            $data = array($key => $value);
        }
        $settings = Configure::read('Setting.settings');
        foreach ($data as $k => $v) {
            if (!in_array($k, array_keys($settings))) {
                return false;
            }
            $model->validate[$k] = $settings[$k];
        }
        $model->set($data);
        if(!$model->validates()) {
            return false;
        }
        $model->begin();
        foreach ($data as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $model->rollback();
                return false;
            }
            if (in_array($k, array_keys($settings))) {
                $d = $model->find('first', array('conditions' => array("{$model->alias}.key" => $k)));
                if (empty($d)) {
                    $d = array($model->alias => array('key' => $k,
                                                      'value' => $v));
                } else {
                    $d[$model->alias]['value'] = $v;
                    unset($d[$model->alias]['modified']);
                }
                $model->create();
                $model->set($d);
                if (!$model->save($d, false)) {
                    $model->rollback();
                    return false;
                }
            }
        }
        $model->commit();
        return true;
    }

    /**
     * getSettingFromDatasource
     *
     * @param $key = null
     */
    public function getSettingFromDatasource(Model $model, $key = null){
        $settings = Configure::read('Setting.settings');
        $prefix = Configure::read('Setting.prefix');
        if ($key === null) {
            $s = $model->find('all', array('conditions' => array("{$model->alias}.key" => array_keys($settings))));
        } else {
            $s = $model->find('all', array('conditions' => array("{$model->alias}.key" => array_intersect((array)$key, array_keys($settings)))));
        }
        $keys = array();
        foreach ($s as $d) {
            $keys[$d[$model->alias]['key']] = $d[$model->alias]['value'];
        }
        if ($key !== null && count($keys) === 1) {
            return array_shift($keys);
        }
        if ($key === null && count($keys) !== count($settings)) {
            foreach ($settings as $k => $v) {
                if (!array_key_exists($k, $keys) && array_key_exists('default', $settings[$k])) {
                    $keys[$k] = (string)$settings[$k]['default'];
                    self::setSetting($model, $k, $settings[$k]['default']);
                }
            }
        }
        return array_merge(array_combine(array_keys($settings), array_fill(0, count($settings), null)), $keys);
    }

    /**
     * deleteSetting
     *
     * @param $key
     */
    public function deleteSetting(Model $model, $key){
        $keys = array_intersect((array)$key, array_keys($settings));
        $s = $model->find('all', array('conditions' => array("{$model->alias}.key" => $keys)));
        if (empty($s)) {
            return true;
        }
        foreach ($s as $d) {
            if (!$model->delete($d[$model->alias]['id'])) {
                return false;
            }
        }
        return true;
    }

}
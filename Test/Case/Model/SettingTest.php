<?php
App::uses('Cache', 'Cache');
App::uses('Setting', 'Setting.Model');

class Setting extends CakeTestModel {
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
}

class SettingTestCase extends CakeTestCase {

    public $fixtures = array('plugin.Setting.setting');

    public function setUp() {
        $this->_cacheDisable = Configure::read('Cache.disable');
        Configure::write('Cache.disable', false);
        $this->_defaultCacheConfig = Cache::config('default');
        Cache::config('default', array('engine' => 'File', 'path' => TMP . 'tests'));

        $this->Setting = new Setting();
        Configure::write('Setting.prefix', 'test');
    }

    public function tearDown() {
        Cache::delete('test' . 'Setting.cache');
        Cache::clear();
        Configure::write('Cache.disable', $this->_cacheDisable);
        Cache::config('default', $this->_defaultCacheConfig['settings']);
    }

    /**
     * testCache
     *
     */
    public function testCache(){
        $result = Cache::write('hoge', 'fuga');
        $this->assertTrue($result);
        $result = Cache::read('hoge');
        $this->assertIdentical($result, 'fuga');
    }

    /**
     * testSetSetting
     *
     */
    public function testSetSetting(){
        Configure::write('Setting.settings', array(
                                                   'tax_rate' => array('rule' => array('numeric')),
                                                   ));
        $result = Setting::setSetting('tax_rate', 0.05);
        $this->assertTrue($result);

        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.05');
    }

    /**
     * testGetSettingFromCache
     *
     */
    public function testGetSettingFromCache(){
        Configure::write('Setting.settings', array(
                                                   'tax_rate' => array('rule' => array('numeric')),
                                                   ));
        $result = Setting::setSetting('tax_rate', 0.05);
        $this->assertTrue($result);

        // jpn: DB側の値を直接変更してしまう(本来はしない処理)
        $setting = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_rate')));
        $setting['Setting']['value'] = 0.10;
        $this->Setting->save($setting);
        $result = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_rate')));
        $this->assertIdentical((float)$result['Setting']['value'], 0.10);

        // jpn: キャッシュを見ているので元の値を出力する
        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.05');

        // jpn: キャッシュをクリア
        Cache::delete('test' . 'Setting.cache');

        // jpn: キャッシュがないのでDBを見に行く
        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical((float)$result, 0.10);
    }

    /**
     * testGetSettingFromDatasource
     *
     */
    public function testGetSettingFromDatasource(){
        Configure::write('Setting.settings', array(
                                                   'tax_rate' => array('rule' => array('numeric')),
                                                   ));
        Setting::setSetting('tax_rate', 0.05);

        // jpn: DB側の値を直接変更してしまう(本来はしない処理)
        $setting = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_rate')));
        $setting['Setting']['value'] = 0.10;
        $this->Setting->save($setting);
        $result = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_rate')));
        $this->assertIdentical((float)$result['Setting']['value'], 0.10);

        // jpn: 第2引数を設定し、強引にDBを見に行く
        $result = Setting::getSetting('tax_rate', true);
        $this->assertIdentical((float)$result, 0.10);
    }

    /**
     * testGetSettingAll
     *
     */
    public function testGetSettingAll(){
        Configure::write('Setting.settings', array(
                                                   'tax_rate' => array('rule' => array('numeric')),
                                                   'tax_flg' => array('rule' => '/^[01]$/'),
                                                   ));
        $result = Setting::getSetting();
        $expect = array('tax_rate' => null,
                        'tax_flg' => null);
        $this->assertIdentical($result, $expect);
        $result = Setting::setSetting('tax_rate', 0.05);
        $this->assertTrue($result);

        $result = Setting::getSetting();
        $expect = array('tax_rate' => '0.05',
                        'tax_flg' => null);
        $this->assertIdentical($result, $expect);

        $result = Setting::setSetting('tax_flg', true);
        $this->assertTrue($result);

        $result = Setting::getSetting();
        $expect = array('tax_rate' => '0.05',
                        'tax_flg' => '1');
        $this->assertIdentical($result, $expect);
    }
}
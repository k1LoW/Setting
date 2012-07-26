<?php
App::uses('Cache', 'Cache');
App::uses('Setting', 'Setting.Model');
class SettingTestCase extends CakeTestCase {

    public $fixtures = array('plugin.Setting.setting');

    function setUp() {
        $this->Setting = new Setting();
        Configure::write('Cache.disable', false);
        Configure::write('Setting.prefix', 'test');
    }

    function tearDown() {
        Cache::delete('test' . 'Setting.cache');
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
        //$this->assertTrue($result);

        $this->assertTrue(is_writable(CACHE));
        $this->assertTrue(file_exists(CACHE . 'cake_test_setting_cache'));

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
        //$this->assertTrue($result);

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
}
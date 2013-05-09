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
        Cache::config('default', array(
                'engine' => 'File',
                'path' => TMP . 'tests' . DS,
                'mask' => 0666,
            ));
        $this->Setting = new Setting();
        Configure::write('Setting.prefix', 'test');
    }

    public function tearDown() {
        Cache::delete('test' . 'Setting.cache');
        Cache::clear();
        @unlink(TMP . 'tests' . DS . 'cake_test_setting_cache');
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
     * jpn: 設定をkey-value形式で保存できる
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
     * testSetSettingMulti
     *
     * jpn: 複数の設定を一括して設定することが可能
     */
    public function testSetSettingMulti(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        $result = Setting::setSetting(array('tax_rate' => 0.05,
                'tax_flg' => 1));
        $this->assertTrue($result);

        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.05');

        $result = Setting::getSetting('tax_flg');
        $this->assertIdentical($result, '1');
    }

    /**
     * testSetSettingValidation
     *
     * jpn: 設定は設定しようとしたもののみをバリデーションチェックする
     */
    public function testSetSettingValidation(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array(
                    'rule' => array('numeric'),
                    'allowEmpty' => false,
                ),
                'tax_flg' => array(
                    'rule' => '/^[01]$/',
                    'allowEmpty' => false,
                ),
            ));
        $result = Setting::setSetting('tax_rate', 0.05);
        $this->assertTrue($result);
    }

    /**
     * testInvalidKeySetSetting
     *
     * jpn: Setting.settingsで設定していないキーの設定はfalse
     */
    public function testInvalidKeySetSetting(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        $result = Setting::setSetting('invalid_key', 0.05);
        $this->assertFalse($result);
    }

    /**
     * testInvalidValueSetSetting
     *
     * jpn: Setting.settingsで設定していないバリデーションにマッチしない場合はfalse
     */
    public function testInvalidValueSetSetting(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        $result = Setting::setSetting('tax_rate', 'invalid_value');
        $this->assertFalse($result);
    }

    /**
     * testGetSettingFromCache
     *
     * jpn: Setting::getSetting()は通常はキャッシュを優先して参照する
     */
    public function testGetSettingFromCache(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        $result = Setting::setSetting(array(
                'tax_rate' => 0.05,
                'tax_flg' => true,
            ));
        $this->assertTrue($result);

        $this->assertTrue(file_exists(TMP . 'tests' . DS . 'cake_test_setting_cache'));

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
     * jpn: Setting::getSetting()の第2引数をtrueにするとDatasourceを優先して参照する
     */
    public function testGetSettingFromDatasource(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        $result = Setting::setSetting('tax_rate', 0.05);
        $this->assertTrue($result);

        // jpn: DB側の値を直接変更してしまう(本来はしない処理)
        $setting = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_rate')));
        $setting['Setting']['value'] = 0.10;
        $this->Setting->save($setting);
        $result = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_rate')));
        $this->assertIdentical((float)$result['Setting']['value'], 0.10);

        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.05');

        // jpn: 第2引数を設定し、強引にDBを見に行く
        $result = Setting::getSetting('tax_rate', true);
        $this->assertIdentical((float)$result, 0.10);
    }

    /**
     * testGetSettingFromDatasource
     *
     * jpn: Setting::getSetting()の第2引数をtrueにして2番目のデータをDatasourceを優先して参照する
     */
    public function testGetSettingFromDatasourceSecond(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        $result = Setting::setSetting(array(
                'tax_rate' => 0.05,
                'tax_flg' => 1,
            ));
        $this->assertTrue($result);
        // jpn: 第2引数を設定し、強引にDBを見に行く
        $result = Setting::getSetting('tax_flg', true);
        $this->assertIdentical($result, '1');
    }

    /**
     * testGetSettingAll
     *
     * jpn: Setting::getSetting()を引数なしで使用した場合、全ての設定を返す
     */
    public function testGetSettingAll(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric')),
                'tax_flg' => array('rule' => '/^[01]$/'),
            ));
        // jpn: keyに対してvalueがない場合はnullを返す
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

    /**
     * testGetSettingDefault
     *
     * jpn: Setting.settingsに`default`パラメータがあって、
     *      キャッシュ、Datasourceにデータがない場合はnullの代わりにそれを返して
     *      Datasourceにそれを設定する
     */
    public function testGetSettingDefault(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric'),
                    'default' => 0.03),
            ));
        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.03');
        $result = Setting::setSetting('tax_rate', 0.05);
        $this->assertTrue($result);
        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.05');

        Cache::delete('test' . 'Setting.cache');
        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.05');
    }

    /**
     * testGetSettingDefaultMulti
     *
     * jpn: 複数の値をセットしたときどの場合でもdefault値を取得できる
     */
    public function testGetSettingDefaultMulti(){
        Configure::write('Setting.settings', array(
                'tax_rate' => array('rule' => array('numeric'),
                    'default' => 0.03),
                'tax_flg' => array('rule' => '/^[01]$/'),
                'tax_label' => array('rule' => array('notEmpty'),
                    'default' => 'TAX'),
            ));
        $result = Setting::getSetting('tax_rate');
        $this->assertIdentical($result, '0.03');

        // jpn: DBを直接操作して、一部のデータとキャッシュを削除
        $setting = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_label')));
        $this->Setting->delete($setting['Setting']['id']);
        Cache::delete('test' . 'Setting.cache');
        Cache::clear();

        // jpn: defaultが存在するときはnullでなくdefaultの値を返す
        $result = Setting::getSetting('tax_label');
        $this->assertIdentical($result, 'TAX');

        // jpn: DBを直接操作して、一部のデータとキャッシュを削除
        $setting = $this->Setting->find('first', array('conditions' => array('Setting.key' => 'tax_label')));
        $this->Setting->delete($setting['Setting']['id']);
        Cache::delete('test' . 'Setting.cache');
        Cache::clear();

        // jpn: Setting::getSetting() の場合でもdefaultが存在するときはnullでなくdefaultの値を返す
        $result = Setting::getSetting();
        $expect = array('tax_rate' => '0.03',
            'tax_flg' => null,
            'tax_label' => 'TAX');
        $this->assertIdentical($result, $expect);
    }
}
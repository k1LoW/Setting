# Setting: Database driven setting plugin for CakePHP.

[![Build Status](https://secure.travis-ci.org/k1LoW/Setting.png?branch=1.3x)](http://travis-ci.org/k1LoW/Setting)

## Install

First, put `fatty’ directory on app/plugins in your CakePHP application.add add the following code in bootstrap.php.

    App::import('Model', 'Setting.Setting');

Second, Create schema.

    cake schema create -plugin setting -name settings

## Usage

### Init

Configure `Setting.settings` like Model::validate.

    Configure::write('Setting.settings', array(
                                               'tax_rate' => array('rule' => array('numeric')),
                                               ));


### Set setting

`Setting::setSetting([key], [value])` or `Setting::setSetting(array([key1] => [value1], [key2] => [value2]))`

### Get setting

`Setting::getSetting([key])` or `Setting::getSetting()`

## If you want not to use Setting (settings table)

See [system_control.test.php](https://github.com/k1LoW/Setting/blob/1.3x/tests/cases/models/system_control.test.php).

## License

the MIT License


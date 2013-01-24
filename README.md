# Setting: Database driven setting plugin for CakePHP.

[![Build Status](https://secure.travis-ci.org/k1LoW/Setting.png?branch=master)](http://travis-ci.org/k1LoW/Setting)

## Install

First, Install 'Setting' by [recipe.php](https://github.com/k1LoW/recipe) , and set `CakePlugin::load('Setting', array('bootstrap' => true));`

Second, Create schema.

    ./lib/Cake/Console/cake schema create settings --plugin Setting

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

See [SystemControlTest.php](https://github.com/k1LoW/Setting/blob/master/Test/Case/Model/SystemControlTest.php).

## License

the MIT License


# Setting: Database driven setting plugin for CakePHP.

## Install

First, Install 'Setting' by [recipe.php](https://github.com/k1LoW/recipe) , and set `CakePlugin::load('Setting', array('bootstrap' => true));`

Second, Create schema.

    ./Console/cake schema create settings --plugin Setting

## Usage

### Set setting

`Setting::setSetting([key], [value])` or `Setting::setSetting(array([key1] => [value1], [key2] => [value2]))`

### Get setting

`Setting::getSetting([key])` or `Setting::getSetting()`

## License

the MIT License


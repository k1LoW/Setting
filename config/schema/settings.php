<?php
class settingsSchema extends CakeSchema {

    public function before($event = array()) {
        return true;
    }

    public function after($event = array()) {
    }

    public $settings = array(
                            'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
                            'key' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
                            'value' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
                            'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
                            'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
                            'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
                            'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
                            );
}

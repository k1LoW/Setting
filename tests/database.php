<?php
class DATABASE_CONFIG {
    public $default = array();
    public $test = array();
    public function __construct() {
        $db = (!empty($_SERVER['DB'])) ? $_SERVER['DB'] : 'mysql';
        switch ($db) {
        case 'pgsql':
            $this->default = $this->test = array(
                                                 'driver' => 'postgres',
                                                 'persistent' => false,
                                                 'host' => '127.0.0.1',
                                                 'login' => 'postgres',
                                                 'password' => '',
                                                 'database' => 'cakephp_test',
                                                 'prefix' => '',
                                                 'encoding' => 'utf8'
                                                 );
            break;
        case 'mysql':
        default:
            $this->default = $this->test = array(
                                                 'driver' => 'mysql',
                                                 'persistent' => false,
                                                 'host' => '0.0.0.0',
                                                 'login' => 'root',
                                                 'password' => '',
                                                 'database' => 'cakephp_test',
                                                 'prefix' => ''
                                                 );

        }
    }
}
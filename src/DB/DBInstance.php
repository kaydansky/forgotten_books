<?php

namespace ForgottenBooks\DB;

use Delight\Db\PdoDatabase;
use Delight\Db\PdoDsn;

/**
 * Description of DBInstance
 *
 * @author AlexK
 */
class DBInstance
{
    static private $instance;

    static function dsn()
    {
        global $config;
        return static::$instance ?? (static::$instance = PdoDatabase::fromDsn(
            new PdoDsn(
                $config['db']['dsn'],
                $config['db']['username'],
                $config['db']['password'],
                $config['db']['options'] ?? []
            )
        ));
    }

    private function __construct(){}
    
    private function __clone(){}
    
    public function __wakeup(){}
}

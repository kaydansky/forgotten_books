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
        return static::$instance ?? (static::$instance = PdoDatabase::fromDsn(
            new PdoDsn(
                'mysql:dbname=' . DATABASE_CREDENTIALS['database'] . ';host=' . DATABASE_CREDENTIALS['hostname'],
                DATABASE_CREDENTIALS['username'],
                DATABASE_CREDENTIALS['password']
            )
        ));
    }

    private function __construct(){}
    
    private function __clone(){}
    
    public function __wakeup(){}
}

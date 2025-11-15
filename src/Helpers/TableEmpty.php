<?php

namespace ForgottenBooks\Helpers;

use ForgottenBooks\DB\DBInstance;

class TableEmpty
{
    static function tableContent($tableName)
    {
        return DBInstance::dsn()->select('SELECT id FROM ' . $tableName . ' LIMIT 0, 1');
    }
}

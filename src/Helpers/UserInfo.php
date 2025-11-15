<?php

namespace ForgottenBooks\Helpers;

use ForgottenBooks\DB\DBInstance;

class UserInfo
{
    static function setSession($id)
    {
        $roles = \Delight\Auth\Role::getMap();
        $token = Encrypt::randomString(40);
        $user = DBInstance::dsn()->select('SELECT * FROM workers t1 JOIN users t2 ON t1.user_id = t2.id WHERE t1.user_id = ? LIMIT 0, 1', [$id]);
        DBInstance::dsn()->update('users', ['token' => $token], ['id' => $id]);

        $_SESSION['user_info'] = [
            'first_name' => $user[0]['first_name'],
            'last_name' => $user[0]['last_name'],
            'role' => ucwords(strtolower(str_replace('_', ' ', $roles[$user[0]['roles_mask']]))),
            'token' => $token
        ];
    }

    static function checkSession($id)
    {
        if (! isset($_SESSION['user_info']['token'])) {
            return false;
        }

        return DBInstance::dsn()->selectRow('SELECT id from users WHERE token = ? AND id = ?', [$_SESSION['user_info']['token'], $id]);
    }
}

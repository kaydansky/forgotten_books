<?php
/**
 * @author: AlexK
 * Date: 21-Jan-19
 * Time: 10:38 PM
 */

namespace ForgottenBooks\Domain\Users;

use ForgottenBooks\Helpers\Encrypt;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\ResetDisabledException;
use Delight\Auth\Role;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UnknownIdException;
use Delight\Auth\UserAlreadyExistsException;
use ForgottenBooks\DB\DBInstance;
use ForgottenBooks\Helpers\TableEmpty;
use ForgottenBooks\Emailer\Emailer;

class UsersModel
{
    private $db;
    private $auth;
    private $emailer;
    private $selector;
    private $token;
    public $notice;

    public function __construct(Emailer $emailer)
    {
        $this->emailer = $emailer;
        $this->db = DBInstance::dsn();
    }

    public function inject($auth)
    {
        $this->auth = $auth;
    }

    public function checkTableUsers()
    {
        return TableEmpty::tableContent('users');
    }

    public function createNewUser($first_name, $last_name, $email, $role, $base_pay_rate)
    {
        $userId = $this->registerUser($email, Encrypt::randomString(40), $role);

        if (! $userId) {
            return $this->notice;
        }

        $this->db->insert(
            'workers', [
                'user_id' => $userId,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'base_pay_rate' => $base_pay_rate
            ]
        );

        $this->requestPasswordReset($email, 'new_user_subject', 'new_user_body', 'new_user_alt_body');

        return null;
    }

    public function createSuperadmin($email, $password, $first_name, $last_name)
    {
        if ($this->checkTableUsers() !== null) {
            die('Users table is busy');
        }

        try {
            $userId = $this->auth->admin()->createUser($email, $password, null);
        } catch (InvalidEmailException $e) {
            die('Invalid email address');
        } catch (InvalidPasswordException $e) {
            die('Invalid password');
        } catch (UserAlreadyExistsException $e) {
            die('User already exists');
        }

        try {
            $this->auth->admin()->addRoleForUserById($userId, Role::COORDINATOR);
        } catch (UnknownIdException $e) {
            die('Unknown user ID');
        }

        $this->db->insert(
            'workers', [
                'user_id' => $userId,
                'first_name' => $first_name,
                'last_name' => $last_name
            ]
        );

        return $userId;
    }

    private function registerUser($email, $password, $role)
    {
        $error = '';
        $userId = 0;

        try {
            $userId = $this->auth->register($email, $password, null);
        } catch (InvalidEmailException $e) {
            $error = 'Invalid email address. ';
        } catch (InvalidPasswordException $e) {
            $error = 'Invalid password. ';
        } catch (UserAlreadyExistsException $e) {
            $error = 'User (email) already exists. ';
        } catch (TooManyRequestsException $e) {
            $error = 'Too many requests. ';
        }

        try {
            $this->auth->admin()->addRoleForUserById($userId, $role);
        } catch (UnknownIdException $e) {
            $error .= 'Unknown user ID.';
        }

        if ($error) {
            $this->notice = 'ERROR: ' . $error;
            return null;
        }

        return $userId;
    }

    public function requestPasswordReset($email, $subject, $body, $altBody)
    {
        try {
            $this->auth->forgotPassword($email, function ($selector, $token) {
                $this->selector = $selector;
                $this->token = $token;
            });

            $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
            $host = ! empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            $url = $protocol . $host . '/confirmation/?selector=' . urlencode($this->selector) . '&token=' . urlencode($this->token);

            $this->emailer->to = $email;
            $this->emailer->subject = EMAIL_CONTENT[$subject];
            $this->emailer->body = str_replace('%URL%', $url, EMAIL_CONTENT[$body]);
            $this->emailer->altBody = str_replace('%URL%', $url, EMAIL_CONTENT[$altBody]);
            return $this->emailer->send();
        } catch (InvalidEmailException $e) {
            $error = 'Invalid email address';
        } catch (EmailNotVerifiedException $e) {
            $error = 'Email not verified';
        } catch (ResetDisabledException $e) {
            $error = 'Password reset is disabled';
        } catch (TooManyRequestsException $e) {
            $error = 'Too many requests';
        }

        return $error ?: null;
    }

    public function verifyPasswordReset($selector, $token)
    {
        $error = '';

        try {
            $this->auth->canResetPasswordOrThrow($selector, $token);
        } catch (InvalidSelectorTokenPairException $e) {
            $error = 'Invalid token';
        } catch (TokenExpiredException $e) {
            $error = 'Token expired';
        } catch (ResetDisabledException $e) {
            $error = 'Password reset is disabled';
        } catch (TooManyRequestsException $e) {
            $error = 'Too many requests';
        }

        return $error ?: null;
    }

    public function updatePassword($selector, $token, $password)
    {
        $error = '';

        try {
            $this->auth->resetPassword($selector, $token, $password);
        } catch (InvalidSelectorTokenPairException $e) {
            $error = 'Invalid token';
        } catch (TokenExpiredException $e) {
            $error = 'Token expired';
        } catch (ResetDisabledException $e) {
            $error = 'Password reset is disabled';
        } catch (InvalidPasswordException $e) {
            $error = 'Invalid password';
        } catch (TooManyRequestsException $e) {
            $error = 'Too many requests';
        }

        return $error ?: null;
    }

    public function changeCurrentPassword($password_current, $password, $subject, $body, $altBody)
    {
        try {
            $this->auth->changePassword($password_current, $password);
            $this->emailer->to = $this->auth->getEmail();
            $this->emailer->subject = EMAIL_CONTENT[$subject];
            $this->emailer->body = EMAIL_CONTENT[$body];
            $this->emailer->altBody = EMAIL_CONTENT[$altBody];
            return $this->emailer->send();
        } catch (\Delight\Auth\NotLoggedInException $e) {
            $error = 'Not logged in';
        } catch (InvalidPasswordException $e) {
            $error = 'Invalid current password';
        } catch (TooManyRequestsException $e) {
            $error = 'Too many requests';
        }

        return $error ?: null;
    }

    public function getUserList()
    {
        return $this->db->select(
<<<'SQL'
SELECT t1.user_id,
       t1.first_name,
       t1.last_name,
       t1.base_pay_rate,
       t2.*,
       t2.id AS UserId,
       FROM_UNIXTIME(t2.registered) AS Registered,
       FROM_UNIXTIME(t2.last_login) AS LastLogin,
       t3.pibn,
       t4.id AS coworkers
FROM workers t1 
  RIGHT JOIN users t2 ON t1.user_id = t2.id 
  LEFT JOIN queue t3 ON (
    t3.requested_by_user = t2.id 
      AND t3.completed IS NULL 
      AND t3.assigned IS NOT NULL
      AND queue_number < 10 
      AND t3.id = (SELECT MAX(id) FROM queue WHERE pibn = t3.pibn AND queue_number = t3.queue_number)
    ) 
  LEFT JOIN users t4 ON t4.id != t2.id AND t4.roles_mask = t2.roles_mask
WHERE t2.id != ?
GROUP BY t1.user_id, t1.first_name, t1.last_name, t1.base_pay_rate, t2.id, t2.email, t2.password, t2.username, t2.status, t2.verified, t2.resettable, t2.roles_mask, t2.registered, t2.last_login, t2.force_logout, t3.pibn, t4.id
SQL
            , [$this->auth->getUserId()]);
    }

    public function deleteUser($id)
    {
        try {
            $this->auth->admin()->deleteUserById($id);
            $this->db->delete('workers', ['user_id' => $id]);
        }
        catch (UnknownIdException $e) {
            die('Unknown ID');
        }
    }

    public function getUserById($userId)
    {
        return $this->db->selectRow('SELECT * FROM workers WHERE user_id = ?', [$userId]);
    }

    public function updatePayRare($user_id, $base_pay_rate)
    {
        return $this->db->update('workers', ['base_pay_rate' => $base_pay_rate], ['user_id' => $user_id]);
    }

    public function updateRole($user_id, $role)
    {
        return $this->db->update('users', ['roles_mask' => $role], ['id' => $user_id]);
    }

    public function getAvailableWorkers()
    {
        return $this->db->select(
<<<'SQL'
SELECT t1.id, t1.roles_mask, t2.first_name, t2.last_name 
FROM users t1 
  JOIN workers t2 ON t2.user_id = t1.id 
WHERE t1.id NOT IN (SELECT requested_by_user FROM queue WHERE completed IS NULL AND assigned IS NOT NULL)
SQL
        );
    }

    public function getBusyWorkers()
    {
        return $this->db->select(
<<<'SQL'
SELECT t1.id, t1.roles_mask, t2.first_name, t2.last_name 
FROM users t1 
  JOIN workers t2 ON t2.user_id = t1.id 
WHERE t1.id IN (SELECT requested_by_user FROM queue WHERE completed IS NULL AND assigned IS NOT NULL)
SQL
        );
    }

    public function getAvailableWorkersRole($rolesMask)
    {
        return $this->db->select(
<<<'SQL'
SELECT t1.id, t1.roles_mask, t2.first_name, t2.last_name 
FROM users t1 
  JOIN workers t2 ON t2.user_id = t1.id 
WHERE t1.id NOT IN (SELECT requested_by_user FROM queue WHERE completed IS NULL AND assigned IS NOT NULL) 
  AND t1.roles_mask = ?
SQL
        , [$rolesMask]);
    }

    public function getBusyWorkersRole($rolesMask)
    {
        return $this->db->select(
<<<'SQL'
SELECT t1.id, t1.roles_mask, t2.first_name, t2.last_name 
FROM users t1 
  JOIN workers t2 ON t2.user_id = t1.id 
WHERE t1.id IN (SELECT requested_by_user FROM queue WHERE completed IS NULL AND assigned IS NOT NULL) 
  AND t1.roles_mask = ?
SQL
        , [$rolesMask]);
    }

    public function getShortestQueueWorker($rolesMask, $queueNumber)
    {
        return $this->db->select(
<<<'SQL'
SELECT t1.id, t1.roles_mask, t2.first_name, t2.last_name 
FROM users t1 
  JOIN workers t2 ON t2.user_id = t1.id  
WHERE t1.roles_mask = ?
ORDER BY (SELECT COUNT(id) FROM queue WHERE completed IS NULL AND assigned IS NOT NULL AND requested_by_user = t1.id AND queue_number = ?) ASC
LIMIT 1
SQL
            , [$rolesMask, $queueNumber]);
    }

    public function reassignUser($id)
    {
        $role = $this->db->select('SELECT t1.roles_mask, t2.first_name, t2.last_name FROM users t1 JOIN workers t2 ON t2.user_id = t1.id WHERE t1.id = ? LIMIT 1', [$id]);
        $queue = ROLE_QUEUE[$role[0]['roles_mask']];

        $incomplete = $this->db->select(
<<<'SQL'
SELECT MAX(id) AS id, pibn 
FROM queue t1 
WHERE t1.requested_by_user = ? 
  AND t1.completed IS NULL 
  AND t1.assigned IS NOT NULL
  AND t1.queue_number = ? 
  AND t1.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND queue_number = t1.queue_number)
GROUP BY pibn
SQL
            , [$id, $queue]);

        if (! $incomplete) {
            die();
        }

        $coworkers = $this->db->select('SELECT t1.id, t2.first_name, t2.last_name FROM users t1 JOIN workers t2 ON t2.user_id = t1.id WHERE t1.roles_mask = ? AND t1.id != ?', [$role[0]['roles_mask'], $id]);

        if (! $coworkers) {
            die();
        }

        $coworkerIds = array_column($coworkers, 'id');
        $coworkerNames = array_combine($coworkerIds, array_column($coworkers, null));
        $books = [];

        foreach ($coworkerIds as $coworkerId) {
            $coworkerBooks = $this->db->select(
                <<<'SQL'
SELECT COUNT(pibn) AS books  
FROM queue t1 
WHERE t1.requested_by_user = ?
  AND t1.completed IS NULL 
  AND t1.assigned IS NOT NULL
  AND t1.queue_number = ? 
  AND t1.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND queue_number = t1.queue_number)
GROUP BY t1.requested_by_user
SQL
                , [$coworkerId, $queue]);

            $books[$coworkerId] = $coworkerBooks ? $coworkerBooks[0]['books'] : 0;
        }

        $total = array_sum($books);

        if (! $total) {
            array_walk($books, function(&$v) {
                $v = $v ?: 1;
            });

            $total = array_sum($books);
        }

        $incompleteBooks = array_column($incomplete, 'id');
        $incompletePibns = array_column($incomplete, 'pibn');
        $incompleteIdsPibns = array_combine($incompleteBooks, $incompletePibns);

        $totalIncomplete = count($incompleteBooks);
        arsort($books);
        $fraction = [];
        $length = [];
        $target = [];
        $path = explode('|', QUEUE_FOLDER[$queue]);
        $destinations = [];

        foreach ($books as $key => $value) {
            $fraction[$key] = round(($value / $total) * 100);
            $length[$key] = intval(round((intval($fraction[$key]) / 100) * $totalIncomplete));
        }

        if (! array_sum($length)) {
            array_walk($length, function(&$v) {
                $v = $v ?: 1;
            });
        }

        $k = array_keys($length);
        $v = array_values($length);
        $rv = array_reverse($v);
        $b = array_combine($k, $rv);

        foreach ($b as $key => $value) {
            $target[$key] = array_splice($incompleteBooks, 0, $value);
        }

        foreach ($target as $key => $value) {
            if (count($value)) {
                foreach ($value as $recordId) {
                    $this->db->update('queue', ['requested_by_user' => $key, 'assigned' => time()], ['id' => $recordId]);
                    $destinations[] = [
                        'source' => $incompleteIdsPibns[$recordId] . $path[0] . $role[0]['first_name'] . ' ' . $role[0]['last_name'] . $path[1],
                        'destination' => $incompleteIdsPibns[$recordId] . $path[0] . $coworkerNames[$key]['first_name'] . ' ' . $coworkerNames[$key]['last_name'] . $path[1]
                    ];
                }
            }
        }

        return $destinations;
    }
}
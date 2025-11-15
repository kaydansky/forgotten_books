<?php
/**
 * @author: AlexK
 * Date: 27-Jan-19
 * Time: 5:45 PM
 */

namespace ForgottenBooks\Domain\Statistics;

use ForgottenBooks\DB\DBInstance;

class StatisticsModel
{
    private $db;
    private $auth;

    public function __construct($auth = null)
    {
        $this->db = DBInstance::dsn();
        $this->auth = $auth;
    }

    public function getBooksWaiting()
    {
//        return $this->db->select('SELECT queue_number, COUNT(id) AS numBooks FROM queue t1 WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) AND assigned IS NOT NULL AND completed IS NULL AND queue_number < 10 AND requested_by_user IS NOT NULL GROUP BY queue_number ORDER BY queue_number');
        return $this->db->select('SELECT queue_number, COUNT(id) AS numBooks FROM queue t1 WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) AND completed IS NULL AND queue_number < 10 GROUP BY queue_number ORDER BY queue_number');
    }

    public function getBooksCompletedWeek()
    {
        return $this->db->select('SELECT queue_number, COUNT(queue_number) AS numBooks FROM queue t1 WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND completed IS NOT NULL AND queue_number < 10) AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY) GROUP BY queue_number ORDER BY queue_number');
    }

    public function getBooksCompletedMonth()
    {
        return $this->db->select('SELECT queue_number, COUNT(queue_number) AS numBooks FROM queue t1 WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND completed IS NOT NULL AND queue_number < 10) AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH) GROUP BY queue_number ORDER BY queue_number');
    }

    public function getBooksCompleted()
    {
        return $this->db->select('SELECT pibn, MAX(id) as id, FROM_UNIXTIME(MAX(added)) AS completionDate, MAX(completed) as completed FROM queue t1 WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) AND queue_number = 10 GROUP BY pibn ORDER BY MAX(completed) DESC');
    }

    public function getBooksRemoved()
    {
        return $this->db->select('SELECT pibn, MAX(id) as id, FROM_UNIXTIME(MAX(added)) AS completionDate, MAX(completed) as completed FROM queue t1 WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) AND queue_number = 11 GROUP BY pibn ORDER BY MAX(completed) DESC');
    }
}
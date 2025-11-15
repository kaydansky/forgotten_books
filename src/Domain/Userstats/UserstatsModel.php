<?php
/**
 * @author: AlexK
 * Date: 31-Jan-19
 * Time: 2:11 AM
 */

namespace ForgottenBooks\Domain\Userstats;

use ForgottenBooks\DB\DBInstance;

class UserstatsModel
{
    private $db;

    public function __construct()
    {
        $this->db = DBInstance::dsn();
    }

    public function booksLastWeek($userId)
    {
        $r = $this->db->selectRow('SELECT COUNT(*) AS BooksCompleted FROM (SELECT COUNT(pibn) FROM queue WHERE requested_by_user = ? AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY) GROUP BY pibn) AS t', [$userId]);

        if (! $r) {
            return 0;
        }

        return $r['BooksCompleted'] ?: 0;
    }

    public function booksLastMonth($userId)
    {
        $r = $this->db->selectRow('SELECT COUNT(*) AS BooksCompleted FROM (SELECT COUNT(pibn) FROM queue WHERE requested_by_user = ? AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH) GROUP BY pibn) AS t', [$userId]);

        if (! $r) {
            return 0;
        }

        return $r['BooksCompleted'] ?: 0;
    }

    public function wordsLastWeek($userId)
    {
        $r = $this->db->selectRow('SELECT SUM(word_count) AS WordCount FROM queue WHERE id IN (SELECT MAX(id) FROM queue WHERE requested_by_user = ? AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY) GROUP BY pibn)', [$userId]);

        if (! $r) {
            return 0;
        }

        return $r['WordCount'] ?: 0;
    }

    public function wordsLastMonth($userId)
    {
        $r = $this->db->selectRow('SELECT SUM(word_count) AS WordCount FROM queue WHERE id IN (SELECT MAX(id) FROM queue WHERE requested_by_user = ? AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH) GROUP BY pibn)', [$userId]);

        if (! $r) {
            return 0;
        }

        return $r['WordCount'] ?: 0;
    }

    public function rejectedLastWeek($userId)
    {
        $r = $this->db->selectRow('SELECT COUNT(DISTINCT pibn) AS Rejections FROM `queue` t1 WHERE queue_number = (SELECT queue_number FROM queue WHERE too_many_errors = 1 AND pibn = t1.pibn ORDER BY id DESC LIMIT 1) AND FROM_UNIXTIME(added) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY) AND added_by_user = ? AND added IS NOT NULL AND too_many_errors = 0 GROUP BY pibn', [$userId]);

        if (! $r) {
            return 0;
        }

        return $r['Rejections'] ?: 0;
    }

    public function rejectedLastMonth($userId)
    {
        $r = $this->db->selectRow('SELECT COUNT(DISTINCT pibn) AS Rejections FROM `queue` t1 WHERE queue_number = (SELECT queue_number FROM queue WHERE too_many_errors = 1 AND pibn = t1.pibn ORDER BY id DESC LIMIT 1) AND FROM_UNIXTIME(added) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH) AND added_by_user = ? AND added IS NOT NULL AND too_many_errors = 0 GROUP BY pibn', [$userId]);

        if (! $r) {
            return 0;
        }

        return $r['Rejections'] ?: 0;
    }
}
<?php
/**
 * @author: AlexK
 * Date: 15-Jan-19
 * Time: 10:26 PM
 */

namespace ForgottenBooks\Domain\Queue;

use Delight\Auth\Role;
use ForgottenBooks\DB\DBInstance;
use ForgottenBooks\Helpers\Sanitizer;

class QueueModel
{
    private $db;
    private $auth;
    private $pibn;
    private $isbn;
    private $title;
    private $subtitle;
    private $author1;
    private $author2;
    private $volume;
    private $volumes_total;
    private $word_count;
    private $images_number;
    private $pages_number;
    private $missing_pages;
    private $blurb;
    private $comments;
    private $supervisor_attention;
    private $coordinator_attention;
    private $too_many_errors;
    private $queue_number;
    private $requested_user;
    private $requested_user_3;
    private $requested_user_4;
    private $has_images;

    public function __construct($auth = null)
    {
        $this->db = DBInstance::dsn();
        $this->auth = $auth;
        $this->pibn = null;
    }

    public function uploadNewBook()
    {
        $this->filterPost();

        if (! $this->pibn) {
            return false;
        }

        if ($this->checkDuplicate($this->pibn)) {
            $this->db->delete('queue', ['pibn' => $this->pibn]);
        }

        $this->db->exec(
<<<'SQL'
INSERT INTO queue 
(pibn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, comments) 
VALUES 
(?, ?, ?, UNIX_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL
, [$this->pibn, $this->auth->getUserId(), $this->queue_number, $this->title, $this->subtitle, $this->author1, $this->author2, $this->volume, $this->volumes_total, $this->word_count, $this->images_number, $this->comments]);

        if ($this->requested_user) {
            $this->pickupBook($this->pibn, $this->queue_number);
        }

        return true;
    }

    public function getBookAssigned($queueNumber)
    {
//        $this->db->exec('SET NAMES \'utf8\'');
        return $this->db->selectRow(
<<<'SQL'
SELECT t1.*, t2.user_id, t2.first_name, t2.last_name, t2.base_pay_rate 
FROM queue t1 
    JOIN workers t2 ON t2.user_id = t1.requested_by_user 
WHERE t1.assigned IS NOT NULL 
  AND t1.completed IS NULL 
  AND t1.queue_number = ? 
  AND t1.requested_by_user = ? 
  AND t1.pibn NOT IN (SELECT pibn FROM queue WHERE queue_number=10 OR queue_number=11) 
ORDER BY t1.assigned ASC LIMIT 1
SQL
, [$queueNumber, $this->auth->getUserId()]);
    }

    public function getBookAvailable($queueNumber)
    {
//        $this->db->exec('SET NAMES \'utf8\'');

        if ($this->auth->hasRole(Role::COORDINATOR)) {
            return $this->db->selectRow(
<<<'SQL'
SELECT t1.*, t2.user_id, t2.first_name, t2.last_name, t2.base_pay_rate 
FROM queue t1 
  JOIN workers t2 ON (t1.added_by_user = t2.user_id OR t1.requested_by_user = t2.user_id) 
WHERE completed IS NULL 
  AND queue_number = ? 
  AND t1.pibn NOT IN (SELECT pibn FROM queue WHERE queue_number=10 OR queue_number=11) 
  AND t1.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND queue_number = ?) 
ORDER BY too_many_errors DESC, added ASC LIMIT 1
SQL
, [$queueNumber, $queueNumber]);
        } else {
            return $this->db->selectRow(
<<<'SQL'
SELECT t1.*, t2.user_id, t2.first_name, t2.last_name, t2.base_pay_rate 
FROM queue t1 
  JOIN workers t2 ON (t1.added_by_user = t2.user_id OR t1.requested_by_user = t2.user_id) 
WHERE completed IS NULL 
  AND (requested_by_user IS NULL OR requested_by_user = ?)
  AND queue_number = ? 
  AND t1.pibn NOT IN (SELECT pibn FROM queue WHERE queue_number=10 OR queue_number=11) 
  AND t1.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND queue_number = ?) 
ORDER BY too_many_errors DESC, added ASC LIMIT 1
SQL
, [$this->auth->getUserId(), $queueNumber, $queueNumber]);
        }
    }

    public function getAvailableBooks($queueNumber)
    {
        return $this->db->select(
<<<'SQL'
SELECT MAX(t1.id) AS id, t1.pibn 
FROM queue t1 
WHERE t1.requested_by_user = ? 
  AND t1.completed IS NULL 
  AND t1.queue_number = ? 
  AND t1.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND queue_number = ?) 
  AND t1.id NOT IN (SELECT id FROM queue WHERE queue_number = ? AND completed IS NOT NULL) 
  AND t1.pibn NOT IN (SELECT pibn FROM queue WHERE queue_number=10 OR queue_number=11) 
GROUP BY t1.pibn ORDER BY id ASC LIMIT 3
SQL
, [$this->auth->getUserId(), $queueNumber, $queueNumber, $queueNumber]);
    }

    public function getBookComments($pibn)
    {
        return $this->db->select('SELECT t2.*, t1.added_by_user, t1.requested_by_user, FROM_UNIXTIME(added) AS added, FROM_UNIXTIME(assigned) AS assigned, FROM_UNIXTIME(completed) AS completed, t1.comments, t1.supervisor_attention, t1.coordinator_attention, t1.too_many_errors, t3.roles_mask FROM queue t1 LEFT JOIN workers t2 ON (t1.added_by_user = t2.user_id OR t1.requested_by_user = t2.user_id) LEFT JOIN users t3 ON (t3.id = t2.user_id) WHERE t1.comments IS NOT NULL AND pibn = ? ORDER BY t1.id DESC', [$pibn]);
    }

    public function getBookWorkPath($pibn)
    {
        return $this->db->select('SELECT t2.*, t1.*, FROM_UNIXTIME(t1.added) AS added, FROM_UNIXTIME(t1.assigned) AS assigned, FROM_UNIXTIME(t1.completed) AS completed, t3.roles_mask FROM queue t1 LEFT JOIN workers t2 ON (t1.added_by_user = t2.user_id OR t1.requested_by_user = t2.user_id) LEFT JOIN users t3 ON (t3.id = t2.user_id) WHERE t1.pibn = ? ORDER BY t1.id DESC', [$pibn]);
    }

    public function getPreviousWorker($pibn)
    {
        return $this->db->selectRow('SELECT t2.user_id, t2.first_name, t2.last_name, t2.base_pay_rate, t1.queue_number FROM queue t1 JOIN workers t2 ON t1.requested_by_user = t2.user_id WHERE t1.pibn = ? AND requested_by_user IS NOT NULL ORDER BY t1.id DESC LIMIT 1 OFFSET 1', [$pibn]);
    }

    public function getBookById($id)
    {
//        $this->db->exec('SET NAMES \'utf8\'');
        return $this->db->selectRow(<<<'SQL'
SELECT t3.*, t2.user_id, t2.first_name, t2.last_name, t2.base_pay_rate 
FROM queue t1 
    LEFT JOIN workers t2 
        ON (t1.added_by_user = t2.user_id OR t1.requested_by_user = t2.user_id) 
    LEFT JOIN queue t3 
        ON (t3.pibn = t1.pibn AND t3.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn))
WHERE t1.id = ? LIMIT 1
SQL
, [$id]);
    }

    public function getUserBooks($userId)
    {
        $books = $this->db->select(<<<'SQL'
SELECT id, pibn, title, FROM_UNIXTIME(added) as added, FROM_UNIXTIME(assigned) as assigned, FROM_UNIXTIME(completed) as completed 
FROM queue t1 
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn AND (added_by_user = ? OR requested_by_user = ?)) 
GROUP BY pibn 
ORDER BY completed DESC, assigned DESC, added DESC
SQL
, [$userId, $userId]);

        if (! $books) {
            return null;
        }

        foreach ($books as $key => $value) {
            $comments = $this->db->selectRow('SELECT comments FROM queue WHERE pibn = ? AND comments IS NOT NULL LIMIT 1', [$value['pibn']]);

            if ($comments) {
                $books[$key]['comments'] = true;
            }
        }

        return $books;
    }

    public function autocompletePibn($term)
    {
        return $this->db->select('SELECT id, pibn FROM queue t1 WHERE pibn LIKE ? AND id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) GROUP BY pibn', ["%$term%"]);
    }

    public function pickupBook($pibn, $queue)
    {
        if ($this->auth->hasRole(Role::COORDINATOR)) {
            $this->db->exec('DELETE FROM queue WHERE pibn = ? AND queue_number = ? AND requested_by_user != ?', [$pibn, $queue, $this->auth->getUserId()]);
        }

        $this->db->exec(
<<<'SQL'
INSERT INTO queue
(pibn, isbn, requested_by_user, queue_number, assigned, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pages_number, missing_pages, blurb, supervisor_attention, coordinator_attention, too_many_errors, has_images)
SELECT pibn, isbn, ?, queue_number, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pages_number, missing_pages, blurb, supervisor_attention, coordinator_attention, too_many_errors, has_images
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = ?)
SQL
, [($this->requested_user ?: $this->auth->getUserId()), $pibn]);
    }

    public function moveBook($id, $pibn, $queueNumber)
    {
        $this->filterPost();

        try {
            $this->db->beginTransaction();

            $this->db->exec(
<<<'SQL'
UPDATE queue 
SET completed = UNIX_TIMESTAMP(), isbn = ?, title = ?, subtitle = ?, author1 = ?, author2 = ?, volume = ?, volumes_total = ?, word_count = ?, images_number = ?, pages_number = ?, missing_pages = ?, blurb = ?
WHERE `id` = ?
SQL
, [$this->isbn, $this->title, $this->subtitle, $this->author1, $this->author2, $this->volume, $this->volumes_total, $this->word_count, $this->images_number, $this->pages_number, $this->missing_pages, $this->blurb, $id]);

            $this->db->exec(
<<<'SQL'
INSERT INTO queue
(pibn, isbn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pages_number, missing_pages, blurb, comments, supervisor_attention, coordinator_attention, too_many_errors, has_images)
SELECT pibn, isbn, ?, ?, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pages_number, missing_pages, blurb, ?, ?, ?, ?, ?
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = ?)
SQL
, [$this->auth->getUserId(), $queueNumber, $this->comments, $this->supervisor_attention, $this->coordinator_attention, $this->too_many_errors, $this->has_images, $pibn]);

            $this->db->commit();
        } catch (\PDOException $ex) {
            $this->db->rollBack();
            die($ex->getMessage());

            return false;
        }

        if ($this->requested_user && $queueNumber < 10) {
            $this->pickupBook($pibn, $queueNumber);
        }

        return true;
    }

    public function checkDuplicate($pibn)
    {
        return $this->db->selectRow('SELECT t1.pibn, t1.title FROM queue t1 WHERE t1.pibn = ? AND t1.id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) LIMIT 1', [$pibn]);
    }

    private function filterPost()
    {
        $this->pibn = filter_input(INPUT_POST, 'pibn', FILTER_SANITIZE_NUMBER_INT);
        $this->isbn = Sanitizer::sanitize(filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->title = Sanitizer::sanitize(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->subtitle = Sanitizer::sanitize(filter_input(INPUT_POST, 'subtitle', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->author1 = Sanitizer::sanitize(filter_input(INPUT_POST, 'author1', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->author2 = Sanitizer::sanitize(filter_input(INPUT_POST, 'author2', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->missing_pages = Sanitizer::sanitize(filter_input(INPUT_POST, 'missing_pages', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->blurb = Sanitizer::sanitize(filter_input(INPUT_POST, 'blurb', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->comments = Sanitizer::sanitize(filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $this->volume = filter_input(INPUT_POST, 'volume', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->volumes_total = filter_input(INPUT_POST, 'volumes_total', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->word_count = filter_input(INPUT_POST, 'word_count', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->images_number = filter_input(INPUT_POST, 'images_number', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->pages_number = filter_input(INPUT_POST, 'pages_number', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->supervisor_attention = filter_input(INPUT_POST, 'supervisor_attention', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->coordinator_attention = filter_input(INPUT_POST, 'coordinator_attention', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->too_many_errors = filter_input(INPUT_POST, 'too_many_errors', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->queue_number = filter_input(INPUT_POST, 'queue_number', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        $this->requested_user = filter_input(INPUT_POST, 'requested_user', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->requested_user_3 = filter_input(INPUT_POST, 'requested_user_3', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->requested_user_4 = filter_input(INPUT_POST, 'requested_user_4', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $this->has_images = filter_input(INPUT_POST, 'has_images', FILTER_SANITIZE_NUMBER_INT) ?: 0;

        if ($this->requested_user_3) {
            $this->requested_user = $this->requested_user_3;
        } elseif ($this->requested_user_4) {
            $this->requested_user = $this->requested_user_4;
        }
    }
}
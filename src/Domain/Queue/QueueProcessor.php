<?php
/**
 * @author: AlexK
 * Date: 17-Jan-19
 * Time: 5:19 PM
 */

namespace ForgottenBooks\Domain\Queue;

class QueueProcessor implements QueueInterface
{
    private $model;
    private $queueNumber;
    private $location;
    private $id;
    private $auth;

    public $data;
    public $template;
    public $templateRequestBook;
    public $active;
    public $comments;
    public $queueTitle;
    public $previousWorker;

    public function __construct($queueNumber, $template, $active, $location, $queueTitle, $auth)
    {
        $this->model = new QueueModel($auth);
        $this->auth = $auth;
        $this->queueNumber = $queueNumber;
        $this->template = $template;
        $this->active = $active;
        $this->location = $location;
        $this->queueTitle = $queueTitle;
        $this->id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $this->requestBook();
        $this->checkForAssignedBook();
        $this->completeBook();
    }

    public function requestBook()
    {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_NUMBER_INT);
        $queue = filter_input(INPUT_POST, 'queue', FILTER_SANITIZE_NUMBER_INT);

        if ($request && $queue) {
            $this->model->pickupBook($request, $queue);
        }
    }

    public function checkForAssignedBook()
    {
        if (! $this->id) {
            $this->data = $this->model->getBookAssigned($this->queueNumber);
        }

        if ($this->data) {
            $this->comments = $this->model->getBookComments($this->data['pibn']);
            $this->previousWorker = $this->fetchPreviousWorker($this->model->getPreviousWorker($this->data['pibn']));
            $this->templateRequestBook = 'Queue/TextAssignedBook.html';
        } else {
            $this->checkForAvailableBook();
        }
    }

    public function checkForAvailableBook()
    {
        $this->data = $this->id ? $this->model->getBookById($this->id) : $this->model->getBookAvailable($this->queueNumber);

        if ($this->data) {
            $this->comments = $this->model->getBookComments($this->data['pibn']);
            $this->previousWorker = $this->fetchPreviousWorker($this->data);

            if ($this->data['requested_by_user'] == $this->auth->getUserId()) {
                $this->templateRequestBook = 'Queue/TextAssignedBook.html';
            } elseif ($this->data['queue_number'] < 10 && ! $this->id) {
                $this->templateRequestBook = 'Queue/ButtonRequestBook.html';
            }
        } else {
            $this->templateRequestBook = 'Queue/TextNoAvailableBook.html';
        }
    }

    public function completeBook()
    {
        $next_queue = filter_input(INPUT_POST, 'next_queue', FILTER_SANITIZE_NUMBER_INT);

        if (! $next_queue) {
            return false;
        }

        if (! $this->data['assigned']) {
            return false;
        }

        $this->model->moveBook($this->data['id'], $this->data['pibn'], $next_queue);
        header('location: ' . $this->location);
    }

    private function fetchPreviousWorker($data)
    {
        return $data ? ['first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'base_pay_rate' => $data['base_pay_rate'], 'queue_number' => $data['queue_number']] : null;
    }
}
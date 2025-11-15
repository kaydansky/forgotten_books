<?php
/**
 * @author: AlexK
 * Date: 15-Jan-19
 * Time: 5:48 PM
 */

namespace ForgottenBooks\Domain\Queue;

use Delight\Auth\Role;
use ForgottenBooks\Autocomplete\AutocompleteFactory;
use ForgottenBooks\Helpers\ResponseCode;
use ForgottenBooks\Helpers\Sanitizer;
use ForgottenBooks\Domain\Users\UserAvailable;

class QueueController
{
    private $template;
    private $templateHeader = 'Queue/Header.html';
    private $templateCurrentBook = 'Queue/TextCurrentBook.html';
    private $templateSupervisorAttention = 'Queue/TextSupervisorAttention.html';
    private $templateCoordinatorAttention = 'Queue/TextCoordinatorAttention.html';
    private $templateTooManyErrors = 'Queue/TextTooManyErrors.html';
    private $templateHasImages = 'Queue/TextHasImages.html';
    private $templateButtonSpecifiedId = 'Queue/ButtonSpecifiedId.html';
    private $path = [];
    private $auth;
    private $builder;
    private $templateNavigation;
    private $active;
    private $data;
    private $notification;
    private $currentBook;
    private $requestBook;
    private $displayForm = 'd-none';
    private $displaySubmit = 'd-none';
    private $fieldsetDisabled;
    private $noticeSupervisor;
    private $noticeCoordinator;
    private $bookComments;
    private $queueTitle;
    private $availableBooks;
    private $bookId;
    private $bookWorkPath;
    private $specifiedId;
    private $queueNumber;
    private $pibn;
    private $selectWorker;
    private $selectWorkerImage;
    private $selectWorkerLayout;
    private $previousQueueWorker;
    private $resolver;
    private $displayPaths;
    private $assigneeName;
    private $previousWorker;
    private $previousQueueNumber;
    private $sourceFileLocation;
    private $hasImages;

    public function inject($path, $auth, $builder, $resolver)
    {
        $this->path = array_replace($this->path, $path);
        $this->auth = $auth;
        $this->builder = $builder;
        $this->resolver = $resolver;
    }

    public function action()
    {
        $term = Sanitizer::sanitize(filter_input(INPUT_GET, 'term', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $slider_queue = filter_input(INPUT_POST, 'slider_queue', FILTER_SANITIZE_NUMBER_INT);
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $check_duplicate_pibn = filter_input(INPUT_POST, 'check_duplicate_pibn', FILTER_SANITIZE_NUMBER_INT);

        if ($term) {
            die((new AutocompleteFactory())->pibn($term)->json);
        }

        if ($slider_queue) {
            die((new UserAvailable($this->builder, $this->resolver, $this->auth))->worker($slider_queue));
        }

        if ($check_duplicate_pibn) {
            $checkResult = (new QueueModel())->checkDuplicate($check_duplicate_pibn);
            die($checkResult ? json_encode($checkResult) : '0');
        }

        if ($this->auth->hasRole(Role::COORDINATOR)) {
            $this->templateNavigation = 'Users/Navigation.html';
        } else {
            $this->templateNavigation = 'Queue/Navigation.html';
        }

        if (! isset($this->path[2])) {
            header('location: /queue');
            die();
        }

        $role1 = $role2 = $this->auth->getRoles();
        
        if (isset($role1) && is_array($role1) && count($role1) > 0) {
            $function = $this->auth->hasRole(Role::COORDINATOR) && $this->path[2] ? $this->path[2] : strtolower(array_shift($role1));
        }

        $function = $this->path[2] == 'upload' && $this->auth->hasRole(Role::COORDINATOR) ? 'upload' : $function;
        $queue = new QueueFactory($this->auth);

        if (! method_exists($queue, $function)) {
            ResponseCode::code404();
        }

        $nameQueue = array_flip(QUEUE_NAMES);
        $this->queueNumber = $this->auth->hasRole(Role::COORDINATOR) && $this->path[2] && $this->path[2] != 'coordinator'
            ? $nameQueue[ucwords(str_replace('_', ' ', $this->path[2]))]
            : ROLE_QUEUE[array_keys($role2)[0]];

        $object = $queue->$function();
        $this->data = $object->data;
        $this->pibn = $object->data['pibn'] ?? 0;
        $this->template = $object->template;
        $this->active = $object->active;
        $this->queueTitle = $object->queueTitle;
        $this->requestBook = $object->templateRequestBook ? $this->builder->setTemplate($object->templateRequestBook)->template : null;
        $this->displayForm = $object->data ? 'd-block' : 'd-none';
        $this->noticeSupervisor = ($object->data['supervisor_attention'] ?? false) ? $this->builder->setTemplate($this->templateSupervisorAttention)->template : null;
        $this->noticeCoordinator = ($object->data['coordinator_attention'] ?? false) ? $this->builder->setTemplate($this->templateCoordinatorAttention)->template : null;
        $this->templateTooManyErrors = ($object->data['too_many_errors'] ?? false) ? $this->builder->setTemplate($this->templateTooManyErrors)->template : null;
        $this->hasImages = ($object->data['has_images'] ?? false) ? $this->builder->setTemplate($this->templateHasImages)->template : null;
        $this->bookComments = (new BindComments($this->builder))->bind($object->comments);
        $this->availableBooks = (new BooksAvailable($this->auth, $this->builder))->check($this->queueNumber, $object->data['id'] ?? null);
        $this->bookId = $id ? '?id=' . $id : '';
        $this->assigneeName = ($object->data['first_name'] ?? '&boxh;') . ' ' . ($object->data['last_name'] ?? '&boxh;');

        if ($object->previousWorker) {
            $this->previousWorker = $object->previousWorker['first_name'] . ' ' . $object->previousWorker['last_name'];
            $this->previousQueueNumber = $object->previousWorker['queue_number'];
        } else {
            $this->previousWorker = 'nobody';
            $this->previousQueueNumber = 1;
        }

        $this->currentBook = $object->data
            ? $this->builder->setTemplate($this->templateCurrentBook)->addBrackets(
                [
                    'pibn' => $object->data['pibn'],
                    'PREVIOUS_WORKER_NAME' => $this->previousWorker
                ])->build()->result
            : null;

        if (($object->data['assigned'] ?? false) && ($object->data['requested_by_user'] ?? null) == $this->auth->getUserId()) {
            $this->displayPaths = 'd-flex';
            $this->displaySubmit = 'd-block';
            $this->fieldsetDisabled = '';
        } elseif ($this->auth->hasRole(Role::COORDINATOR)
            && ! ($object->data['completed'] ?? false)
//            && $object->data['assigned']
            && ($object->data['queue_number'] ?? 0) < 10
            && $this->path[2] == 'coordinator') {
            $this->displayPaths = 'd-flex';
            $this->displaySubmit = 'd-block';
            $this->fieldsetDisabled = '';
        } else {
            $this->displayPaths = 'd-none';
            $this->displaySubmit = 'd-none';
            $this->fieldsetDisabled = 'disabled';
        }

        if ($this->auth->hasRole(Role::COORDINATOR) && $function == 'coordinator') {
            if (($object->data['requested_by_user'] ?? null) != $this->auth->getUserId()) {
                $this->specifiedId = $this->builder->setTemplate($this->templateButtonSpecifiedId)->template;
            }

            $this->bookWorkPath = (new BindBookWorkPath($this->auth, $this->builder))->bind($object->data['pibn'] ?? null);
        } elseif ($this->auth->hasRole(Role::COORDINATOR)) {
            $this->availableBooks = '';
        }

        if ($this->queueNumber == 0 || $this->queueNumber == 3 || $this->queueNumber == 5 || $this->queueNumber == 7 || $this->queueNumber == 9) {
            $queueNext = $this->queueNumber == 9 ? 1 : $this->queueNumber + 1;
            $selectWorker = new UserAvailable($this->builder, $this->resolver, $this->auth);
            $this->selectWorker = $selectWorker->worker($queueNext);
            $selectWorker->worker($this->queueNumber - 1);
            $this->previousQueueWorker = $selectWorker->userId;
        }

        if ($this->queueNumber == 2) {
            $selectWorker = new UserAvailable($this->builder, $this->resolver, $this->auth);
            $selectWorker->worker(1);
            $this->previousQueueWorker = $selectWorker->userId;
            $this->selectWorkerImage = (new UserAvailable($this->builder, $this->resolver, $this->auth))->worker(3, 'requested_user_3');
            $this->selectWorkerLayout = (new UserAvailable($this->builder, $this->resolver, $this->auth))->worker(4, 'requested_user_4');
        }

        if ($this->auth->hasAnyRole(Role::COORDINATOR, Role::LAYOUT_SUPERVISOR, Role::PROOFING_SUPERVISOR, Role::BLURB_EDITOR)) {
            $filesPath = explode('|', QUEUE_FOLDER[$this->previousQueueNumber]);
            $this->sourceFileLocation = $this->pibn
                . $filesPath[0]
                . ($filesPath[0] != '/Consolidation' ? $this->previousWorker : '')
                . (($object->data['completed'] ?? false) ? $filesPath[2] : $filesPath[2]);
        }
    }

    public function output()
    {
        return
            [
                'CONTAINER' => $this->data
                    ? $this->builder->setTemplate($this->template)->addBrackets($this->data)->build()->result
                    : $this->builder->setTemplate($this->template)->template,
                'NAVIGATION' => $this->builder->setTemplate($this->templateNavigation)->addBrackets([$this->active => ' active'])->build()->result,
                'HEADER' => $this->builder->setTemplate($this->templateHeader)->template,
                'QUEUE_TITLE' => $this->queueTitle,
                'WORKER_NAME' => $_SESSION['user_info']['first_name'] . '&nbsp;' . $_SESSION['user_info']['last_name'],
                'WORKER_ID' => $this->auth->getUserId(),
                'CURRENT_BOOK' => $this->currentBook,
                'REQUEST_BOOK' => $this->requestBook,
                'ATTENTION_NOTICE_SUPERVISOR' => $this->noticeSupervisor,
                'ATTENTION_NOTICE_COORDINATOR' => $this->noticeCoordinator,
                'DISPLAY_FORM' => $this->displayForm,
                'DISPLAY_SUBMIT' => $this->displaySubmit,
                'DISPLAY_PATHS' => $this->displayPaths,
                'FIELDSET_DISABLED' => $this->fieldsetDisabled,
                'BOOK_COMMENTS' => $this->bookComments,
                'QUEUE_NAMES' => "'" . implode("','", QUEUE_NAMES) . "'",
                'QUEUE_FOLDER' => "'" . implode("','", QUEUE_FOLDER) . "'",
                'NOTIFICATION' => $this->notification,
                'AVAILABLE_BOOKS' => $this->availableBooks,
                'BOOK_ID' => $this->bookId,
                'BOOK_WORK_PATH' => $this->bookWorkPath,
                'SPECIFIED_ID' => $this->specifiedId,
                'PIBN' => $this->pibn,
                'SELECT_WORKER' => $this->selectWorker,
                'SELECT_WORKER_IMAGE' => $this->selectWorkerImage,
                'SELECT_WORKER_LAYOUT' => $this->selectWorkerLayout,
                'ASSIGNEE_NAME' => $this->assigneeName,
                'PREVIOUS_WORKER' => $this->previousWorker,
                'SOURCE_FILE_LOCATION' => $this->sourceFileLocation,
                'HAS_IMAGES' => $this->hasImages,
                'TOO_MANY_ERRORS' => $this->templateTooManyErrors,
                'PREVIOUS_QUEUE_WORKER' => $this->previousQueueWorker,
            ];
    }
}
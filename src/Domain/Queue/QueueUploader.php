<?php
/**
 * @author: AlexK
 * Date: 16-Jan-19
 * Time: 12:19 AM
 */

namespace ForgottenBooks\Domain\Queue;


class QueueUploader implements UploadInterface
{
    private $model;

    public $template;
    public $active = 'ACTIVE_UPLOAD';
    public $data;
    public $comments;
    public $queueTitle = 'Upload New Book';
    public $templateRequestBook;
    public $previousWorker;

    public function __construct($auth)
    {
        $this->model = $model = new QueueModel($auth);
        $this->template = 'Queue/Upload.html';
        $this->upload();
    }


    public function upload()
    {
        if ($this->model->uploadNewBook()) {
            header('location: /queue/upload');
        }
    }
}
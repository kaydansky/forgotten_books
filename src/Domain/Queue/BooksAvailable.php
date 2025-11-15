<?php
/**
 * @author: AlexK
 * Date: 24-Jan-19
 * Time: 4:18 PM
 */

namespace ForgottenBooks\Domain\Queue;


class BooksAvailable
{
    private $model;
    private $builder;
    private $template = 'Queue/ButtonAvailableBooks.html';

    public function __construct($auth, $builder)
    {
        $this->model = new QueueModel($auth);
        $this->builder = $builder;
    }

    public function check($queueNumber, $defaultId = 0)
    {
        $data = $this->model->getAvailableBooks($queueNumber);
        $filesPath = explode('|', QUEUE_FOLDER[$queueNumber]);

        if (! $data) {
            return null;
        }

        $list = '';

        foreach ($data as $value) {
            if ($value['id'] == $defaultId) {
                continue;
            }

//            $previous = $this->model->getPreviousWorker($value['pibn']);
            $list .= '<li class="list-group-item lead"><a href="/queue/?id='
                . $value['id']
                . '">'
                . $value['pibn']
                . '</a>'
                . ' <span class="mark">'
                . $value['pibn']
                . $filesPath[0]
//                . $previous['first_name']
                . $_SESSION['user_info']['first_name']
                . ' '
//                . $previous['last_name']
                . $_SESSION['user_info']['last_name']
                . $filesPath[1]
                . '</span>' . '</li>';
        }

        if (! $list) {
            return null;
        }

        return $this->builder->setTemplate($this->template)->addBrackets(['list' => $list])->build()->result;

//        if ($data) {
//            $id = $next = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
//            $a = [];
//
//            foreach ($data as $k => $v) {
//                $a[$k] = $v['id'];
//            }
//
//            $id = $id ?: ($defaultId ?: $a[0]);
//
//            foreach ($a as $value) {
//                if ($value < $id) {
//                    $next = $value;
//                    break;
//                } else {
//                    $next = $a[0];
//                }
//            }
//
//            return $this->builder->setTemplate($this->template)->addBrackets(['id' => $next])->build()->result;
//        }
    }
}
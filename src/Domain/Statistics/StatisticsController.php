<?php
/**
 * @author: AlexK
 * Date: 27-Jan-19
 * Time: 3:14 PM
 */

namespace ForgottenBooks\Domain\Statistics;

use Delight\Auth\Role;
use ForgottenBooks\Binder\BinderFactory;

class StatisticsController
{
    private $template = 'Statistics/Container.html';
    private $active = 'ACTIVE_STATISTICS';
    private $path = [];
    private $auth;
    private $builder;
    private $templateNavigation = 'Users/Navigation.html';
    private $data;
    private $queuesHeader;
    private $rowsBooksWaiting;
    private $rowsBooksCompletedWeek;
    private $rowsBooksCompletedMonth;

    public function inject($path, $auth, $builder)
    {
        $this->path = array_replace($this->path, $path);
        $this->auth = $auth;
        $this->builder = $builder;
    }

    public function action()
    {
        if ($this->auth->hasRole(Role::COORDINATOR)) {
            $this->templateNavigation = 'Users/Navigation.html';
        } else {
            header('location: /');
            die();
        }

        $ajax_completed = filter_input(INPUT_GET, 'ajax_completed', FILTER_SANITIZE_NUMBER_INT);
        $ajax_removed = filter_input(INPUT_GET, 'ajax_removed', FILTER_SANITIZE_NUMBER_INT);

        if ($ajax_completed) {
            die((new BinderFactory())->booksCompleted()->json);
        }

        if ($ajax_removed) {
            die((new BinderFactory())->booksRemoved()->json);
        }

        $this->queuesHeader = $this->queuesHeader();
        $this->rowsBooksWaiting = (new BinderFactory())->booksWaiting()->html;
        $this->rowsBooksCompletedWeek = (new BinderFactory())->booksCompletedWeek()->html;
        $this->rowsBooksCompletedMonth = (new BinderFactory())->booksCompletedMonth()->html;
    }

    private function queuesHeader()
    {
        $header = '';
        $a = QUEUE_NAMES;
        unset($a[0], $a[10], $a[11]);

        foreach ($a as $value) {
            $header .= '<th class="font-weight-normal">' . $value . '</th>';
        }

        return $header;
    }

    public function output()
    {
        return
            [
                'CONTAINER' => $this->data
                    ? $this->builder->setTemplate($this->template)->addBrackets($this->data)->build()->result
                    : $this->builder->setTemplate($this->template)->template,
                'NAVIGATION' => $this->builder->setTemplate($this->templateNavigation)->addBrackets([$this->active => ' active'])->build()->result,
                'QUEUES_HEADER' => $this->queuesHeader,
                'ROWS_BOOKS_WAITING' => $this->rowsBooksWaiting,
                'ROWS_BOOKS_COMPLETED_WEEK' => $this->rowsBooksCompletedWeek,
                'ROWS_BOOKS_COMPLETED_MONTH' => $this->rowsBooksCompletedMonth,
            ];
    }
}
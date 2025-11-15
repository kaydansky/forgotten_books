<?php
/**
 * @author: AlexK
 * Date: 25-Jan-19
 * Time: 12:55 AM
 */

namespace ForgottenBooks\Domain\Queue;

use Delight\Auth\Role;
use ForgottenBooks\Helpers\Format;

class BindBookWorkPath
{
    private $model;
    private $builder;
    private $templateWorkPath = 'Queue/BookWorkPath.html';

    public function __construct($auth, $builder)
    {
        $this->model = new QueueModel($auth);
        $this->builder = $builder;
    }

    public function bind($pibn)
    {
        $data = $this->model->getBookWorkPath($pibn);

        if (! $data) {
            return false;
        }

        $list = false;
        $count = count($data) + 1;

        foreach ($data as $value) {
            $count--;
            $date = '';
            $action = '';
            $completed = '';
            $roles = Role::getMap();

            if ($value['added']) {
                $date = $value['added'];
                $action = 'Moved to queue';
            }

            if ($value['assigned'] && ! $value['completed']) {
                $date = $value['assigned'];
                $action = 'Requested queue';
            }

            if ($value['completed']) {
                $assignedDate = new \DateTime($value['assigned']);
                $completedDate = new \DateTime($value['completed']);
                $interval = $assignedDate->diff($completedDate);

                $date = $value['completed'];
                $action = 'Completed queue';
                $completed = '<br>Requested on '
                    . Format::dateTime($value['assigned'])
                    . '. Time spent: ' . $interval->format('%h hrs : %i min : %s sec') . '.';
            }

            $list .= $this->builder
                ->setTemplate($this->templateWorkPath)
                ->addBrackets(
                    [
                        'first_name' => $value['first_name'] ?: '<i class="fa fa-question"></i>',
                        'last_name' => $value['last_name'] ?: '<i class="fa fa-question"></i>',
                        'role' => ucwords(strtolower(str_replace('_', ' ', $roles[$value['roles_mask']]))),
                        'date' => Format::dateTime($date),
                        'comment' => $value['comments'] ? '&mdash;&nbsp;' . $value['comments'] : '',
                        'display_supervisor' => ($value['supervisor_attention'] ? 'd-inline' : 'd-none'),
                        'display_coordinator' => ($value['coordinator_attention'] ? 'd-inline' : 'd-none'),
                        'display_many_errors' => ($value['too_many_errors'] ? 'd-inline' : 'd-none'),
                        'action' => $action
                            . ' <span class="text-monospace">'
                            . $value['queue_number'] . '.'
                            . QUEUE_NAMES[$value['queue_number']] . '</span>.' . $completed,
                        'count' => $count,
                    ])
                ->build()->result;
        }

        return $list;
    }
}
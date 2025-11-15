<?php
/**
 * @author: AlexK
 * Date: 27-Jan-19
 * Time: 3:24 PM
 */

namespace ForgottenBooks\Binder;

use Delight\Auth\Role;
use ForgottenBooks\Helpers\Format;

class BinderProcessor implements BinderInterface
{
    private $templateRolesOptions = 'Binder/RolesOptions.html';
    private $model;
    private $builder;
    private $placeHolder;
    private $data;

    public $html;
    public $json;

    public function __construct($method, $function, $model, $arg = null, $builder = null)
    {
        $this->model = $model;
        $this->builder = $builder;
        $this->placeHolder = array_map(function() { return 0; }, QUEUE_NAMES);
        unset($this->placeHolder[0], $this->placeHolder[10], $this->placeHolder[11]);
        $this->data = $this->model->$function($arg);
        $this->json = json_encode(['data' => false]);
        $this->$method();
    }

    public function bindHtmlBooks()
    {
        if ($this->data) {
            foreach ($this->data as $value) {
                $this->placeHolder[$value['queue_number']] = '<span class="font-weight-bolder">' . $value['numBooks'] . '</span>';
            }
        }

        $this->html = '<td>' . implode('</td><td>', $this->placeHolder) . '</td>';
    }

    public function bindJsonBooks()
    {
        if ($this->data) {
            $rows = [];

            foreach ($this->data as $value) {
                $rows[] = [
                    '<a href="/queue/coordinator/?id=' . $value['id'] . '" title="Final Approval Panel">' . $value['pibn'] . '</a>',
                    $value['title'],
                    $value['author1'],
                    $value['author2'],
                    Format::date($value['completionDate'])
                ];
            }

            $this->json = json_encode(['data' => $rows]);
        }
    }

    public function bindJsonUsers()
    {
        if ($this->data) {
            $rows = [];
            $roles = Role::getMap();
            $selected = [
                1 => '',
                2 => '',
                4 => '',
                8 => '',
                16 => '',
                32 => '',
                64 => '',
                128 => '',
                256 => ''
            ];

            foreach ($this->data as $value) {
                $selected[$value['roles_mask']] = 'selected';
                $options = $this->builder->setTemplate($this->templateRolesOptions)->addBrackets($selected)->build()->result;
                $roleName = ucwords(strtolower(str_replace('_', ' ', $roles[$value['roles_mask']])));
                $reassign = $value['pibn'] && $value['coworkers']
                    ?
                        '<a title="Reassign Books" data-record-id="'
                        . $value['UserId']
                        . '" data-record-title="'
                        . $value['first_name'] . ' '
                        . $value['last_name']
                        . '" data-record-role="'
                        . $roleName
                        . '" data-toggle="modal" data-target="#confirm-reassign" href="#"><i class="fa fa-users"></i></a>'
                    :   '<i class="fa fa-users text-muted" title="No incomplete books nor coworkers"></i>';


                $rows[] = [
                    $value['UserId'],
                    $value['first_name'] . ' ' . $value['last_name'],
                    $value['email'],
                    '<select class="' . $value['UserId'] . '" style="width:130px;display:none;">' . $options . '</select><span class="'. $value['UserId'] .'">' . $roleName . '</span>&nbsp;<editrole class="' . $value['UserId'] . '" style="float:right;cursor:pointer;" title="Click to Change"><i class="fa fa-edit text-primary"></i></editrole>',
                    '<inputrate class="' . $value['UserId'] . '">' . $value['base_pay_rate'] . '</inputrate><editrate class="' . $value['UserId'] . '" style="float:right;cursor:pointer;" title="Click to Change"><i class="fa fa-edit text-primary"></i></editrate>',
                    Format::dateTime($value['Registered'], true),
                    Format::dateTime($value['LastLogin'], true),
                    '<a title="Delete Worker" data-record-id="'
                    . $value['UserId']
                    . '" data-record-title="'
                    . $value['first_name'] . ' '
                    . $value['last_name']
                    . '" data-toggle="modal" data-target="#confirm-delete" href="#"><i class="far fa-trash-alt mr-3"></i></a>'
                    . '<a title="View All Books worked by Worker" href="/userbooks/' . $value['UserId'] . '"><i class="fa fa-book mr-3"></i></a>'
                    . '<a title="View Work Statistics of Worker" href="/userstats/' . $value['UserId'] . '"><i class="fa fa-chart-line mr-3"></i></a>'
                    . $reassign
                ];

                $selected[$value['roles_mask']] = '';
            }

            $this->json = json_encode(['data' => $rows]);
        }
    }

    public function bindJsonUserBooks()
    {
        if ($this->data) {
            $rows = [];

            foreach ($this->data as $value) {
                $comments = '<h4 class="far fa-comment-alt text-muted"></h4>';

                if (isset($value['comments'])) {
                    $comments = '<a title="Show Comments" data-book-id="'
                        . $value['pibn']
                        . '" data-toggle="modal" data-target="#modalComments" href="#"><h4 class="fa fa-comment-alt"></h4></a>';
                }

                $date = '';
                $status = '';

                if ($value['added']) {
                    $date = Format::dateTime($value['added']);
                    $status = '<span class="badge badge-secondary">Added</span>';
                } elseif ($value['assigned']) {
                    $date = Format::dateTime($value['assigned']);
                    $status = '<span class="badge badge-secondary">Assigned</span>';
                } elseif ($value['completed']) {
                    $date = Format::dateTime($value['completed']);
                    $status = '<span class="badge badge-secondary">Completed</span>';
                }

                $rows[] = [
                    '<a href="/queue/coordinator/?id=' . $value['id'] . '" title="Final Approval Panel">' . $value['pibn'] . '</a>',
                    $value['title'],
                    $date,
                    $status,
                    $comments
                ];
            }

            $this->json = json_encode(['data' => $rows]);
        }
    }
}
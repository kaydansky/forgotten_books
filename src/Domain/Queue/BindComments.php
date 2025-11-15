<?php
/**
 * @author: AlexK
 * Date: 25-Jan-19
 * Time: 1:22 AM
 */

namespace ForgottenBooks\Domain\Queue;

use Delight\Auth\Role;
use ForgottenBooks\Helpers\Format;

class BindComments
{
    private $builder;
    private $templateComments = 'Queue/Comments.html';

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function bind($comments)
    {
        if (! $comments) {
            return false;
        }

        $list = false;
        $roles = Role::getMap();

        foreach ($comments as $key => $comment) {
            $date = '';

            if ($comment['added']) {
                $date = $comment['added'];
            }

            if ($comment['assigned']) {
                $date = $comment['assigned'];
            }

            if ($comment['completed']) {
                $date = $comment['completed'];
            }

            $list .= $this->builder
                ->setTemplate($this->templateComments)
                ->addBrackets(
                    [
                        'first_name' => $comment['first_name'] ?: '<i class="fa fa-question"></i>',
                        'last_name' => $comment['last_name'] ?: '<i class="fa fa-question"></i>',
                        'role' => ucwords(strtolower(str_replace('_', ' ', $roles[$comment['roles_mask']]))),
                        'date' => Format::dateTime($date),
                        'comment' => $comment['comments'],
                        'bgcolor' => ($comment['supervisor_attention'] || $comment['coordinator_attention'] || $comment['too_many_errors']
                            ? 'highlighted'
                            : null),
                        'display_supervisor' => ($comment['supervisor_attention'] ? 'd-inline' : 'd-none'),
                        'display_coordinator' => ($comment['coordinator_attention'] ? 'd-inline' : 'd-none'),
                        'display_many_errors' => ($comment['too_many_errors'] ? 'd-inline' : 'd-none'),
                    ])
                ->build()->result;
        }

        return $list;
    }
}
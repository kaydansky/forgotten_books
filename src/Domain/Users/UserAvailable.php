<?php
/**
 * @author: AlexK
 * Date: 01-Mar-19
 * Time: 2:05 PM
 */

namespace ForgottenBooks\Domain\Users;

use Delight\Auth\Role;

class UserAvailable
{
    private $builder;
    private $auth;
    private $modelName = 'ForgottenBooks\Domain\Users\UsersModel';
    private $model;
    private $template = 'Users/UserAvailable.html';

    public $userId;

    public function __construct($builder, $resolver, $auth)
    {
        $this->model = $resolver->resolve($this->modelName);
        $this->builder = $builder;
        $this->auth = $auth;
    }

    public function worker($queueNumber, $selectName = 'requested_user')
    {
        $availableWorkerName = '&boxh;';
        $availableWorkerRole = '';
        $roles = Role::getMap();
        $rolesMask = array_search($queueNumber, ROLE_QUEUE);
        $available = $this->model->getShortestQueueWorker($rolesMask, $queueNumber);

        if ($available) {
            $availableWorkerName = $available[0]['first_name'] . ' ' . $available[0]['last_name'];
            $availableWorkerRole = ', ' . ucwords(strtolower(str_replace('_', ' ', $roles[$available[0]['roles_mask']])));
            $this->userId = $available[0]['id'];
        }

        return $this->builder
            ->setTemplate($this->template)
            ->addBrackets(
                [
                    'AVAILABLE_WORKER_NAME' => $availableWorkerName,
                    'AVAILABLE_WORKER_ROLE' => $availableWorkerRole,
                    'NAME' => $selectName,
                    'QUEUE' => $selectName != 'requested_user' ? $queueNumber : '',
                    'USER_ID' => $this->userId
                ])
            ->build()
            ->result;
    }
}
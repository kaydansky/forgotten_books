<?php
/**
 * @author: AlexK
 * Date: 07-Feb-19
 * Time: 11:35 PM
 */

namespace ForgottenBooks\Domain\Users;

use Delight\Auth\Role;

class UsersAvailable
{
    private $builder;
    private $auth;
    private $modelName = 'ForgottenBooks\Domain\Users\UsersModel';
    private $model;
    private $template = 'Users/ListAvailableUsers.html';
    private $availableOptions = '<option value="" disabled>&boxh;</option>';

    public function __construct($builder, $resolver, $auth)
    {
        $this->model = $resolver->resolve($this->modelName);
        $this->builder = $builder;
        $this->auth = $auth;
    }

    public function options($queueNumber, $selectName = 'requested_user')
    {
        $roles = Role::getMap();
        $rolesMask = array_search($queueNumber, ROLE_QUEUE);
        $available = $this->model->getAvailableWorkersRole($rolesMask);

        if ($available) {
            $this->availableOptions = '';

            foreach ($available as $value) {
                $this->availableOptions .= '<option rolemask="'
                    . $value['roles_mask'] . '" roletitle="'
                    . ucwords(strtolower(str_replace('_', ' ', $roles[$value['roles_mask']]))) . '" value="'
                    . $value['id'] . '">'
                    . $value['first_name'] . ' ' . $value['last_name'] . '</option>';
            }
        }

        return $this->builder
            ->setTemplate($this->template)
            ->addBrackets(
                [
                    'AVAILABLE_OPTIONS' => $this->availableOptions,
                    'ID' => 'SelectWorker' . $queueNumber,
                    'QUEUE' => $queueNumber,
                    'NAME' => $selectName
                ])
            ->build()
            ->result;
    }
}
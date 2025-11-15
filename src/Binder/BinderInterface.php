<?php
/**
 * @author: AlexK
 * Date: 28-Jan-19
 * Time: 3:38 PM
 */

namespace ForgottenBooks\Binder;


interface BinderInterface
{
    public function bindHtmlBooks();

    public function bindJsonBooks();

    public function bindJsonUsers();

    public function bindJsonUserBooks();
}
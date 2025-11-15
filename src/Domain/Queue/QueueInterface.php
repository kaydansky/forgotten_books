<?php
/**
 * @author: AlexK
 * Date: 15-Jan-19
 * Time: 8:38 PM
 */

namespace ForgottenBooks\Domain\Queue;


interface QueueInterface
{
    public function requestBook();

    public function checkForAssignedBook();

    public function checkForAvailableBook();

    public function completeBook();
}
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class TaskNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct(int $id)
    {
        parent::__construct("Task with ID {$id} not found");
    }
}

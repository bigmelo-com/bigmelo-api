<?php


namespace App\Classes\Rules;


use Illuminate\Validation\Rules\Password;

class UserPassword extends Password
{
    /**
     * Adds the given failures, and return false.
     *
     * @param  array|string  $messages
     * @return bool
     */
    protected function fail($messages)
    {
        $this->messages = ['Your password must be a minimum of 6 characters, contain number, and contain one lower and one upper case letter.'];

        return false;
    }
}

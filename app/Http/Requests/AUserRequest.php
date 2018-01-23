<?php

namespace App\Http\Requests;


use App\Exceptions\CustomExceptions\InvalidValueException;

/**
 * Class AUserRequest, provides functionality for varifying UserRequests
 *
 *
 * @package App\Http\Requests
 */
abstract class AUserRequest implements IRequest
{
    /**
     * Checks whether the password fulfills all requirements:
     * Min 8 Symbols, 3 Letters, 2 numbers, 1 special character
     * Max 25 Symbols
     *
     * If the Requirements are not met, an InvalidValueException is thrown
     *
     * @param string $password
     * @throws InvalidValueException
     */
    protected function checkPasswordValidity(string $password)
    {
        if(strlen($password) <  8)
            throw new InvalidValueException('The password is too short!');

        $specialChars = 0;
        $numbers = 0;
        $letters = 0;


        for ($i = 0, $j = strlen($password); $i < $j; $i++) {
            $c = substr($password, $i,1);

            if (preg_match('#[a-zA-Z]#',$c)) {
                $letters++;
            } elseif (preg_match('/^[[:digit:]]$/',$c)) {
                $numbers++;
            } else {
                $specialChars++;
            }
        }

        if($specialChars < 1)
            throw new InvalidValueException('The password must include at least 1 special character!');
        if($letters < 3)
            throw new InvalidValueException('The password must include at least 3 letters!');
        if($numbers < 2)
            throw new InvalidValueException('The password must include at least 2 numbers!');
    }

    protected function checkEmailSyntax(string $email)
    {
        if(!preg_match('.+@.+\..+', $email))
            throw new InvalidValueException('The email is invalid, make sure it has the form *@*.*');
    }
}
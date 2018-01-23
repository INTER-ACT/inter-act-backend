<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Gender implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return ($value === 'm' || $value === 'w');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The value for Gender is invalid, it should be m or w.';
    }
}

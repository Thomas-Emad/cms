<?php

namespace Illuminate\Validation;

class Rule
{
    public static function in(array $values): string
    {
        return 'in:'.implode(',', $values);
    }
}

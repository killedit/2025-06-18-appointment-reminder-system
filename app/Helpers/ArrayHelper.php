<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}

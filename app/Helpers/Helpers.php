<?php

use Illuminate\Support\Str;

if (! function_exists('first_name')) {
    function firstName($name)
    {
        return Str::title(explode(' ', $name)[0]);
    }
}

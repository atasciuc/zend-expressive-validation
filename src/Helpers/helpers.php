<?php

if (! function_exists('endsWith')) {
    /***
     * Check if string ends with a specific str
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0
            && strpos($haystack, $needle, $temp) !== false);
    }
}

if (! function_exists('startsWith')) {
    /***
     * Check if the string starts with a specific string
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

}


if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (startsWith($value, '"') && endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
if (! function_exists('ddd')) {
    /**
     * var_dumps the passed parameter
     */
    function ddd()
    {
        echo '<pre>';
        call_user_func_array('var_dump', func_get_args());
        echo '</pre>';
    }
}

if (! function_exists('dd')) {
    /**
     * var_dumps the passed parameter and dies
     */
    function dd()
    {
        call_user_func_array('ddd', func_get_args());
        die();
    }
}
if (! function_exists('flatten')) {
    function flatten(array $array)
    {
        $return = [];
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }
}
if (! function_exists('isCountRequest')) {
    function isCountRequest(Psr\Http\Message\ServerRequestInterface $request)
    {
        return strpos($request->getUri()->getPath(), '/count') !== false;
    }
}

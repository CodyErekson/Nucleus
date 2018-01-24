<?php
/**
 * A place to define any needed global functions. Required by bootstrap.php
 */


if (!function_exists('d')) {
    /**
     * Dump the passed variables using Symfony var_dumper
     *
     * @param  mixed
     * @return void
     */
    function d(... $args)
    {
        foreach ($args as $x) {
            (new Illuminate\Support\Debug\Dumper)->dump($x);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables using Symfony var_dumper and cease execution
     *
     * @param  mixed
     * @return void
     */
    function dd(... $args)
    {
        function d(... $args)
        {
            foreach ($args as $x) {
                (new Illuminate\Support\Debug\Dumper)->dump($x);
            }
        }
        die();
    }
}

if (!function_exists('ddd')) {
    /**
     * Display the passed variables using dBug
     *
     * @param mixed
     * @return void
     */
    function ddd(... $args)
    {
        foreach ($args as $x) {
            new \dBug\dBug($x);
        }
    }
}

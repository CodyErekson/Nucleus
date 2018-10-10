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
    function d()
    {
        array_map(function ($value) {
            if (class_exists(Symfony\Component\VarDumper\Dumper\CliDumper::class)) {
                $dumper = 'cli' === PHP_SAPI ?
                    new Symfony\Component\VarDumper\Dumper\CliDumper :
                    new Symfony\Component\VarDumper\Dumper\HtmlDumper;
                $dumper->dump((new Symfony\Component\VarDumper\Cloner\VarCloner)->cloneVar($value));
            } else {
                var_dump($value);
            }
        }, func_get_args());
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables using Symfony var_dumper and cease execution
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        array_map(function ($value) {
            if (class_exists(Symfony\Component\VarDumper\Dumper\CliDumper::class)) {
                $dumper = 'cli' === PHP_SAPI ?
                    new Symfony\Component\VarDumper\Dumper\CliDumper :
                    new Symfony\Component\VarDumper\Dumper\HtmlDumper;
                $dumper->dump((new Symfony\Component\VarDumper\Cloner\VarCloner)->cloneVar($value));
            } else {
                var_dump($value);
            }
        }, func_get_args());
        die(1);
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

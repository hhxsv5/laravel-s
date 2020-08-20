<?php
if (!function_exists('dd')) {
    function dd()
    {
        foreach (func_get_args() as $v) {
            \Symfony\Component\VarDumper\VarDumper::dump($v);
        }

        exit(1);
    }
}

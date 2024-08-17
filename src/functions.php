<?php

if (!function_exists('db') && class_exists('Leaf\App')) {
    /**
     * Return the database object
     *
     * @return \Leaf\Db
     */
    function db()
    {
        if (!(\Leaf\Config::getStatic('db'))) {
            \Leaf\Config::singleton('db', function () {
                return new \Leaf\Db();
            });
        }

        return \Leaf\Config::get('db');
    }
}

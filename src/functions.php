<?php

if (!function_exists('db') && class_exists('Leaf\App')) {
    /**
     * Return the database object
     *
     * @param string|null $connection The connection to return db with
     * @return \Leaf\Db
     */
    function db(?string $connection = null)
    {
        if (!(\Leaf\Config::getStatic('db'))) {
            \Leaf\Config::singleton('db', function () {
                return new \Leaf\Db();
            });
        }

        return (\Leaf\Config::get('db'))->use($connection);
    }
}

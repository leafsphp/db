<?php

if (!function_exists('db') && class_exists('Leaf\App')) {
    /**
     * Return the database object
     * 
     * @return \Leaf\Db
     */
    function db()
    {
        if (!(app()->config('db.instance'))) {
            $db = new \Leaf\Db;
            app()->config('db.instance', $db);
        }

        return app()->config('db.instance');
    }
}

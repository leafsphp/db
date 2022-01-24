<?php

if (!function_exists('db') && class_exists('Leaf\App')) {
    /**
     * Return the database object
     * 
     * @return \Leaf\Db
     */
    function db()
    {
        if (!(\Leaf\Config::get("db.instance"))) {
            \Leaf\Config::set("db.instance", new \Leaf\Db());
        }

        return \Leaf\Config::get("db.instance");
    }
}

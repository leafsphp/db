<?php

namespace Leaf\Db;

/**
 * Leaf Db Utils
 * -------------------------
 * Core utilities for leaf db.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 1.0.0
 */
class Utils
{
    /**
     * Flatten multidimensional array into a single array
     *
     * @param array $array The array to flatten
     * @return bool $keys Use array keys or not
     *
     * @return array
     */
    public static function flatten(array $array, bool $keys = false)
    {
        $parsed = [];

        if ($keys) {
            array_walk_recursive($array, function ($a, $b) use (&$parsed) {
                $parsed[$b] = $a;
            });
        } else {
            array_walk_recursive($array, function ($a) use (&$parsed) {
                $parsed[] = $a;
            });
        }

        return $parsed;
    }

    /**
     * Construct search that begins with a phrase in db
     *
     * @param string $phrase The phrase to check
     *
     * @return string
     */
    public static function beginsWith(string $phrase)
    {
        return "$phrase%";
    }

    /**
     * Construct search that ends with a phrase in db
     *
     * @param string $phrase The phrase to check
     *
     * @return string
     */
    public static function endsWith(string $phrase)
    {
        return "%$phrase";
    }

    /**
     * Construct search that includes a phrase in db
     *
     * @param string $phrase The phrase to check
     *
     * @return string
     */
    public static function includes(string $phrase)
    {
        return "%$phrase%";
    }

    /**
     * Construct search that begins and ends with a phrase in db
     *
     * @param string $beginsWith The beginning of the phrase to search
     * @param string $endsWith The end of the phrase to search
     *
     * @return string
     */
    public static function word(string $beginsWith, string $endsWith): string
    {
        return "$beginsWith%$endsWith";
    }
}

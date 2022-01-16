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
     */
    public static function flatten(array $array, bool $keys = false): array
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
     */
    public static function beginsWith($phrase): string
    {
        return "$phrase%";
    }

    /**
     * Construct search that ends with a phrase in db 
     */
    public static function endsWith($phrase): string
    {
        return "%$phrase";
    }

    /**
     * Construct search that includes a phrase in db 
     */
    public static function includes($phrase): string
    {
        return "%$phrase%";
    }

    /**
     * Construct search that begins and ends with a phrase in db 
     */
    public static function word($beginsWith, $endsWith): string
    {
        return "$beginsWith%$endsWith";
    }
}

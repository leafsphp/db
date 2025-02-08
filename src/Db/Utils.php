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

    /**
     * Pluralizes a given singular noun based on common English rules.
     *
     * @param string $singular The singular noun.
     * @param int    $count    The count determining singular or plural (default is 2).
     *
     * @return string The pluralized form if count is not 1, otherwise the singular.
     */
    public static function basicPluralize(string $singular, int $count = 2): string
    {
        // Return singular if the count is exactly 1
        if ($count === 1) {
            return $singular;
        }

        // Define a list of irregular nouns
        $irregulars = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'mouse' => 'mice',
            'goose' => 'geese',
            'ox' => 'oxen'
        ];

        // Check if the word is irregular (case-insensitive)
        $lowerSingular = strtolower($singular);
        if (isset($irregulars[$lowerSingular])) {
            return $irregulars[$lowerSingular];
        }

        // If the word ends in a consonant followed by 'y', replace 'y' with 'ies'
        if (preg_match('/([^aeiou])y$/i', $singular)) {
            return preg_replace('/y$/i', 'ies', $singular);
        }

        // If the word ends in 's', 'x', 'z', 'ch', or 'sh', append 'es'
        if (preg_match('/(s|x|z|ch|sh)$/i', $singular)) {
            return "{$singular}es";
        }

        // Optionally, handle words ending with 'f' or 'fe' (like "leaf" -> "leaves")
        // Note: This rule is not universal and may need adjustments for exceptions.
        if (preg_match('/(f|fe)$/i', $singular)) {
            return preg_replace('/(f|fe)$/i', 'ves', $singular);
        }

        // Default case: simply append 's'
        return "{$singular}s";
    }

    /**
     * Singularizes a given plural noun based on common English rules.
     *
     * @param string $plural The plural noun.
     *
     * @return string The singular form of the noun.
     */
    public static function basicSingularize(string $plural): string
    {
        // Define a list of irregular plurals
        $irregulars = [
            'children' => 'child',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'mice' => 'mouse',
            'geese' => 'goose',
            'oxen' => 'ox'
        ];

        // Check if the word is an irregular plural (case-insensitive)
        $lowerPlural = strtolower($plural);

        if (isset($irregulars[$lowerPlural])) {
            return $irregulars[$lowerPlural];
        }

        // If a word ends in "ies", it often becomes "y"
        // e.g. "babies" becomes "baby"
        if (preg_match('/(.*[^aeiou])ies$/i', $plural, $matches)) {
            return $matches[1] . 'y';
        }

        // If a word ends in "ves", it might become "f" or "fe"
        // Examples:
        //   "leaves" becomes "leaf"
        //   "wives" becomes "wife"
        if (preg_match('/(.*)ves$/i', $plural, $matches)) {
            // Special case for words like "wives"
            if (preg_match('/wives$/i', $plural)) {
                return preg_replace('/wives$/i', 'wife', $plural);
            }
            return preg_replace('/ves$/i', 'f', $plural);
        }

        // If a word ends in "ches", "shes", "xes", or "zes", remove the "es"
        // e.g. "churches" becomes "church"
        if (preg_match('/(.*)(ches|shes|xes|zes)$/i', $plural)) {
            return preg_replace('/es$/i', '', $plural);
        }

        // Generic rule: if the word ends with "s", remove it.
        if (substr($plural, -1) === 's') {
            return substr($plural, 0, -1);
        }

        // If no rule applies, return the word as-is
        return $plural;
    }
}

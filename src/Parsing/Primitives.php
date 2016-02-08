<?php
/**
 * ParsingHelper.
 *
 * This class uses NO Exception Handling, cause it's not "cheap" to throw and
 * catch an exception and in case of scraping it's not that important to have
 * all files.
 */
namespace Inkrement\ScrapingFramework\Parsing;

class Primitives
{
    private static function int($int)
    {
        if (is_null($int) || empty($int)) {
            return;
        }

        return intval($int);
    }

    public static function parseBool($bool)
    {
        if ($bool === true || $bool === false) {
            return $bool;
        }
        if ('true' === $bool || 'True' === $bool) {
            return true;
        }
        if ('false' === $bool || 'False' === $bool) {
            return false;
        }

        return;
    }
}

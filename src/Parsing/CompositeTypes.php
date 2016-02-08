<?php

namespace Inkrement\ScrapingFramework\Parsing;

class CompositeTypes
{
    public static function date($date)
    {
        if (is_null($date)) {
            return;
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }

    /*
    private static function genStrList($array)
    {
        if (is_null($array) || empty($array) || !is_array($array)) {
            return;
        }

        return implode(',', $array);
    }
    */
}

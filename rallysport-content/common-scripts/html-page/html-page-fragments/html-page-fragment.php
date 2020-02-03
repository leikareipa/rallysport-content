<?php namespace RSC\HTMLPage\Fragment;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Implements the base functionality a HTMLPage fragment should have.
class HTMLPageFragment
{
    public static function css() : string
    {
        return "";
    }

    static public function scripts() : array
    {
        return [""];
    }
}

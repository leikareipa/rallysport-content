<?php namespace RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// A reusable element or set of elements an on a HTMLPage.
abstract class HTMLPageComponent
{
    // Returns as a string any CSS styling this component makes use of.
    public static function css() : string
    {
        return "";
    }
    
    // Returns as an array of strings the individual JavaScript scripts this
    // component makes use of. Each string will be embedded in <script></script>
    // tags when inserted into the HTMLPage.
    static public function scripts() : array
    {
        return [""];
    }
}

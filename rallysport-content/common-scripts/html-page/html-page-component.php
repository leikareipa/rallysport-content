<?php namespace RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Represents an abstract element or set of elements on a HTMLPage. You would
// use fragments to build reusable components, for instance.
abstract class HTMLPageComponent
{
    // Returns as a string any CSS styling this fragment makes use of.
    public static function css() : string
    {
        return "";
    }
    
    // Returns as an array of strings the individual JavaScript scripts this
    // fragment makes use of. Each string will be embedded in <script></script>
    // tags when inserted into the HTMLPage.
    static public function scripts() : array
    {
        return [""];
    }
}

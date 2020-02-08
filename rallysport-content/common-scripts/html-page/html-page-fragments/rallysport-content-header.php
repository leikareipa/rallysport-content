<?php namespace RSC\HTMLPage\Fragment;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-fragment.php";
require_once __DIR__."/../../resource/resource-visibility.php";

// A basic header element intended to be displayed on Rally-Sport Content's
// HTML pages.
abstract class RallySportContentHeader extends HTMLPage\HTMLPageFragment
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-header.css");
    }

    static public function html()
    {
        return "
        <header id='rallysport-content-header'>
            Rally-Sport Content
        </header>
        ";
    }
}

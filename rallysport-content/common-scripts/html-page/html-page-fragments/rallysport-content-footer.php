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

// A basic footer element intended to be displayed on Rally-Sport Content's
// HTML pages.
class RallySportContentFooter extends HTMLPage\HTMLPageFragment
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-footer.css");
    }

    static public function html()
    {
        return "
        <footer id='rallysport-content-footer'>
            Rally-Sport Content &copy; 2020 Tarpeeksi Hyvae Soft.
            <a href='https://www.github.com/leikareipa/rallysport-content/'>See on GitHub.</a>
        </footer>
        ";
    }
}

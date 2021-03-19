<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-visibility.php";

// A basic footer element intended to be displayed on Rally-Sport Content's
// HTML pages.
abstract class RallySportContentFooter extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-footer.css");
    }

    static public function html() : string
    {
        return "
        <footer id='rallysport-content-footer'>

            <span class='first'>

                <a href='/rallysport-content/'>Rally-Sport Content</a>
                by <a href='https://www.tarpeeksihyvaesoft.com/'>Tarpeeksi Hyvae Soft</a>.

                <a href='https://www.github.com/leikareipa/rallysport-content/'>Find this on GitHub.</a>

            </span>

        </footer>
        ";
    }
}

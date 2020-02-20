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
abstract class RallySportContentNavibar extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-navibar.css");
    }

    static public function html()
    {
        return "
        <div id='rallysport-content-navibar'>
            <a href='/rallysport-content/' title='Home'>
                <i class='fas fa-fw fa-home'></i>
            </a>

            <a href='/rallysport-content/tracks/' title='Tracks'>
                <i class='fas fa-fw fa-road'></i>
            </a>

            <a href='/rallysport-content/users/' title='Users'>
                <i class='fas fa-fw fa-users'></i>
            </a>
        </div>
        ";
    }
}

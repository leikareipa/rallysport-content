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

// A navigation bar intended to be displayed on each Rally-Sport Content page.
// It provides the user with clickable buttons to navigate to HTML views of the
// various resources hosted on RSC, like tracks and users.
abstract class RallySportContentNavibar extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-navibar.css");
    }

    static public function html() : string
    {
        // Figure out which page we're on.
        /// TODO: Don't hard-code URLs.
        if (strpos($_SERVER["REQUEST_URI"], "/rallysport-content/tracks") !== FALSE)
        {
            $currentPage = "tracks";
        }
        else if (strpos($_SERVER["REQUEST_URI"], "/rallysport-content/users") !== FALSE)
        {
            $currentPage = "users";
        }
        else
        {
            $currentPage = "home";
        }

        return "
        <div id='rallysport-content-navibar'>
            <a href='/rallysport-content/' title='Home'
               class='button ".(($currentPage == "home")? "current-page" : "")."'>
                <i class='fas fa-fw fa-home'></i>
            </a>

            <a href='/rallysport-content/tracks/' title='Tracks'
               class='button ".(($currentPage == "tracks")? "current-page" : "")."'>
                <i class='fas fa-fw fa-road'></i>
            </a>

            <a href='/rallysport-content/users/' title='Users'
               class='button ".(($currentPage == "users")? "current-page" : "")."'>
                <i class='fas fa-fw fa-users'></i>
            </a>
        </div>
        ";
    }
}
<?php namespace RSC\HTMLPage\Fragment;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-visibility.php";
require_once __DIR__."/../../resource/resource-id.php";

// A basic header element intended to be displayed on Rally-Sport Content's
// HTML pages.
abstract class RallySportContentHeader extends HTMLPage\HTMLPageComponent
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

            ".(isset($_SESSION["user_resource_id"])
            ?
            "<div id='header-login-container'>
                <span><i class='fas fa-fw fa-sm fa-user'></i><b>{$_SESSION["user_resource_id"]}</b></span>
                <span class='separator'>|</span>
                <a href='/rallysport-content/logout.php'>Log out</a>
            </div>"
            :
            "<div id='header-login-container'>
                <a href='/rallysport-content/?form=login'>Log in</a>
                <span class='separator'>|</span>
                <a href='/rallysport-content/users/?form=add'>Register</a>
            </div>"
            )."

        </header>
        ";
    }
}

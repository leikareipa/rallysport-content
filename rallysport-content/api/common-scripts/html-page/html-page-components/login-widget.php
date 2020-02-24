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
require_once __DIR__."/../../resource/resource-id.php";

// Displays a widget with two possible states:
//
//   State 1: If the user is currently logged in, the widget displays the
//            logged-in username, and a link with which the user can log out.
//
//   State 2: If the user is not currently logged in, the widget displays
//            links with which the user can log in or register a new account.
//
abstract class LoginWidget extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/login-widget.css");
    }

    static public function html() : string
    {
        return "
        <div class='login-widget'>

            ".(isset($_SESSION["user_resource_id"])
            ?
            "<span><i class='far fa-fw fa-sm fa-user'></i>{$_SESSION["user_resource_id"]}</span>
             <span class='separator'>|</span>
             <a href='/rallysport-content/logout.php'>Log out</a>"
            :
            "<a href='/rallysport-content/?form=login'>Log in</a>
             <span class='separator'>|</span>
             <a href='/rallysport-content/users/?form=add'>Register</a>"
            )."

        </div>
        ";
    }
}

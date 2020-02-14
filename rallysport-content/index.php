<?php namespace RSC;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs requests arriving at Rally-Sport Content's root to the REST API.
 * 
 */

require_once __DIR__."/server-api/form-dispatch/dispatch-form.php";
require_once __DIR__."/server-api/response.php";
require_once __DIR__."/server-api/root/view-control-panel.php";
require_once __DIR__."/server-api/session.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        switch ($_GET["form"] ?? "unknown-form-identifier")
        {
            case "login": API\dispatch_form(API\Form\UserLogin::class); break;
            default:
            {
                if (!API\Session\is_current_user_logged_in())
                {
                    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login"));
                }
                else
                {
                    API\Root\view_control_panel();
                }
                
                break;
            }
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

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

require_once __DIR__."/api/page-dispatch/pages/form/form.php";
require_once __DIR__."/api/page-dispatch/pages/root/control-panel.php";
require_once __DIR__."/api/response.php";
require_once __DIR__."/api/session.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        switch ($_GET["form"] ?? "unknown-form-identifier")
        {
            case "login": API\PageDisplay\form(API\Form\UserLogin::class); break;
            default:
            {
                if (!API\Session\is_client_logged_in())
                {
                    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login"));
                }
                else
                {
                    API\PageDisplay\Root\control_panel();
                }
                
                break;
            }
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

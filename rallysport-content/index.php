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

require_once __DIR__."/api/page-dispatch/pages/form/forms/user-login.php";
require_once __DIR__."/api/page-dispatch/pages/form/forms/request-password-reset.php";
require_once __DIR__."/api/page-dispatch/pages/form/forms/password-reset-request-success.php";
require_once __DIR__."/api/page-dispatch/pages/form/forms/password-reset-success.php";
require_once __DIR__."/api/page-dispatch/pages/form/forms/reset-password.php";
require_once __DIR__."/api/page-dispatch/pages/form/forms/unknown-form-identifier.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        if (isset($_GET["form"]))
        {
            switch ($_GET["form"] ?? "unknown-form-identifier")
            {
                case "login":                           API\PageDisplay\form(API\Form\UserLogin::class); break;
                case "request-password-reset":          API\PageDisplay\form(API\Form\RequestPasswordReset::class); break;
                case "password-reset-request-success":  API\PageDisplay\form(API\Form\PasswordResetRequestSuccess::class); break;
                case "reset-password":                  API\PageDisplay\form(API\Form\ResetPassword::class); break;
                case "password-reset-success":          API\PageDisplay\form(API\Form\PasswordResetSuccess::class); break;
                default:                                API\PageDisplay\form(API\Form\UnknownFormIdentifier::class); break;
            }
        }
        else
        {
            if (!API\Session\is_client_logged_in())
            {
                exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login"));
            }
            else
            {
                API\PageDisplay\Root\control_panel();
            }
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

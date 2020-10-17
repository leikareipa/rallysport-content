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
require_once __DIR__."/api/page-dispatch/pages/form/forms/password-reset-request-done.php";
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
                case "login":
                {
                    $page = API\BuildPage\form(API\Form\UserLogin::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
                case "request-password-reset":
                {
                    $page = API\BuildPage\form(API\Form\RequestPasswordReset::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
                case "password-reset-request-done":
                {
                    $page = API\BuildPage\form(API\Form\PasswordResetRequestDone::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
                case "reset-password":
                {
                    $page = API\BuildPage\form(API\Form\ResetPassword::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
                case "password-reset-success":
                {
                    $page = API\BuildPage\form(API\Form\PasswordResetSuccess::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
                default:
                {
                    $page = API\BuildPage\form(API\Form\UnknownFormIdentifier::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
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
                $page = API\BuildPage\Root\control_panel();
                exit(API\Response::code(200)->html($page->html()));
            }
        }

        break;
    }
    default:
    {
        exit(API\Response::code(405)->allowed("GET, HEAD"));
    }
}

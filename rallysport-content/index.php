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

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        switch ($_GET["form"] ?? "unknown-form-identifier")
        {
            case "login": API\dispatch_form(API\Form\UserLogin::class); break;
            default:      API\dispatch_form(API\Form\UnknownFormIdentifier::class); break;
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

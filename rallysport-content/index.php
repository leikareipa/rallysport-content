<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs requests arriving at Rally-Sport Content's root to the REST API.
 * 
 */

require_once __DIR__."/server-api/view-form-page.php";
require_once __DIR__."/common-scripts/response.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        API\view_form_page($_GET["form"] ?? "unknown_form_identifier");

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

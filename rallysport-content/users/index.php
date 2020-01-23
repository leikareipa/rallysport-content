<?php

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs USER-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/server-api/create-new-user.php";
require_once __DIR__."/server-api/printout-user-information.php";
require_once __DIR__."/../common-scripts/return.php";
require_once __DIR__."/../common-scripts/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "GET":
    {
        $resourceID = (isset($_GET["id"])? (new RallySportContent\UserResourceID($_GET["id"])) : NULL);

        // Output as JSON.
        if (isset($_GET["json"]) && $_GET["json"])
        {
            RallySportContent\printout_user_information($resourceID);
        }
        // Output as a view.
        else
        {
            ///RallySportContent\view_user($resourceID);
        }

        break;
    }

    case "POST":
    {
        RallySportContent\create_new_user(json_decode(file_get_contents("php://input"), true));

    break;
    }

    case "PUT":
    {
        ///RallySportContent\update_user(json_decode(file_get_contents("php://input"), true));

        break;
    }

    default: exit(RallySportContent\ReturnObject::script_failed("Unknown request: {$_SERVER["REQUEST_METHOD"]}"));
}

<?php

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs TRACK-related network requests to the server's REST API.
 * 
 */

require_once dirname(__FILE__)."/server-api/add-new-track.php";
require_once dirname(__FILE__)."/server-api/printout-track-information.php";
require_once dirname(__FILE__)."/../common-scripts/return.php";
require_once dirname(__FILE__)."/../common-scripts/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "GET":
    {
        $resourceID = (isset($_GET["id"])? (new RallySportContent\ResourceID("track", $_GET["id"])) : NULL);

        // Output as JSON.
        if (isset($_GET["json"]) && $_GET["json"])
        {
            RallySportContent\printout_track_information($resourceID);
        }
        // Output as a view.
        else
        {
            ///RallySportContent\view_track($resourceID);
        }

        break;
    }

    case "POST":
    {
        RallySportContent\add_new_track(json_decode(file_get_contents("php://input"), true));

    break;
    }

    case "PUT":
    {
        ///RallySportContent\update_track(json_decode(file_get_contents("php://input"), true));

        break;
    }

    default: exit(RallySportContent\ReturnObject::script_failed("Unknown request: {$_SERVER["REQUEST_METHOD"]}"));
}

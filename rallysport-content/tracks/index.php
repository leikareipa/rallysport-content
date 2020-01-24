<?php

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs TRACK-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/server-api/add-new-track.php";
require_once __DIR__."/server-api/serve-track-data.php";
require_once __DIR__."/../common-scripts/return.php";
require_once __DIR__."/../common-scripts/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "GET":
    {
        $resourceID = (isset($_GET["id"])? (new RallySportContent\TrackResourceID($_GET["id"])) : NULL);

        if ($_GET["zip"] ?? false)
        {
            RallySportContent\serve_track_data_as_zip_file($resourceID);
        }
        else if ($_GET["json"] ?? false)
        {
            RallySportContent\serve_track_data_as_json($resourceID);
        }
        else if ($_GET["metadata"] ?? false)
        {
            RallySportContent\serve_track_metadata_as_json($resourceID);
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

    case "DELETE";
    {
        /// TODO.
        
        break;
    }

    default: exit(RallySportContent\ReturnObject::script_failed("Unknown request: {$_SERVER["REQUEST_METHOD"]}"));
}

<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs TRACK-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/server-api/add-new-track.php";
require_once __DIR__."/server-api/view-track-data.php";
require_once __DIR__."/server-api/serve-track-data.php";
require_once __DIR__."/../common-scripts/response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        // Find which track we're requested to operate on. If no track ID is
        // provided, we assume the query relates to all tracks in the database.
        if ($_GET["id"] ?? false)
        {
            $resourceID = ResourceID::from_string($_GET["id"], ResourceType::TRACK);
            
            if (!$resourceID)
            {
                exit(API\Response::code(400)->error_message("Invalid track resource ID."));
            }
        }
        else
        {
            $resourceID = NULL;
        }

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["zip"] ?? false)
        {
            API\serve_track_data_as_zip_file($resourceID);
        }
        else if ($_GET["json"] ?? false)
        {
            API\serve_track_data_as_json($resourceID);
        }
        else if ($_GET["metadata"] ?? false)
        {
            API\serve_track_metadata_as_json($resourceID);
        }
        else
        {
            API\view_track_metadata($resourceID);
        }

        break;
    }
    case "POST":
    {
        API\add_new_track(json_decode(file_get_contents("php://input"), true));

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD, POST"));
}

<?php namespace RSC;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs TRACK-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/../server-api/tracks/add-new-track.php";
require_once __DIR__."/../server-api/tracks/view-form.php";
require_once __DIR__."/../server-api/tracks/view-track.php";
require_once __DIR__."/../server-api/tracks/serve-track-data.php";
require_once __DIR__."/../common-scripts/response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        $resourceID = NULL;

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

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["form"] ?? false)          API\Tracks\view_form($_GET["form"] ?? "unknown_form_identifier");
        else if ($_GET["zip"] ?? false)      API\Tracks\serve_track_data_as_zip_file($resourceID);
        else if ($_GET["json"] ?? false)     API\Tracks\serve_track_data_as_json($resourceID);
        else if ($_GET["metadata"] ?? false) API\Tracks\serve_track_metadata_as_json($resourceID);
        else                                 API\Tracks\view_track_metadata($resourceID);

        break;
    }
    case "POST":
    {
        API\Tracks\add_new_track(json_decode(file_get_contents("php://input"), true));

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD, POST"));
}

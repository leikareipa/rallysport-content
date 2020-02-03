<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs USER-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/server-api/create-new-user.php";
require_once __DIR__."/server-api/serve-user-data.php";
require_once __DIR__."/../common-scripts/response.php";
require_once __DIR__."/../common-scripts/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "GET":
    {
        // Find which user we're requested to operate on. If no user ID is
        // provided, we assume the query relates to all users in the database.
        if ($_GET["id"] ?? false)
        {
            $resourceID = ResourceID::from_string($_GET["id"], ResourceType::USER);
            if (!$resourceID)
            {
                echo $_GET["id"];
                exit(API\Response::code(400)->error_message("Invalid user resource ID."));
            }
        }
        else
        {
            $resourceID = NULL;
        }

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["metadata"] ?? false)
        {
            API\serve_user_metadata_as_json($resourceID);
        }
        else
        {
            ///RallySportContent\view_user($resourceID);
        }

        break;
    }

    case "POST":
    {
        API\create_new_user(json_decode(file_get_contents("php://input"), true));

        break;
    }

    case "PUT":
    {
        ///RallySportContent\update_user(json_decode(file_get_contents("php://input"), true));

        break;
    }

    case "DELETE";
    {
        /// TODO.

        break;
    }

    default: exit(API\Response::code(400)->error_message("Unknown request: {$_SERVER["REQUEST_METHOD"]}"));
}

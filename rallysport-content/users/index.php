<?php namespace RSC;

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
            ///RSC\view_user($resourceID);
        }

        break;
    }
    case "HEAD":
    {
        /// TODO.

        break;
    }
    case "POST":
    {
        API\create_new_user(json_decode(file_get_contents("php://input"), true));

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD, POST"));
}

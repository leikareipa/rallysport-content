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
require_once __DIR__."/server-api/serve-user-data.php";
require_once __DIR__."/../common-scripts/return.php";
require_once __DIR__."/../common-scripts/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "GET":
    {
        // Find which user we're requested to operate on. If no user ID is
        // provided, we assume the query relates to all users in the database.
        if ($_GET["id"] ?? false)
        {
            $resourceID = RallySportContent\ResourceID::from_string($_GET["id"], RallySportContent\ResourceType::USER);
            if (!$resourceID)
            {
                echo $_GET["id"];
                exit(RallySportContent\ReturnObject::script_failed("Invalid user resource ID."));
            }
        }
        else
        {
            $resourceID = NULL;
        }

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["metadata"] ?? false)
        {
            RallySportContent\serve_user_metadata_as_json($resourceID);
        }
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

    case "DELETE";
    {
        /// TODO.

        break;
    }

    default: exit(RallySportContent\ReturnObject::script_failed("Unknown request: {$_SERVER["REQUEST_METHOD"]}"));
}

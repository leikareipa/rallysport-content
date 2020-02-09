<?php namespace RSC;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs USER-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/../server-api/users/create-new-user.php";
require_once __DIR__."/../server-api/users/serve-user-data.php";
require_once __DIR__."/../server-api/users/view-form.php";
require_once __DIR__."/../common-scripts/response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        $resourceID = NULL;

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

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["form"] ?? false)          API\Users\view_form($_GET["form"] ?? "unknown_form_identifier");
        else if ($_GET["metadata"] ?? false) API\Users\serve_user_metadata_as_json($resourceID);

        break;
    }
    case "POST": // Create a new user account.
    {
        if (isset($_SESSION["user_resource_id"]))
        {
            exit(API\Response::code(303)->redirect_to("/rallysport-content/users/?form=add&error=You are already logged in as a user"));
        }
        
        if (!isset($_POST["email"]) ||
            !isset($_POST["password"]))
        {
            exit(API\Response::code(303)->redirect_to("/rallysport-content/users/?form=add&error=Missing email or password"));
        }

        API\Users\create_new_user($_POST["email"], $_POST["password"]);

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD, POST"));
}

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
require_once __DIR__."/../server-api/users/view-user.php";
require_once __DIR__."/../server-api/form-dispatch/dispatch-form.php";
require_once __DIR__."/../server-api/response.php";
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
            $resourceID = Resource\UserResourceID::from_string($_GET["id"]);
            
            if (!$resourceID)
            {
                exit(API\Response::code(400)->error_message("Invalid user resource ID."));
            }
        }

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["form"] ?? false)
        {
            switch ($_GET["form"])
            {
                case "add":                 API\dispatch_form(API\Form\CreateUserAccount::class); break;
                case "new-account-created": API\dispatch_form(API\Form\NewUserAccountCreated::class); break;
                default:                    API\dispatch_form(API\Form\UnknownFormIdentifier::class); break;
            }
        }
        else if ($_GET["metadata"] ?? false) API\Users\serve_user_data_as_json("metadata-array", $resourceID);
        else                                 API\Users\view_user_metadata($resourceID);

        break;
    }
    case "POST": // Create a new user account.
    {
        // NOTE: Creating user accounts is disabled for now.
        exit(API\Response::code(404)->error_message("Registration is temporarily unavailable."));




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

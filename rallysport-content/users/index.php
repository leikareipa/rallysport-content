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

require_once __DIR__."/../api/page-dispatch/pages/form/form.php";
require_once __DIR__."/../api/page-dispatch/pages/users/all-public-users.php";
require_once __DIR__."/../api/page-dispatch/pages/users/specific-public-user.php";
require_once __DIR__."/../api/users/create-new-user.php";
require_once __DIR__."/../api/users/serve-user-data.php";
require_once __DIR__."/../api/response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["form"] ?? false)
        {
            switch ($_GET["form"])
            {
                case "add":                 API\Page\form(API\Form\CreateUserAccount::class); break;
                case "new-account-created": API\Page\form(API\Form\NewUserAccountCreated::class); break;
                default:                    API\Page\form(API\Form\UnknownFormIdentifier::class); break;
            }
        }
        else if ($_GET["metadata"] ?? false) API\Users\serve_user_data_as_json("metadata-array", Resource\UserResourceID::from_string($_GET["id"]));
        else // Provide a HTML view into the user data.
        {
            if (isset($_GET["id"])) API\Page\Users\specific_public_user(Resource\UserResourceID::from_string($_GET["id"]));
            else                    API\Page\Users\all_public_users();
        }

        break;
    }
    case "POST": // Create a new user account.
    {
        // NOTE: Creating user accounts is temporarily disabled.
        exit(API\Response::code(404)->error_message("Registration is temporarily unavailable."));
        //////////////////////
        //////////////////////



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

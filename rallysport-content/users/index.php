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
require_once __DIR__."/../api/user-actions/create-new-user.php";
require_once __DIR__."/../api/user-actions/serve-user-data.php";
require_once __DIR__."/../api/response.php";
require_once __DIR__."/../api/session.php";
require_once __DIR__."/../api/common-scripts/resource/resource-id.php";
require_once __DIR__."/../api/common-scripts/resource/resource-view-url-params.php";

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
                case "add":
                {
                    // We don't allow logged-in users to register a new account,
                    // so let's not even show the form for doing so.
                    if (API\Session\is_client_logged_in())
                    {
                        exit(API\Response::code(303)->redirect_to("/rallysport-content/"));
                    }
                    else
                    {
                        API\PageDisplay\form(API\Form\CreateUserAccount::class);
                    }
                
                    break;
                }
                case "new-account-created": API\PageDisplay\form(API\Form\NewUserAccountCreated::class); break;
                default:                    API\PageDisplay\form(API\Form\UnknownFormIdentifier::class); break;
            }
        }
        else if ($_GET["metadata"] ?? false)
        {
            API\Users\serve_user_data_as_json("metadata-array", Resource\UserResourceID::from_string(Resource\ResourceViewURLParams::target_id()));
        }
        else // Provide a HTML view into the user data.
        {
            if (Resource\ResourceViewURLParams::target_id())
            {
                API\PageDisplay\Users\specific_public_user(Resource\UserResourceID::from_string(Resource\ResourceViewURLParams::target_id()));
            }
            else
            {
                API\PageDisplay\Users\all_public_users();
            }
        }

        break;
    }
    case "POST": // Create a new user account.
    {
        if (API\Session\is_client_logged_in())
        {
            exit(API\Response::code(303)->redirect_to("/rallysport-content/users/?form=add&error=You are already logged in as a user"));
        }
        
        if (!isset($_POST["email"]) ||
            !isset($_POST["password"]))
        {
            exit(API\Response::code(303)->redirect_to("/rallysport-content/users/?form=add&error=Missing email or password"));
        }

        if (!isset($_FILES["rallysported_track_file"]))
        {
            exit(API\Response::code(303)->redirect_to("/rallysport-content/users/?form=add&error=Missing the track ZIP file"));
        }

        API\Users\create_new_user($_POST["email"], $_POST["password"], $_FILES["rallysported_track_file"]);

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD, POST"));
}

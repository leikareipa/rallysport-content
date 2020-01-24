<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to create and populate a new entry in the database's
 * table of users.
 * 
 * Returns: JSON {succeeded: bool [, account: object[, errorMessage: string]]}
 * 
 *  - On failure (that is, when 'succeeded' == false), 'errorMessage' will
 *    provide a brief description of the error. The 'account' object will
 *    not be returned.
 * 
 *  - On success (when 'succeeded' == TRUE), the 'account' object will provide
 *    information about the new account. Currently, the following info is
 *    included:
 * 
 *      {
 *          // The new user's resource id, without the resource type; e.g.
 *          // "xxx-xxx-xxx". The full resource id, including the resource
 *          // type, would be "user:xxx-xxx-xxx", but this won't be needed
 *          // in client-to-server interaction.
 *          id: string
 *      }
 * 
 */

require_once __DIR__."/../../common-scripts/return.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/database.php";

// Attempts to add to the Rally-Sport Content database a new user, whose
// password is specified by the function call parameters.
//
// Note: This function should always return using exit() with either
// ReturnObject::script_failed() or ReturnObject::script_succeeded().
//
function create_new_user(array $parameters)
{
    if (!isset($parameters["password"])) exit(ReturnObject::script_failed("Missing the 'password' parameter."));
    if (!isset($parameters["email"])) exit(ReturnObject::script_failed("Missing the 'email' parameter."));

    /// TODO: Make sure the password and email are of the appropriate length, etc.

    $userID = NULL;

    // Add the new user into the database.
    {
        $database = new DatabaseAccess();
        if (!$database->connect())
        {
            exit(ReturnObject::script_failed("Could not connect to the database."));
        }

        $userID = new UserResourceID();
        if (!$database->create_new_user($userID, $parameters["password"], $parameters["email"]))
        {
            exit(ReturnObject::script_failed("Could not create a new user."));
        }
    }

    if ($userID)
    {
        exit(ReturnObject::script_succeeded(["id"=>$userID->resource_key()], "account"));
    }
    else
    {
        exit(ReturnObject::script_failed("Could not create a new user."));
    }
}

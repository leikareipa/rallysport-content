<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * user or a set of users to the client.
 * 
 */

require_once __DIR__."/../../common-scripts/return.php";
require_once __DIR__."/../../common-scripts/database.php";
require_once __DIR__."/../../common-scripts/resource-id.php";

// Prints into the PHP output stream a stringified JSON object containing
// public information about the given user, or of all users in the database if
// the user resource ID is NULL.
//
// Note: This function should always return using exit() with either
// ReturnObject::script_failed() or ReturnObject::script_succeeded().
//
// Returns: JSON {succeeded: bool [, users: object[, errorMessage: string]]}
// 
//  - On failure (that is, when 'succeeded' == false), 'errorMessage' will
//    provide a brief description of the error. No user data will be returned
//    in this case.
// 
//  - On success (when 'succeeded' == true), the 'users' object will contain
//    information about the users queried. The 'errorMessage' string will
//    not be included.
//
function serve_user_metadata_as_json(UserResourceID $resourceID = NULL)
{
    $database = new DatabaseAccess();
    if (!$database->connect())
    {
        exit(ReturnObject::script_failed("Could not connect to the database."));
    }

    $userInfo = $database->get_user_information($resourceID);
    if (!is_array($userInfo) || !count($userInfo))
    {
        exit(ReturnObject::script_failed("No matching users found."));
    }

    exit(ReturnObject::script_succeeded($userInfo, "users"));
}
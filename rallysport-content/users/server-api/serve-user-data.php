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

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/user-database-connection.php";

// Prints into the PHP output stream a stringified JSON object containing
// public information about the given user, or of all users in the database if
// the user resource ID is NULL.
//
// Note: This function should always return using exit() with either
// Response::script_failed() or Response::script_succeeded().
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
function serve_user_metadata_as_json(ResourceID $resourceID = NULL)
{
    $userInfo = (new UserDatabaseConnection())->get_user_information($resourceID);
    if (!$userInfo || !is_array($userInfo) || !count($userInfo))
    {
        exit(Response::script_failed("No matching users found."));
    }

    exit(Response::script_succeeded($userInfo, "users"));
}

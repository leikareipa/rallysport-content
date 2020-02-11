<?php namespace RSC\API\Users;
      use RSC\DatabaseConnection;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * user or a set of users to the client.
 * 
 */

require_once __DIR__."/../../server-api/response.php";
require_once __DIR__."/../../common-scripts/resource/resource-id.php";
require_once __DIR__."/../../common-scripts/database-connection/user-database.php";

// Prints into the PHP output stream a stringified JSON object containing
// public information about the given user, or of all users in the database if
// the user resource ID is NULL.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On failure, the response body will be a JSON string whose 'errorMessage'
//    attribute provides a brief description of the error.
//
//  - On success, the response body will be a JSON string that provides
//    information about the user(s) queried.
//
function serve_user_metadata_as_json(\RSC\ResourceID $resourceID = NULL) : void
{
    $userInfo = (new DatabaseConnection\UserDatabase())->get_user_metadata($resourceID);
    if (!$userInfo || !is_array($userInfo) || !count($userInfo))
    {
        exit(API\Response::code(404)->error_message("No matching user data found."));
    }

    exit(API\Response::code(200)->json($userInfo));
}
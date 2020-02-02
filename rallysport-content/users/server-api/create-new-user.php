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

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/user-database-connection.php";

// Attempts to add to the Rally-Sport Content database a new user, whose
// password is specified by the function call parameters.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->json([...]).
//
function create_new_user(array $parameters)
{
    if (!isset($parameters["password"])) exit(Response::code(400)->error_message("Missing the 'password' parameter."));
    if (!isset($parameters["email"]))    exit(Response::code(400)->error_message("Missing the 'email' parameter."));

    /// TODO: Make sure the password and email are of the appropriate length, etc.

    $userResourceID = ResourceID::random(ResourceType::USER);

    if (!$userResourceID ||
        !(new UserDatabaseConnection())->create_new_user($userResourceID, $parameters["password"], $parameters["email"]))
    {
        exit(Response::code(500)->error_message("Could not create a new user."));
    }

    exit(Response::code(201)->json(["id"=>$userResourceID->string()]));
}

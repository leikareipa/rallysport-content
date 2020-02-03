<?php namespace RSC\API;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to create and populate a new entry in the database's
 * table of users.
 * 
 */

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/database-connection/user-database.php";

// Attempts to add to the Rally-Sport Content database a new user, whose
// password is specified by the function call parameters.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->json([...]).
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On failure, the response body will be a JSON string whose 'errorMessage'
//    attribute provides a brief description of the error.
//
//  - On success, returns the HTML status code 201 and a body as a JSON string
//    that provides information about the newly-created user account.
//
function create_new_user(array $parameters)
{
    if (!isset($parameters["password"])) exit(Response::code(400)->error_message("Missing the 'password' parameter."));
    if (!isset($parameters["email"]))    exit(Response::code(400)->error_message("Missing the 'email' parameter."));

    /// TODO: Make sure the password and email are of the appropriate length, etc.

    $userResourceID = \RSC\ResourceID::random(\RSC\ResourceType::USER);

    if (!$userResourceID ||
        !(new DatabaseConnection\UserDatabase())->create_new_user($userResourceID, $parameters["password"], $parameters["email"]))
    {
        exit(Response::code(500)->error_message("Could not create a new user."));
    }

    exit(Response::code(201)->json(["id"=>$userResourceID->string()]));
}

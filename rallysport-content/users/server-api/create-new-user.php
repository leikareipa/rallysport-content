<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to create and populate a new entry in the database's
 * table of users.
 * 
 * Returns: JSON {succeeded: bool [, errorMessage: string]}
 * 
 *  - On failure (that is, when succeeded == false), 'errorMessage' will provide
 *    a brief description of the error.
 * 
 *  - On success, only the 'succeeded' parameter (set to true) is returned.
 * 
 */

require_once __DIR__."/../../common-scripts/return.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/database.php";

// Attempts to add to the Rally-Sport Content database a new user, whose
// username and password are specified by the function call parameters.
//
// Note:
//
//  - The 'username' and 'password' parameters are AT THE MOMENT not required
//    to be unique in the database; users are instead uniquely identified by
//    a unique user resource ID (e.g. "user+t8r-uwb-u22"). The resource ID is
//    automatically generated by this function.
//
//  - The function should not return. Instead, it should exit() with either
//    ReturnObject::script_succeeded() or ReturnObject::script_failed().
//
function create_new_user(array $parameters)
{
    // Validate input parameters.
    {
        if (!isset($parameters["username"])) exit(ReturnObject::script_failed("Missing the 'username' parameter."));
        if (!isset($parameters["password"])) exit(ReturnObject::script_failed("Missing the 'password' parameter."));

        /// TODO: Make sure the password and username are of the appropriate length,
        /// etc.
    }

    // Add the new user into the database.
    {
        $database = new DatabaseAccess();
        $resourceID = new UserResourceID();

        if (!$database->connect())
        {
            exit(ReturnObject::script_failed("Could not connect to the database."));
        }

        /// TODO: Test to make sure the resource ID is unique in the TRACKS table.

        if (!$database->create_new_user($resourceID, $parameters["username"], $parameters["password"]))
        {
            exit(ReturnObject::script_failed("Could not create a new user."));
        }
    }

    exit(ReturnObject::script_succeeded());
}

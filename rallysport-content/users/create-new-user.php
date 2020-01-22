<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script creates and populates a new entry in the database's table of
 * users, with the username and password provided in the request body.
 * 
 * Expected parameters: [username, password]
 * 
 * Returns: JSON {succeeded: bool [, errorMessage: string]}
 * 
 *  - On failure (that is, when succeeded == false), 'errorMessage' will provide
 *    a brief description of the error.
 * 
 */

require_once "../common-scripts/return.php";
require_once "../common-scripts/resource-id.php";
require_once "../common-scripts/database.php";

function create_new_user($parameters)
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
        $resourceID = new ResourceID("user");

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

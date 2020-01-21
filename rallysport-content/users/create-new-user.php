<?php

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script creates and populates a new entry in the database's table of
 * users, with the username and password provided in the request body.
 * 
 * Expected request body: JSON {username, password}
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

// Validate input parameters in the request body.
$requestBody = json_decode(file_get_contents("php://input"), true);
{
    if (!isset($requestBody["username"]))
    {
        exit(RSC\ReturnObject::script_failed("A username must be provided when creating a new user."));
    }

    if (!isset($requestBody["password"]))
    {
        exit(RSC\ReturnObject::script_failed("A password must be provided when creating a new user."));
    }

    /// TODO: Make sure the password and username are of the appropriate length,
    /// etc.
}

// Add the new user into the database.
{
    $database = new RSC\DatabaseAccess();
    $resourceID = new RSC\ResourceID("user");

    if (!$database->connect())
    {
        exit(RSC\ReturnObject::script_failed("Could not connect to the database."));
    }

    if (!$database->create_new_user($resourceID, $requestBody["username"], $requestBody["password"]))
    {
        exit(RSC\ReturnObject::script_failed("Could not create a new user."));
    }
}

exit(RSC\ReturnObject::script_succeeded());

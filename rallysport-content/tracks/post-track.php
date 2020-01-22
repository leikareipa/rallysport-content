<?php

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script adds a new public track into RSC's database.
 * 
 * Expected POST request body:
 *  JSON {
 *      internalName,
 *      displayName,
 *      width,
 *      height,
 *      containerData,
 *      manifestoData,
 *  }
 * 
 *  - Strings are expected in UTF-8.
 * 
 *  - For more information about the request body parameters, see the documentation
 *    in RallySportED-js's repo, https://github.com/leikareipa/rallysported-js.
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
    if (!isset($requestBody["internalName"]))  exit(RSC\ReturnObject::script_failed("Missing the 'internalName' parameter in the request body."));
    if (!isset($requestBody["displayName"]))   exit(RSC\ReturnObject::script_failed("Missing the 'displayName' parameter in the request body."));
    if (!isset($requestBody["width"]))         exit(RSC\ReturnObject::script_failed("Missing the 'width' parameter in the request body."));
    if (!isset($requestBody["height"]))        exit(RSC\ReturnObject::script_failed("Missing the 'height' parameter in the request body."));
    if (!isset($requestBody["containerData"])) exit(RSC\ReturnObject::script_failed("Missing the 'containerData' parameter in the request body."));
    if (!isset($requestBody["manifestoData"])) exit(RSC\ReturnObject::script_failed("Missing the 'manifestoData' parameter in the request body."));

    // Validate track dimensions.
    {
        if ($requestBody["width"] != $requestBody["height"])
        {
            exit(RSC\ReturnObject::script_failed("Track dimensions must be square."));
        }

        if (($requestBody["width"] != 64) &&
            ($requestBody["height"] != 128))
        {
            exit(RSC\ReturnObject::script_failed("Unsupported track dimensions ."));
        }
    }

    // Validate names.
    {
        // Internal track names are allowed to consist of 1-8 ASCII alphabet characters.
        if (!mb_strlen($requestBody["internalName"], "UTF-8") ||
            (mb_strlen($requestBody["internalName"], "UTF-8") > 8) ||
            preg_match("/[^a-zA-Z]/", $requestBody["internalName"]))
        {
            exit(RSC\ReturnObject::script_failed("Malformed 'internalName' parameter."));
        }

        // Display names are allowed to consist of 1-15 ASCII + Finnish umlaut
        // alphabet characters.
        if (!mb_strlen($requestBody["displayName"], "UTF-8") ||
            (mb_strlen($requestBody["displayName"], "UTF-8") > 15) ||
            preg_match("/[^A-Za-z\x{c5}\x{e5}\x{c4}\x{e4}\x{d6}\x{f6}]/u", $requestBody["displayName"]))
        {
            exit(RSC\ReturnObject::script_failed("Malformed 'displayName' parameter."));
        }
    }

    /// TODO: Verify that containerData and manifestoData contain 100% valid
    /// RallySportED data, as these data will be written into files on the
    /// server.

    /// TODO: The parameters should also contain a session ID or the like, since
    /// only registered users who are logged in should be able to post tracks.
}

// Add the new track into the database.
{
    $database = new RSC\DatabaseAccess();
    $resourceID = new RSC\ResourceID("track");

    if (!$database->connect())
    {
        exit(RSC\ReturnObject::script_failed("Could not connect to the database."));
    }

    /// TODO: Test to make sure the resource ID is unique in the TRACKS table.

    /// TODO: Test to make sure the track's name is unique in the TRACKS table.

    if (!$database->add_new_track($resourceID,
                                  $requestBody["internalName"],
                                  $requestBody["displayName"],
                                  $requestBody["width"],
                                  $requestBody["height"]))
    {
        exit(RSC\ReturnObject::script_failed("Server-side failure. Could not add the new track."));
    }
}

exit(RSC\ReturnObject::script_succeeded());

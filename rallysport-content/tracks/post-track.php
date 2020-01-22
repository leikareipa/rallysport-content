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
 *  - The 'containerData' parameter is expected to be a Base64 string.
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
require_once "validate-track-container-data.php";
require_once "validate-track-manifesto-data.php";

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

    // Validate the track's data.
    {
        // Container data should never be larger than ~250 KB (the value below
        // accounts for the temporary Base64 encoding inflating the data size
        // a bit).
        if (strlen($requestBody["containerData"]) > 358400)
        {
            exit(RSC\ReturnObject::script_failed("Invalid container data."));
        }

        // The container data was sent in as Base64, but we'll want to process
        // and store it in binary.
        $requestBody["containerData"] = base64_decode($requestBody["containerData"], true);
        if (!$requestBody["containerData"])
        {
            exit(RSC\ReturnObject::script_failed("Invalid container data."));
        }

        // Note: At this point, we assume that the track's width and height are
        // equal, e.g. that it's square.
        if (!RSC\is_valid_container_data($requestBody["containerData"], $requestBody["width"]))
        {
            exit(RSC\ReturnObject::script_failed("Invalid container data."));
        }

        if (!RSC\is_valid_manifesto_data($requestBody["manifestoData"]))
        {
            exit(RSC\ReturnObject::script_failed("Invalid container data."));
        }
    }

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

    // Create files on disk to hold the track's data.
    {
        if (!mkdir("./data/{$resourceID->string()}"))
        {
            exit(RSC\ReturnObject::script_failed("Server-side failure. Could not add the new track."));
        }

        // Move into the folder that contains tracks' data files.
        try
        {
            chdir("./data/{$resourceID->string()}");
        }
        catch(Exception $exception)
        {
            exit(RSC\ReturnObject::script_failed("Server-side failure. Could not add the new track."));
        }

        if (!file_put_contents((mb_strtoupper($requestBody["internalName"], "UTF-8") . ".DTA"), $requestBody["containerData"]) ||
            !file_put_contents((mb_strtoupper($requestBody["internalName"], "UTF-8") . '.$FT'), $requestBody["manifestoData"]))
        {
            /// TODO: Remove the track's files and folder.

            /// TODO: Remove the track from the databse.

            exit(RSC\ReturnObject::script_failed("Server-side failure. Could not add the new track."));
        }
    }
}

exit(RSC\ReturnObject::script_succeeded());

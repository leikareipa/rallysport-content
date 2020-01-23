<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to add a new track into RSC's database.
 * 
 * Returns: JSON {succeeded: bool [, errorMessage: string]}
 * 
 *  - On failure (that is, when succeeded == false), 'errorMessage' will provide
 *    a brief description of the error.
 * 
 *  - On success, only the 'succeeded' parameter (set to true) is returned.
 * 
 */

require_once dirname(__FILE__)."/../../common-scripts/return.php";
require_once dirname(__FILE__)."/../../common-scripts/resource-id.php";
require_once dirname(__FILE__)."/../../common-scripts/database.php";
require_once dirname(__FILE__)."/validate-track-container-data.php";
require_once dirname(__FILE__)."/validate-track-manifesto-data.php";

// Attempts to add to the Rally-Sport Content database a new track, whose data
// are specified by the function call parameters.
//
// Note:
//
//  - The 'containerData' parameter is expected to be a Base64-encoded string
//    representation of the track's RallySportED container file.
//
//  - Other strings in the parameters are expected in UTF-8.
// 
//  - For more information about the parameters and what they mean, see docs in
//    RallySportED-js's repo, https://github.com/leikareipa/rallysported-js.
//
//  - The function should not return. Instead, it should exit() with either
//    ReturnObject::script_succeeded() or ReturnObject::script_failed().
//
function add_new_track(array $parameters)
{
    // Validate input parameters.
    {
        if (!isset($parameters["internalName"]))  exit(ReturnObject::script_failed("Missing the 'internalName' parameter."));
        if (!isset($parameters["displayName"]))   exit(ReturnObject::script_failed("Missing the 'displayName' parameter."));
        if (!isset($parameters["width"]))         exit(ReturnObject::script_failed("Missing the 'width' parameter."));
        if (!isset($parameters["height"]))        exit(ReturnObject::script_failed("Missing the 'height' parameter."));
        if (!isset($parameters["containerData"])) exit(ReturnObject::script_failed("Missing the 'containerData' parameter."));
        if (!isset($parameters["manifestoData"])) exit(ReturnObject::script_failed("Missing the 'manifestoData' parameter."));

        // Validate track dimensions.
        {
            if ($parameters["width"] != $parameters["height"])
            {
                exit(ReturnObject::script_failed("Track dimensions must be square."));
            }

            if (($parameters["width"] != 64) &&
                ($parameters["height"] != 128))
            {
                exit(ReturnObject::script_failed("Unsupported track dimensions ."));
            }
        }

        // Validate names.
        {
            // Internal track names are allowed to consist of 1-8 ASCII alphabet characters.
            if (!mb_strlen($parameters["internalName"], "UTF-8") ||
                (mb_strlen($parameters["internalName"], "UTF-8") > 8) ||
                preg_match("/[^a-zA-Z]/", $parameters["internalName"]))
            {
                exit(ReturnObject::script_failed("Malformed 'internalName' parameter."));
            }

            // Display names are allowed to consist of 1-15 ASCII + Finnish umlaut
            // alphabet characters.
            if (!mb_strlen($parameters["displayName"], "UTF-8") ||
                (mb_strlen($parameters["displayName"], "UTF-8") > 15) ||
                preg_match("/[^A-Za-z\x{c5}\x{e5}\x{c4}\x{e4}\x{d6}\x{f6}]/u", $parameters["displayName"]))
            {
                exit(ReturnObject::script_failed("Malformed 'displayName' parameter."));
            }
        }

        // Validate the track's data.
        {
            // Container data should never be larger than ~250 KB (the value below
            // accounts for the temporary Base64 encoding inflating the data size
            // a bit).
            if (strlen($parameters["containerData"]) > 358400)
            {
                exit(ReturnObject::script_failed("Invalid container data."));
            }

            // The container data was sent in as Base64, but we'll want to process
            // and store it in binary.
            $parameters["containerData"] = base64_decode($parameters["containerData"], true);
            if (!$parameters["containerData"])
            {
                exit(ReturnObject::script_failed("Invalid container data."));
            }

            // Note: At this point, we assume that the track's width and height are
            // equal, e.g. that it's square.
            if (!is_valid_container_data($parameters["containerData"], $parameters["width"]))
            {
                exit(ReturnObject::script_failed("Invalid container data."));
            }

            if (!is_valid_manifesto_data($parameters["manifestoData"]))
            {
                exit(ReturnObject::script_failed("Invalid container data."));
            }
        }

        /// TODO: The parameters should also contain a session ID or the like, since
        /// only registered users who are logged in should be able to post tracks.
    }

    // Add the new track into the database.
    {
        $database = new DatabaseAccess();
        $resourceID = new ResourceID("track");

        if (!$database->connect())
        {
            exit(ReturnObject::script_failed("Could not connect to the database."));
        }

        /// TODO: Test to make sure the resource ID is unique in the TRACKS table.

        /// TODO: Test to make sure the track's name is unique in the TRACKS table.

        if (!$database->add_new_track($resourceID,
                                    $parameters["internalName"],
                                    $parameters["displayName"],
                                    $parameters["width"],
                                    $parameters["height"]))
        {
            exit(ReturnObject::script_failed("Server-side failure. Could not add the new track."));
        }

        // Create files on disk to hold the track's data.
        {
            if (!mkdir("./server-data/{$resourceID->string()}"))
            {
                exit(ReturnObject::script_failed("Server-side failure. Could not add the new track."));
            }

            // Move into the folder that contains tracks' data files.
            try
            {
                chdir("./server-data/{$resourceID->string()}");
            }
            catch(Exception $exception)
            {
                exit(ReturnObject::script_failed("Server-side failure. Could not add the new track."));
            }

            if (!file_put_contents((mb_strtoupper($parameters["internalName"], "UTF-8") . ".DTA"), $parameters["containerData"]) ||
                !file_put_contents((mb_strtoupper($parameters["internalName"], "UTF-8") . '.$FT'), $parameters["manifestoData"]))
            {
                /// TODO: Remove the track's files and folder.

                /// TODO: Remove the track from the databse.

                exit(ReturnObject::script_failed("Server-side failure. Could not add the new track."));
            }
        }
    }

    exit(ReturnObject::script_succeeded());
}

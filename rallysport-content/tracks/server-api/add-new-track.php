<?php namespace RSC\API;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to add a new track into RSC's database.
 * 
 */

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/database-connection/track-database-connection.php";
require_once __DIR__."/../../common-scripts/svg-image-from-kierros-data.php";
require_once __DIR__."/../../common-scripts/validate-track-container-data.php";
require_once __DIR__."/../../common-scripts/validate-track-manifesto-data.php";

// Attempts to add to the Rally-Sport Content database a new track, whose data
// are specified by the function call parameters.
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On failure, the response body will be a JSON string whose 'errorMessage'
//    attribute provides a brief description of the error.
//
//  - On success, returns the HTML status code 201 without a body.
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
//  - The function should always return using exit() together with a Response
//    object, e.g. exit(Response::code(200)->json([...]).
//
function add_new_track(array $parameters)
{
    // Validate input parameters.
    {
        if (!isset($parameters["internalName"]))  exit(Response::code(400)->error_message("Missing the 'internalName' parameter."));
        if (!isset($parameters["displayName"]))   exit(Response::code(400)->error_message("Missing the 'displayName' parameter."));
        if (!isset($parameters["width"]))         exit(Response::code(400)->error_message("Missing the 'width' parameter."));
        if (!isset($parameters["height"]))        exit(Response::code(400)->error_message("Missing the 'height' parameter."));
        if (!isset($parameters["containerData"])) exit(Response::code(400)->error_message("Missing the 'containerData' parameter."));
        if (!isset($parameters["manifestoData"])) exit(Response::code(400)->error_message("Missing the 'manifestoData' parameter."));

        // Validate track dimensions.
        {
            if ($parameters["width"] != $parameters["height"])
            {
                exit(Response::code(400)->error_message("Track dimensions must be square."));
            }

            if (($parameters["width"] != 64) &&
                ($parameters["height"] != 128))
            {
                exit(Response::code(400)->error_message("Unsupported track dimensions ."));
            }
        }

        // Validate names.
        {
            // Internal track names are allowed to consist of 1-8 ASCII alphabet characters.
            if (!mb_strlen($parameters["internalName"], "UTF-8") ||
                (mb_strlen($parameters["internalName"], "UTF-8") > 8) ||
                preg_match("/[^a-zA-Z]/", $parameters["internalName"]))
            {
                exit(Response::code(400)->error_message("Malformed 'internalName' parameter."));
            }

            // Display names are allowed to consist of 1-15 ASCII + Finnish umlaut
            // alphabet characters.
            if (!mb_strlen($parameters["displayName"], "UTF-8") ||
                (mb_strlen($parameters["displayName"], "UTF-8") > 15) ||
                preg_match("/[^A-Za-z-.,():\/ \x{c5}\x{e5}\x{c4}\x{e4}\x{d6}\x{f6}]/u", $parameters["displayName"]))
            {
                exit(Response::code(400)->error_message("Malformed 'displayName' parameter."));
            }

            // We'll want the internal name to be all uppercase, for legacy reasons.
            $parameters["internalName"] = mb_strtoupper($parameters["internalName"]);
        }

        // Validate the track's data.
        {
            // Container data should never be larger than ~250 KB (the value below
            // accounts for the temporary Base64 encoding inflating the data size
            // a bit).
            if (strlen($parameters["containerData"]) > 358400)
            {
                exit(Response::code(400)->error_message("Invalid container data."));
            }

            // The container data was sent in as Base64, but we'll want to process
            // and store it in binary.
            $parameters["containerData"] = base64_decode($parameters["containerData"], true);
            if (!$parameters["containerData"])
            {
                exit(Response::code(400)->error_message("Invalid container data."));
            }

            // Note: At this point, we assume that the track's width and height are
            // equal, e.g. that it's square.
            if (!is_valid_container_data($parameters["containerData"], $parameters["width"]))
            {
                exit(Response::code(400)->error_message("Invalid container data."));
            }

            // Manifesto files are fairly simple and relatively short text files -
            // they should not be very large at all, often less than a kilobyte.
            if (strlen($parameters["manifestoData"]) > 10240)
            {
                exit(Response::code(400)->error_message("Invalid manifesto data."));
            }

            if (!is_valid_manifesto_data($parameters["manifestoData"]))
            {
                exit(Response::code(400)->error_message("Invalid manifesto data."));
            }
        }

        /// TODO: The parameters should also contain a session ID or the like, since
        /// only registered users who are logged in should be able to post tracks.
    }

    // Add the new track into the database.
    {
        $resourceID = \RSC\ResourceID::random(\RSC\ResourceType::TRACK);
        $creatorID = \RSC\ResourceID::random(\RSC\ResourceType::USER); /// TODO: Use the actual creator ID.

        /// TODO: Test to make sure the track's name is unique in the TRACKS table.

        // We'll also need to include Rally-Sport's default HITABLE.TXT file,
        // which is required by RallySportED for playing the track in-game.
        if (!($hitableData = file_get_contents(__DIR__."/../server-data/HITABLE.TXT")))
        {
            exit(Response::code(500)->error_message("Invalid HITABLE.TXT file."));
        }

        if (!(new DatabaseConnection\TrackDatabaseConnection())->add_new_track(
                                                                 $resourceID,
                                                                 $creatorID,
                                                                 $parameters["internalName"],
                                                                 $parameters["displayName"],
                                                                 $parameters["width"],
                                                                 $parameters["height"],
                                                                 $parameters["containerData"],
                                                                 $parameters["manifestoData"],
                                                                 \RSC\svg_image_from_kierros_data($parameters["containerData"]),
                                                                 $hitableData))
        {
            exit(Response::code(500)->error_message("Database error."));
        }
    }

    exit(Response::code(201)->empty_body());
}

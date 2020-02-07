<?php namespace RSC\API\Tracks;
      use RSC\DatabaseConnection;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * track or a set of tracks to the client.
 * 
 */

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/resource/resource-id.php";
require_once __DIR__."/../../common-scripts/database-connection/track-database.php";

// Sends the track's data (container and manifesto files) as a zip file to
// the client.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On failure, the response body will be a JSON string whose 'errorMessage'
//    attribute provides a brief description of the error. No track data will
//    be returned in this case.
//
//  - On success, the response body will consist of the file's bytes.
//
function serve_track_data_as_zip_file(\RSC\ResourceID $trackResourceID = NULL) : void
{
    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we only support serving individual tracks' data.
    if (!$trackResourceID)
    {
        exit(API\Response::code(400)->error_message("A track ID must be provided."));
    }

    $trackZipFile = (new DatabaseConnection\TrackDatabase())->get_track_data_as_zip_file($trackResourceID);
    if (!$trackZipFile)
    {
        exit(API\Response::code(404)->error_message("No matching track data found."));
    }

    // We ask the client to keep the file cached for 30 days, as server-side
    // track data are not expected to change.
    exit(API\Response::code(200)->binary_file($trackZipFile["filename"], $trackZipFile["data"], 2592000));
}

// Prints into the PHP output stream a stringified JSON object containing the
// track's data.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On failure, the response body will be a JSON string whose 'errorMessage'
//    attribute provides a brief description of the error. No track data will
//    be returned in this case.
//
//  - On success, the response body will be a JSON string that contains the
//    track's data. The data will be in the following form:
//
//      {
//          // Base64-encoded string representing the track's container file.
//          container: string,
//
//          // Plaintext string representing the track's manifesto file.
//          manifesto: string,
//
//          meta: object
//          {
//              // (See docs in https://github.com/leikareipa/rallysported-js.)
//              internalName: string,
//              displayName: string,
//              width: int,
//              height: int,
//
//              // ResourceID identifying this track in RSC's database.
//              contentID: string,
//
//              // ResourceID identifying the track's creator in RSC's database.
//              creatorID: string,
//          }
//      }
//
function serve_track_data_as_json(\RSC\ResourceID $trackResourceID = NULL) : void
{
    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we only support serving individual tracks' data.
    if (!$trackResourceID)
    {
        exit(API\Response::code(400)->error_message("A track ID must be provided."));
    }

    $trackDataJSON = (new DatabaseConnection\TrackDatabase())->get_track_data_as_json($trackResourceID);
    if (!$trackDataJSON)
    {
        exit(API\Response::code(404)->error_message("No matching track data found."));
    }

    // We ask the client to keep the response data cached for 30 days, as
    // server-side track data are not expected to change.
    exit(API\Response::code(200)->json(json_decode($trackDataJSON, true), 2592000));
}

// Prints into the PHP output stream a stringified JSON object containing public
// information about the given track, or of all tracks in the database if the
// track resource ID is NULL.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
// Returns: a response from the Response class (HTML status code + body).
//
function serve_track_metadata_as_json(\RSC\ResourceID $trackResourceID = NULL) : void
{
    $trackInfo = (new DatabaseConnection\TrackDatabase())->get_track_metadata($trackResourceID);
    if (!$trackInfo || !is_array($trackInfo) || !count($trackInfo))
    {
        exit(API\Response::code(404)->error_message("No matching track data found."));
    }

    // We ask the client to keep the response data cached for 30 days, as
    // server-side track data are not expected to change.
    exit(API\Response::code(200)->json($trackInfo, 2592000));
}

function serve_track_kierros_svg(\RSC\ResourceID $trackResourceID = NULL) : void
{
    $kierrosSVG = (new DatabaseConnection\TrackDatabase())->get_track_kierros_svg($trackResourceID);
    if (!$kierrosSVG)
    {
        exit(API\Response::code(404)->error_message("No matching track data found."));
    }

    // We ask the client to keep the response data cached for 30 days, as
    // server-side track data are not expected to change.
    exit(API\Response::code(200)->svg_image($kierrosSVG, 2592000));
}

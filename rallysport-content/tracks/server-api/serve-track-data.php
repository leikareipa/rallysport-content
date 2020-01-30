<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * track or a set of tracks to the client.
 * 
 */

require_once __DIR__."/../../common-scripts/return.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/track-database-connection.php";

// Sends the track's data (container and manifesto files) as a zip file to
// the client.
//
// Note: This function should always return using exit() with either
// ReturnObject::script_failed() or ReturnObject::file().
//
// Returns:
//
//  - On failure: JSON {succeeded: false, errorMessage: string}
//
//  - On success: The file's bytes as a stream
//
//
function serve_track_data_as_zip_file(TrackResourceID $trackResourceID = NULL)
{
    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we only support serving individual tracks' data.
    if (!$trackResourceID)
    {
        exit(ReturnObject::script_failed("A track ID must be provided."));
    }

    $trackZipFile = (new TrackDatabaseConnection())->get_track_data_as_zip_file($trackResourceID);
    if (!$trackZipFile)
    {
        exit(ReturnObject::script_failed("No matching tracks found."));
    }

    exit(ReturnObject::file($trackZipFile["filename"], $trackZipFile["data"]));
}

// Prints into the PHP output stream a stringified JSON object containing the
// track's data.
//
// Note: This function should always return using exit() with either
// ReturnObject::script_failed() or ReturnObject::script_succeeded().
//
// Returns: JSON {succeeded: bool [, track: object[, errorMessage: string]]}
//
//  - On failure (that is, when 'succeeded' == false), 'errorMessage' will
//    provide a brief description of the error. No track data will be returned
//    in this case.
//
//  - On success (when 'succeeded' == true), the 'track' object will contain
//    the track's data. The data will be in the following form:
//
//      {
//          // Base64-encoded string representing the bytes of Rally-Sport's HITABLE.TXT file.
//          hitable: string,
//
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
//              // TrackResourceID identifying this track in RSC's database.
//              contentID: string,
//
//              // UserResourceID identifying the track's creator in RSC's database.
//              creatorID: string,
//          }
//      }
//
function serve_track_data_as_json(TrackResourceID $trackResourceID = NULL)
{
    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we only support serving individual tracks' data.
    if (!$trackResourceID)
    {
        exit(ReturnObject::script_failed("A track ID must be provided."));
    }

    $trackDataJSON = (new TrackDatabaseConnection())->get_track_data_as_json($trackResourceID);
    if (!$trackDataJSON)
    {
        exit(ReturnObject::script_failed("Failed to fetch track data."));
    }

    exit(ReturnObject::script_succeeded(json_decode($trackDataJSON, true), "track"));
}

// Prints into the PHP output stream a stringified JSON object containing public
// information about the given track, or of all tracks in the database if the
// track resource ID is NULL.
//
// Note: This function should always return using exit() with either
// ReturnObject::script_failed() or ReturnObject::script_succeeded().
//
// Returns: JSON {succeeded: bool [, tracks: object[, errorMessage: string]]}
//
//  - On failure (that is, when 'succeeded' == false), 'errorMessage' will
//    provide a brief description of the error. No track data will be returned
//    in this case.
//
//  - On success (when 'succeeded' == true), the 'tracks' object will contain
//    information about the tracks queried. The 'errorMessage' string will
//    not be included.
//
function serve_track_metadata_as_json(TrackResourceID $trackResourceID = NULL)
{
    $trackInfo = (new TrackDatabaseConnection())->get_track_metadata($trackResourceID);
    if (!$trackInfo || !is_array($trackInfo) || !count($trackInfo))
    {
        exit(ReturnObject::script_failed("No matching tracks found."));
    }

    exit(ReturnObject::script_succeeded($trackInfo, "tracks"));
}

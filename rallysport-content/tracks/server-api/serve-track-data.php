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
require_once __DIR__."/../../common-scripts/database.php";
require_once __DIR__."/../../common-scripts/resource-id.php";

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
function serve_track_data_as_zip_file(TrackResourceID $resourceID = NULL)
{
    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we only support serving individual tracks' data.
    if (!$resourceID)
    {
        exit(ReturnObject::script_failed("A track ID must be provided."));
    }

    $database = new DatabaseAccess();
    if (!$database->connect())
    {
        exit(ReturnObject::script_failed("Could not connect to the database."));
    }

    $trackZipFile = $database->get_track_data_as_zip_file($resourceID);
    if (!$trackZipFile)
    {
        exit(ReturnObject::script_failed("No matching tracks found."));
    }

    exit(ReturnObject::file($trackZipFile["filename"], $trackZipFile["data"]));
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
function serve_track_metadata_as_json(TrackResourceID $resourceID = NULL)
{
    $database = new DatabaseAccess();
    if (!$database->connect())
    {
        exit(ReturnObject::script_failed("Could not connect to the database."));
    }

    $trackInfo = $database->get_track_information($resourceID);
    if (!is_array($trackInfo) || !count($trackInfo))
    {
        exit(ReturnObject::script_failed("No matching tracks found."));
    }

    exit(ReturnObject::script_succeeded($trackInfo, "tracks"));
}

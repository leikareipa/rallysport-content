<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve the data (or metadata) of a
 * given track (or a set of tracks).
 * 
 */

require_once __DIR__."/../../common-scripts/return.php";
require_once __DIR__."/../../common-scripts/database.php";
require_once __DIR__."/../../common-scripts/resource-id.php";

// Sends the track's data (container and manifesto files) as a zip file to
// the client.
//
// Note: This function should not return. Instead, it should exit() with either
// ReturnObject::script_failed() or ReturnObject::file().
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

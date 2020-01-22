<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script prints out textual information about a given track (or, if no
 * track ID is specified, of all tracks).
 * 
 * Returns: JSON {succeeded: bool [, tracks: object[, errorMessage: string]]}
 * 
 *  - On failure (that is, when 'succeeded' == false), 'errorMessage' will
 *    provide a brief description of the error. No track data will be returned
 *    in this case.
 * 
 *  - On success (when 'succeeded' == true), the 'tracks' object will contain
 *    information about the tracks queried. The 'errorMessage' string will
 *    not be included.
 * 
 */

require_once "../common-scripts/return.php";
require_once "../common-scripts/database.php";
require_once "../common-scripts/resource-id.php";

function printout_track_information(ResourceID $trackResourceID = NULL)
{
    $database = new DatabaseAccess();
    if (!$database->connect())
    {
        exit(ReturnObject::script_failed("Could not connect to the database."));
    }

    $trackInfo = $database->get_track_information($trackResourceID);
    if (!is_array($trackInfo) || !count($trackInfo))
    {
        exit(ReturnObject::script_failed("No matching tracks not found."));
    }

    exit(ReturnObject::script_succeeded($trackInfo, "tracks"));
}

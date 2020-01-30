<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Provides functionality for accessing the RSC track database. (Note that
 * for now, the track database is in fact just a table in the general RSC
 * database rather than a database of its own.)
 * 
 * Usage:
 * 
 *  1. Create a new TrackDatabaseConnection instance: $trackDB = new TrackDatabaseConnection().
 * 
 *  2. Use the methods provided by the TrackDatabaseConnection class for manipulating
 *     the contents of the track database.
 * 
 */

require_once "database-connection.php";
require_once "resource-id.php";

class TrackDatabaseConnection extends DatabaseConnection
{
    /*
     * The track database is currently a table ("rsc_tracks") in the general
     * RSC database, to which this class gains access via the DatabaseConnection
     * class.
     * 
     */

    function __construct()
    {
        parent::__construct();
        return;
    }

    // Adds into the TRACKS table a new track with the given parameters. Returns
    // TRUE on success; FALSE otherwise. The 'trackDataZIP' parameter is a string
    // representing the byte data of a zip file containing the track's end-user
    // data (container, manifesto, and HITABLE files).
    function add_new_track(TrackResourceID $resourceID,
                           UserResourceID $creatorID,
                           string $internalName,
                           string $displayName,
                           int $width,
                           int $height,
                           string $containerData,
                           string $manifestoData,
                           string $hitableData) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        /// TODO: Validate the input parameters.

        // The full track data as a zip file. The file contains everything needed
        // to play the track in Rally-Sport using the RallySportED Loader.
        $trackDataZIP = create_zip_from_file_data(["{$internalName}.DTA"  => $containerData,
                                                   "{$internalName}.\$FT" => $manifestoData,
                                                   "HITABLE.TXT"          => $hitableData],
                                                   $internalName);
        if (!$trackDataZIP)
        {
            return false;
        }

        // The full track data as a JSON object. The JSON contains everything
        // needed to load the track into RallySportED-js for editing.
        $trackDataJSON = json_encode([
            "hitable"   => base64_encode($hitableData),
            "container" => base64_encode($containerData),
            "manifesto" => $manifestoData,
            "meta"      => [
                "internalName" => $internalName,
                "displayName"  => $displayName,
                "width"        => $width,
                "height"       => $height,
                "contentID"    => $resourceID->string(),
                "creatorID"    => $creatorID->string(),
            ],
        ]);

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_tracks
                                   (resource_id,
                                    track_name_internal,
                                    track_name_display,
                                    track_width,
                                    track_height,
                                    track_data_zip,
                                    track_data_json,
                                    creation_timestamp,
                                    creator_resource_id)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   $internalName,
                                   $displayName,
                                   $width,
                                   $height,
                                   $trackDataZIP,
                                   $trackDataJSON,
                                   time(),
                                   $creatorID->string()]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    // On error, FALSE will be returned.
    function get_track_metadata(TrackResourceID $resourceID = NULL)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        // If no resource ID is provided, we'll return info for all tracks
        // in the database.
        $rowSelector = ($resourceID? "WHERE resource_id = ?" : "");

        $trackInfo = $this->issue_db_query(
                        "SELECT
                          resource_id,
                          creator_resource_id,
                          creation_timestamp,
                          track_name_internal,
                          track_name_display,
                          track_width,
                          track_height
                         FROM rsc_tracks
                         {$rowSelector}",
                         ($resourceID? [$resourceID->string()] : NULL));

        if (!is_array($trackInfo) || !count($trackInfo))
        {
            return false;
        }

        // Simplify some parameter names, etc.
        $returnObject = [];
        foreach ($trackInfo as $track)
        {
            $returnObject[] =
            [
                "resourceID"       => $track["resource_id"],
                "creatorID"        => $track["creator_resource_id"],
                "internalName"     => $track["track_name_internal"],
                "displayName"      => $track["track_name_display"],
                "width"            => $track["track_width"],
                "height"           => $track["track_height"],
                "creationTimestamp"=> $track["creation_timestamp"],
            ];
        }

        return $returnObject;
    }

    // Returns the given track's data as a zip file. The zip file will contain
    // the track's container, manifesto, and HITABLE files; and is thus suitable
    // for serving the track to end-users of the RallySportED Loader.
    //
    // On success, the return value will be an array of the following form:
    //
    //   [
    //       "filename": string,
    //       "data": string
    //   ]
    //
    //   - The 'filename' parameter gives the filename associated with the data
    //     (with the .zip extension). Generally, this will match the project's
    //     internal name; e.g. a project called "Suorundi" would have the
    //     filename "SUORUNDI.ZIP".
    //
    //   - The 'data' parameter contains as a string the zip file's raw bytes.
    //
    // On failure, FALSE is returned.
    //
    function get_track_data_as_zip_file(TrackResourceID $resourceID = NULL)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        // For the moment, we can't return multiple tracks' data.
        if (!$resourceID)
        {
            return false;
        }

        $trackZipFile = $this->issue_db_query(
                            "SELECT
                              track_data_zip,
                              track_name_internal
                             FROM rsc_tracks
                             WHERE resource_id = ?",
                            [$resourceID->string()]);

        // We should receive an array with exactly one element: the given
        // track's data.
        if (!is_array($trackZipFile) ||
            (count($trackZipFile) != 1) ||
            !isset($trackZipFile[0]["track_data_zip"]) ||
            !isset($trackZipFile[0]["track_name_internal"]))
        {
            return false;
        }

        return ["filename" => "{$trackZipFile[0]['track_name_internal']}.ZIP",
                "data"     => $trackZipFile[0]["track_data_zip"]];
    }

    // Returns the given track's data as a JSON string. The string will contain
    // all data needed to load the track into RallySportED-js for editing. On
    // failure, FALSE is returned.
    function get_track_data_as_json(TrackResourceID $resourceID = NULL)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        // For the moment, we can't return multiple tracks' data.
        if (!$resourceID)
        {
            return false;
        }

        $trackJSON = $this->issue_db_query(
                        "SELECT
                          track_data_json
                         FROM rsc_tracks
                         WHERE resource_id = ?",
                        [$resourceID->string()]);

        // We should receive an array with exactly one element: the given
        // track's data.
        if (!is_array($trackJSON) ||
            (count($trackJSON) != 1) ||
            !isset($trackJSON[0]["track_data_json"]))
        {
            return false;
        }

        return $trackJSON[0]["track_data_json"];
    }
}

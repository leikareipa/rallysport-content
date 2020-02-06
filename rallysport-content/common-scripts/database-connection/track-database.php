<?php namespace RSC\DatabaseConnection;

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
 *  1. Create a new TrackDatabase instance: $trackDB = new DatabaseConnection\TrackDatabase().
 * 
 *  2. Use the methods provided by the TrackDatabase class for manipulating
 *     the contents of the track database.
 * 
 */

require_once __DIR__."/database-connection.php";
require_once __DIR__."/../resource/resource-id.php";
require_once __DIR__."/../resource/resource-visibility.php";
require_once __DIR__."/../zip-file.php";

class TrackDatabase extends DatabaseConnection
{
    /*
     * The track database is currently a table ("rsc_tracks") in the general
     * RSC database, to which this class gains access via the DatabaseConnection
     * class.
     * 
     */

    public function __construct()
    {
        parent::__construct();

        return;
    }

    private function increment_track_download_count(\RSC\ResourceID $resourceID) : bool 
    {
        if (!$this->is_connected())
        {
            return false;
        }

        // The HEAD request is identical to a GET request in all respects except
        // that the data body is not returned to the caller. As such, we shouldn't
        // increment the download count then.
        if ($_SERVER["REQUEST_METHOD"] === "HEAD")
        {
            return true;
        }

        $databaseReturnValue = $this->issue_db_command("UPDATE rsc_tracks
                                                        SET download_count = download_count + 1
                                                        WHERE resource_id = ?",
                                                        [$resourceID->string()]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Adds into the TRACKS table a new track with the given parameters. Returns
    // TRUE on success; FALSE otherwise.
    public function add_new_track(\RSC\ResourceID $resourceID,
                                  \RSC\ResourceID $creatorID,
                                  string $internalName,
                                  string $displayName,
                                  int $width,
                                  int $height,
                                  string $containerData,
                                  string $manifestoData,
                                  string $kierrosSVGImage) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        /// TODO: Validate the input parameters.

        if (!($compressedContainer = gzencode($containerData, 9, FORCE_GZIP)) ||
            !($compressedManifesto = gzencode($manifestoData, 9, FORCE_GZIP)) ||
            !($compressedKierrosSVG = gzencode($kierrosSVGImage, 9, FORCE_GZIP)))
        {
            return false;
        }

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_tracks
                                  (resource_id,
                                   resource_visibility,
                                   track_name_internal,
                                   track_name_display,
                                   track_width,
                                   track_height,
                                   track_container_gzip,
                                   track_manifesto_gzip,
                                   kierros_svg_gzip,
                                   creation_timestamp,
                                   creator_resource_id)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                  [$resourceID->string(),
                                   \RSC\ResourceVisibility::EVERYONE,
                                   $internalName,
                                   $displayName,
                                   $width,
                                   $height,
                                   $compressedContainer,
                                   $compressedManifesto,
                                   $compressedKierrosSVG,
                                   time(),
                                   $creatorID->string()]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns public information about the given track. If a null resource ID
    // is given, the information of all tracks in the database will be returned.
    // On error, FALSE will be returned.
    public function get_track_metadata(\RSC\ResourceID $resourceID = NULL)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        // If no resource ID is provided, we'll return info for all tracks
        // in the database.
        $rowSelector = ($resourceID? "WHERE resource_id = ?" : "");

        $trackInfo = $this->issue_db_query(
                        "SELECT resource_id,
                                creator_resource_id,
                                creation_timestamp,
                                download_count,
                                track_name_internal,
                                track_name_display,
                                track_width,
                                track_height,
                                kierros_svg_gzip
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
                "resourceID"        => $track["resource_id"],
                "creatorID"         => $track["creator_resource_id"],
                "internalName"      => $track["track_name_internal"],
                "displayName"       => $track["track_name_display"],
                "width"             => $track["track_width"],
                "height"            => $track["track_height"],
                "creationTimestamp" => $track["creation_timestamp"],
                "kierrosSVG"        => gzdecode($track["kierros_svg_gzip"]),
                "downloadCount"     => $track["download_count"],
            ];
        }

        return $returnObject;
    }

    // Returns the given track's data as a zip file. The zip file will include
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
    public function get_track_data_as_zip_file(\RSC\ResourceID $resourceID = NULL)
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

        $trackData = $this->issue_db_query(
                            "SELECT track_container_gzip,
                                    track_manifesto_gzip,
                                    track_name_internal,
                                    creation_timestamp
                             FROM rsc_tracks
                             WHERE resource_id = ?",
                            [$resourceID->string()]);

        // We should receive an array with exactly one element: the given
        // track's data.
        if (!is_array($trackData) || (count($trackData) != 1))
        {
            return false;
        }

        // Build a RallySportED Loader-compatible zip archive out of the track's
        // data files.
        $zipArchive = new \RSC\ZipFile();
        {
            $internalTrackName = strtoupper($trackData[0]["track_name_internal"]);
            $fileTimestamp = $trackData[0]["creation_timestamp"];

            // We'll include Rally-Sport's default HITABLE.TXT file.
            if (!($hitableData = file_get_contents(__DIR__."/../../tracks/server-data/HITABLE.TXT")))
            {
                return false;
            }

            $zipArchive->add_file("{$internalTrackName}/{$internalTrackName}.DTA",
                                  gzdecode($trackData[0]["track_container_gzip"]),
                                  $fileTimestamp);

            $zipArchive->add_file("{$internalTrackName}/{$internalTrackName}.\$FT",
                                  gzdecode($trackData[0]["track_manifesto_gzip"]),
                                  $fileTimestamp);

            $zipArchive->add_file("{$internalTrackName}/HITABLE.TXT",
                                  $hitableData,
                                  $fileTimestamp);
        }

        $this->increment_track_download_count($resourceID);

        return ["filename" => "{$internalTrackName}.ZIP",
                "data"     => $zipArchive->string()];
    }

    // Returns the given track's data as a JSON string. The string will contain
    // all data needed to load the track into RallySportED-js for editing. On
    // failure, FALSE is returned.
    public function get_track_data_as_json(\RSC\ResourceID $resourceID = NULL)
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

        $trackData = $this->issue_db_query(
                        "SELECT track_container_gzip,
                                track_manifesto_gzip,
                                track_name_internal,
                                track_name_display,
                                track_width,
                                track_height,
                                creator_resource_id
                         FROM rsc_tracks
                         WHERE resource_id = ?",
                        [$resourceID->string()]);

        // We should receive an array with exactly one element: the given
        // track's data.
        if (!is_array($trackData) || (count($trackData) != 1))
        {
            return false;
        }

        $trackDataJSON = json_encode([
            "container" => base64_encode(gzdecode($trackData[0]["track_container_gzip"])),
            "manifesto" => gzdecode($trackData[0]["track_manifesto_gzip"]),
            "meta"      => [
                "internalName" => $trackData[0]["track_name_internal"],
                "displayName"  => $trackData[0]["track_name_display"],
                "width"        => $trackData[0]["track_width"],
                "height"       => $trackData[0]["track_height"],
                "contentID"    => $resourceID->string(),
                "creatorID"    => $trackData[0]["creator_resource_id"],
            ],
        ]);

        $this->increment_track_download_count($resourceID);

        return $trackDataJSON;
    }
}

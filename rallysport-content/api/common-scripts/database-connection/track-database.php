<?php namespace RSC\DatabaseConnection;
      use RSC\Resource;

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
require_once __DIR__."/../resource/resource.php";
require_once __DIR__."/../resource/resource-id.php";
require_once __DIR__."/../resource/resource-visibility.php";
require_once __DIR__."/../rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../zip-file.php";

class TrackDatabase extends DatabaseConnection
{
    /*
     * The track database is currently a table ("rsc_tracks") in the general
     * RSC database, to which this class gains access via the DatabaseConnection
     * class.
     * 
     */

    // Utility function for creating a hash of the given track resource's data
    // that can be stored in the tracks database. On error, returns NULL.
    public static function generate_hash_of_track_data(Resource\TrackResource $track)
    {
        // Some track resources instances may only contain metadata and not the
        // actual track data. In that case, we can't produce a hash.
        if (!$track->data())
        {
            return NULL;
        }

        return hash("sha256", $track->data()->container());
    }

    // Returns TRUE if a track has not yet been uploaded using the given hash;
    // FALSE otherwise. Note that FALSE will also be returned if an error is
    // encountered.
    public function is_resource_hash_unique(string $resourceHash) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_tracks
                                             WHERE resource_data_hash_sha256 = ?",
                                            [$resourceHash]);

        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return false;
        }

        return (($dbResponse[0]["COUNT(*)"] == 0)? true : false);
    }

    // Returns TRUE if the given track name does not exist among the other
    // tracks in the database; FALSE otherwise.
    public function is_track_name_unique(string $name) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_tracks
                                             WHERE track_name = ?",
                                            [$name]);

        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return false;
        }

        return (($dbResponse[0]["COUNT(*)"] == 0)? true : false);
    }

    private function increment_track_download_count(Resource\TrackResourceID $resourceID) : bool 
    {
        if (!$this->is_connected())
        {
            return false;
        }

        // If write access to the database is currently disabled, we want to
        // bail without attempting to increment the download count, as proceeding
        // would cause the script to exit with an error, preventing the user from
        // e.g. downloading a track (which triggers the download count increment
        // in the first place). Failing to count downloads is better than entirely
        // disrupting the service.
        if (!self::ALLOW_DATABASE_WRITE_ACCESS)
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

    // Mark the track identified by the given resource ID as being deleted.
    // Note that the track's entry in the database will not be removed, so as
    // to reserve its resource ID. The track will, however, no longer be shown
    // on public track listings.
    public function delete_track(Resource\TrackResourceID $resourceID)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_command("UPDATE rsc_tracks
                                               SET resource_visibility = ?,
                                                   resource_data_hash_sha256 = NULL,
                                                   track_width = NULL,
                                                   track_height = NULL,
                                                   track_name = NULL,
                                                   track_container_gzip = NULL,
                                                   track_manifesto_gzip = NULL,
                                                   kierros_svg_gzip = NULL
                                               WHERE resource_id = ?",
                                              [Resource\ResourceVisibility::DELETED,
                                               $resourceID->string()]);

        return (($dbResponse == 0)? true : false);
    }

    // Returns the count of tracks in the database; or FALSE on error. The
    // 'uploaders' array provides user ID strings such that if non-empty, only
    // tracks uploaded by these users will be included in the count; and the
    // 'visibilityLevels' array provides ResourceVisibility elements such that
    // only tracks whose visibility level is one of these will be included in
    // the count.
    public function tracks_count(array $uploaders = [],
                                 array $visibilityLevels = [Resource\ResourceVisibility::PUBLIC]) : int
    {
        // Assert that we received valid parameter values.
        {
            foreach ($visibilityLevels as $visibilityLevel)
            {
                if (!Resource\ResourceVisibility::is_valid_visibility_level($visibilityLevel))
                {
                    return false;
                }
            }

            foreach ($uploaders as $uploaderIDString)
            {
                if (!Resource\UserResourceID::from_string($uploaderIDString))
                {
                    return false;
                }
            }
        }

        $uploaderConditional = empty($uploaders)
                               ? "1"
                               : "creator_resource_id IN ('".implode("','", $uploaders)."')";

        $resourceVisibilityConditional = empty($visibilityLevels)
                                         ? "1"
                                         : "resource_visibility IN ('".implode("','", $visibilityLevels)."')";

        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_tracks
                                             WHERE {$resourceVisibilityConditional}
                                             AND {$uploaderConditional}");

        
        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return false;
        }
                                                
        return $dbResponse[0]["COUNT(*)"];
    }

    // Returns as an array of TrackResource elements the track resources that match
    // the given query string; or FALSE on error. The tracks will be sorted by
    // upload date in descending order.
    //
    // $visibilityLevels = an array of ResourceVisibility elements such that
    // only tracks whose visibility level is one of these will be included in
    // the return array
    //
    public function search(string $queryString,
                           array $visibilityLevels = [Resource\ResourceVisibility::PUBLIC])
    {
         // Assert that we received valid parameter values.
         {
            foreach ($visibilityLevels as $visibilityLevel)
            {
                if (!Resource\ResourceVisibility::is_valid_visibility_level($visibilityLevel))
                {
                    return false;
                }
            }
        }

        $resourceVisibilityConditional = empty($visibilityLevels)
                                         ? "1"
                                         : "resource_visibility IN ('".implode("','", $visibilityLevels)."')";
                                         
        $dbResponse = $this->issue_db_query("SELECT resource_id,
                                                    resource_visibility,
                                                    creator_resource_id,
                                                    creation_timestamp,
                                                    download_count,
                                                    track_name,
                                                    track_width,
                                                    track_height
                                             FROM rsc_tracks
                                             WHERE {$resourceVisibilityConditional}
                                             AND track_name LIKE CONCAT('%', ?, '%')
                                             ORDER BY creation_timestamp DESC",
                                             [$queryString]);

        // If the query failed.
        if (!is_array($dbResponse))
        {
            return false;
        }

        // Combine the discrete track variables to form track resource objects.
        $tracks = [];
        foreach ($dbResponse as $trackParameters)
        {
            // Verify that we have all the required parameters for a track resource.
            {
                if (!isset($trackParameters["track_name"]) ||
                    !isset($trackParameters["track_width"]) ||
                    !isset($trackParameters["creation_timestamp"]) ||
                    !isset($trackParameters["download_count"]) ||
                    !isset($trackParameters["resource_id"]) ||
                    !isset($trackParameters["creator_resource_id"]) ||
                    !isset($trackParameters["resource_visibility"]))
                {
                    continue;
                }
            }

            $trackData = new \RSC\RallySportEDTrackData();

            if (!$trackData->set_name($trackParameters["track_name"]) ||
                !$trackData->set_side_length($trackParameters["track_width"]))
            {
                return false;
            }
    
            $trackResource = Resource\TrackResource::with($trackData,
                                                          $trackParameters["creation_timestamp"],
                                                          $trackParameters["download_count"],
                                                          Resource\TrackResourceID::from_string($trackParameters["resource_id"]),
                                                          Resource\UserResourceID::from_string($trackParameters["creator_resource_id"]),
                                                          $trackParameters["resource_visibility"]);
    
            if (!$trackResource)
            {
                return false;
            }

            $tracks[] = $trackResource;
        }

        return $tracks;
    }
    
    // Returns one or more track resources as an array of TrackResource elements;
    // or FALSE on error. The tracks will be sorted by upload date in descending
    // order.
    //
    // $count = defines the number of tracks to return at most (if 0, all will
    // be returned)
    //
    // $offset = sets the starting offset in the full list of tracks from which
    // to extract the desired number of tracks
    //
    // $metadataOnly = sets whether to return only metadata (like track dimensions
    // and name) or all data (including the track's container and manifesto)
    //
    // $uploaders = an array of user ID strings such that if non-empty, only
    // tracks uploaded by these users will be included in the return array
    //
    // $visibilityLevels = an array of ResourceVisibility elements such that
    // only tracks whose visibility level is one of these will be included in
    // the return array
    //
    // $trackIDs = an array of track ID strings such that if non-empty, only
    // tracks whose resource IDs match these strings will be included in the
    // return array.
    //
    // $sort = sets the property by which to sort the tracks; but can only be
    // "timestamp" at this time.
    //
    public function get_tracks(int $count = 0,
                               int $offset = 0,
                               array $uploaders = [],
                               array $visibilityLevels = [Resource\ResourceVisibility::PUBLIC],
                               array $trackIDs = [],
                               bool $metadataOnly = true,
                               string $sort = "timestamp")
    {
        // Assert that we received valid parameter values.
        {
            if (($offset < 0) ||
                ($count < 0))
            {
                return false;
            }

            // We only support sorting by timestamp, for now.
            if ($sort !== "timestamp")
            {
                return false;
            }

            foreach ($visibilityLevels as $visibilityLevel)
            {
                if (!Resource\ResourceVisibility::is_valid_visibility_level($visibilityLevel))
                {
                    return false;
                }
            }

            foreach ($uploaders as $uploaderIDString)
            {
                if (!Resource\UserResourceID::from_string($uploaderIDString))
                {
                    return false;
                }
            }

            foreach ($trackIDs as $trackIDString)
            {
                if (!Resource\TrackResourceID::from_string($trackIDString))
                {
                    return false;
                }
            }
        }

        $uploaderConditional = empty($uploaders)
                               ? "1"
                               : "creator_resource_id IN ('".implode("','", $uploaders)."')";

        $resourceVisibilityConditional = empty($visibilityLevels)
                                         ? "1"
                                         : "resource_visibility IN ('".implode("','", $visibilityLevels)."')";

        $trackIDConditional = empty($trackIDs)
                              ? "1"
                              : "resource_id IN ('".implode("','", $trackIDs)."')";

        // A count of 0 will return all matching tracks.
        $limitConditional = ($count <= 0)
                            ? ""
                            : "LIMIT {$offset},{$count}";

        $dbResponse = $this->issue_db_query("SELECT resource_id,
                                                    resource_visibility,
                                                    creator_resource_id,
                                                    creation_timestamp,
                                                    download_count,
                                                    track_name,
                                                    track_width,
                                                    track_height".
                                                    ($metadataOnly? "" : ", track_container_gzip,
                                                                            track_manifesto_gzip")."
                                             FROM rsc_tracks
                                             WHERE {$resourceVisibilityConditional}
                                             AND {$uploaderConditional}
                                             AND {$trackIDConditional}
                                             ORDER BY creation_timestamp DESC
                                             {$limitConditional}");

        // If the query failed.
        if (!is_array($dbResponse))
        {
            return false;
        }

        // Combine the discrete track variables to form track resource objects.
        $tracks = [];
        foreach ($dbResponse as $trackParameters)
        {
            // Verify that we have all the required parameters for a track resource.
            {
                if (!isset($trackParameters["track_name"]) ||
                    !isset($trackParameters["track_width"]) ||
                    !isset($trackParameters["creation_timestamp"]) ||
                    !isset($trackParameters["download_count"]) ||
                    !isset($trackParameters["resource_id"]) ||
                    !isset($trackParameters["creator_resource_id"]) ||
                    !isset($trackParameters["resource_visibility"]))
                {
                    return false;
                }

                if (!$metadataOnly)
                {
                    if (!isset($trackParameters["track_container_gzip"]) ||
                        !isset($trackParameters["track_manifesto_gzip"]))
                    {
                        return false;
                    }
                }
            }

            $trackData = new \RSC\RallySportEDTrackData();

            if (!$trackData->set_name($trackParameters["track_name"]) ||
                !$trackData->set_side_length($trackParameters["track_width"]))
            {
                return false;
            }
    
            if (!$metadataOnly)
            {
                if (!$trackData->set_container(gzdecode($trackParameters["track_container_gzip"])) ||
                    !$trackData->set_manifesto(gzdecode($trackParameters["track_manifesto_gzip"])))
                {
                    return false;
                }
            }
    
            $trackResource = Resource\TrackResource::with($trackData,
                                                          $trackParameters["creation_timestamp"],
                                                          $trackParameters["download_count"],
                                                          Resource\TrackResourceID::from_string($trackParameters["resource_id"]),
                                                          Resource\UserResourceID::from_string($trackParameters["creator_resource_id"]),
                                                          $trackParameters["resource_visibility"]);
    
            if (!$trackResource)
            {
                return false;
            }

            $tracks[] = $trackResource;
        }

        if (!$metadataOnly)
        {
            foreach ($tracks as $track)
            {
                $this->increment_track_download_count($track->id());
            }
        }

        return $tracks;
    }

    // Adds into the TRACKS table a new track using the given track resource's
    // data. The 'resourceHash' parameter provides a hash of the track's data,
    // such that it can be used to detect attempts to upload duplicates of this
    // track. The 'kierrosSVGImage' parameter provides as a string an SVG image
    // representing the track's KIERROS data. Returns TRUE on success; FALSE
    // otherwise.
    public function add_new_track(Resource\TrackResource $track,
                                  string $kierrosSVGImage,
                                  string $resourceHash) : bool
    {
        if (!$this->is_connected())
        {
            return false;
        }

        if (!($compressedContainer = gzencode($track->data()->container(), 9, FORCE_GZIP)) ||
            !($compressedManifesto = gzencode($track->data()->manifesto(), 9, FORCE_GZIP)) ||
            !($compressedKierrosSVG = gzencode($kierrosSVGImage, 9, FORCE_GZIP)))
        {
            return false;
        }

        $databaseReturnValue = $this->issue_db_command(
                                 "INSERT INTO rsc_tracks
                                  (resource_id,
                                   resource_visibility,
                                   resource_data_hash_sha256,
                                   track_name,
                                   track_width,
                                   track_height,
                                   track_container_gzip,
                                   track_manifesto_gzip,
                                   kierros_svg_gzip,
                                   creation_timestamp,
                                   download_count,
                                   creator_resource_id)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                  [$track->id()->string(),
                                   $track->visibility(),
                                   $resourceHash,
                                   $track->data()->name(),
                                   $track->data()->side_length(),
                                   $track->data()->side_length(),
                                   $compressedContainer,
                                   $compressedManifesto,
                                   $compressedKierrosSVG,
                                   $track->creation_timestamp(),
                                   $track->download_count(),
                                   $track->creator_id()->string()]);

        return (($databaseReturnValue == 0)? true : false);
    }

    // Returns an SVG image (as a string) of the track's KIERROS data. On
    // error, returns false.
    public function get_track_svg(Resource\TrackResourceID $trackResourceID)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $dbResponse = $this->issue_db_query("SELECT kierros_svg_gzip
                                             FROM rsc_tracks
                                             WHERE resource_id = ?",
                                            [$trackResourceID->string()]);

        if (!is_array($dbResponse) || !count($dbResponse))
        {
            return false;
        }

        return gzdecode($dbResponse[0]["kierros_svg_gzip"]);
    }

    // Returns the number of times the given track has been downloaded; or FALSE
    // on error.
    public function get_track_download_count(Resource\TrackResourceID $trackID)
    {
        $queryResults = $this->issue_db_query("SELECT download_count
                                               FROM rsc_tracks
                                               WHERE resource_id = ?
                                               AND resource_visibility = ?",
                                              [$trackID->string(),
                                               Resource\ResourceVisibility::PUBLIC]);

        if (!is_array($queryResults) || count($queryResults) != 1)
        {
            return false;
        }

        return $queryResults[0]["download_count"];
    }
}

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

    public function __construct()
    {
        parent::__construct();

        return;
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
                                             WHERE resource_hash = ?",
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

    // Returns the number of tracks uploaded by the given user that are marked
    // as being publically viewable.
    public function num_public_tracks_by_user(Resource\UserResourceID $userResourceID)
    {
        $dbResponse = $this->issue_db_query("SELECT COUNT(*)
                                             FROM rsc_tracks
                                             WHERE creator_resource_id = ?
                                             AND resource_visibility = ?",
                                            [$userResourceID->string(),
                                             Resource\ResourceVisibility::PUBLIC]);

        if (!is_array($dbResponse) ||
            !count($dbResponse) ||
            !isset($dbResponse[0]["COUNT(*)"]))
        {
            return 0;
        }

        return $dbResponse[0]["COUNT(*)"];
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
                                                   resource_hash = NULL,
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

    // Adds into the TRACKS table a new track with the given parameters. Returns
    // TRUE on success; FALSE otherwise.
    public function add_new_track(Resource\TrackResourceID $resourceID,
                                  int /*\ResourceVisibility*/ $resourceVisibility,
                                  string $resourceHash,
                                  Resource\UserResourceID $creatorID,
                                  int $downloadCount,
                                  int $creationTimestamp,
                                  string $name,
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
                                   resource_hash,
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
                                  [$resourceID->string(),
                                   $resourceVisibility,
                                   $resourceHash,
                                   $name,
                                   $width,
                                   $height,
                                   $compressedContainer,
                                   $compressedManifesto,
                                   $compressedKierrosSVG,
                                   $creationTimestamp,
                                   $downloadCount,
                                   $creatorID->string()]);

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

    // Returns as an array of TrackResource elements all public tracks uploaded
    // by the given user. On error, returns FALSE.
    public function get_all_public_track_resources_uploaded_by_user(Resource\UserResourceID $userID,
                                                                    bool $metadataOnly = false)
    {
        $trackIDs = $this->get_ids_of_all_public_tracks_uploaded_by_user($userID);

        if (!is_array($trackIDs) || !count($trackIDs))
        {
            return false;
        }

        // Fetch the track data.
        $tracks = array_reduce($trackIDs, function($acc, $trackIDString) use ($metadataOnly)
        {
            if (($trackResource = $this->get_track_resource(Resource\TrackResourceID::from_string($trackIDString),
                                                            Resource\ResourceVisibility::PUBLIC,
                                                            $metadataOnly)))
            {
                $acc[] = $trackResource;
            }

            return $acc;
        }, []);

        if (count($tracks) !== count($trackIDs))
        {
            return false;
        }

        return $tracks;
    }
    
    // Returns the resource IDs (as an array of strings) of all public tracks
    // in the database. On error, returns FALSE.
    public function get_ids_of_all_public_tracks()
    {
        return $this->get_ids_of_all_public_tracks_uploaded_by_user(NULL);
    }

    // Returns the resource IDs (as an array of strings) of all public tracks
    // uploaded by the given user, or all users if NULL. On error, returns FALSE.
    public function get_ids_of_all_public_tracks_uploaded_by_user(Resource\UserResourceID $userResourceID = NULL)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        $queryResults = $this->issue_db_query("SELECT resource_id
                                               FROM rsc_tracks
                                               WHERE creator_resource_id LIKE ?
                                               AND resource_visibility = ?",
                                              [($userResourceID? $userResourceID->string() : "%"),
                                               Resource\ResourceVisibility::PUBLIC]);

        if (!is_array($queryResults) || !count($queryResults))
        {
            return false;
        }

        return array_reduce($queryResults, function($acc, $element)
        {
            $acc[] = $element["resource_id"];
            return $acc;
        }, []);
    }

    // Returns as an array of TrackResource elements all public tracks in the
    // database. On error, returns FALSE.
    public function get_all_public_track_resources(bool $metadataOnly = false)
    {
        $trackIDs = $this->get_ids_of_all_public_tracks();

        if (!is_array($trackIDs) || !count($trackIDs))
        {
            return false;
        }

        // Fetch the track data.
        $tracks = array_reduce($trackIDs, function($acc, $trackIDString) use ($metadataOnly)
        {
            if (($trackResource = $this->get_track_resource(Resource\TrackResourceID::from_string($trackIDString),
                                                            Resource\ResourceVisibility::PUBLIC,
                                                            $metadataOnly)))
            {
                $acc[] = $trackResource;
            }

            return $acc;
        }, []);

        if (count($tracks) !== count($trackIDs))
        {
            return false;
        }

        return $tracks;
    }

    // Returns the given track's data as a TrackResource object. The given
    // visibility level must match the actual visibility level of the track in
    // the database; or an error will be returned. If $metadataOnly is true, only
    // metadata will be included in the return object; excluding things like
    // the track's container. On error, returns FALSE.
    public function get_track_resource(Resource\TrackResourceID $trackResourceID = NULL,
                                       int /*Resource\ResourceVisibility*/ $expectedVisibility = Resource\ResourceVisibility::PUBLIC,
                                       bool $metadataOnly = false)
    {
        if (!$this->is_connected())
        {
            return false;
        }

        if (!$trackResourceID)
        {
            return false;
        }

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
                                             WHERE resource_id = ?
                                             AND resource_visibility = ?",
                                            [$trackResourceID->string(),
                                             $expectedVisibility]);

        // Track resource IDs should be unique, so we should find no more than
        // one element in the response array (or 0 elements if the ID doesn't
        // exist).
        if (!is_array($dbResponse) || count($dbResponse) != 1)
        {
            return false;
        }

        // We expect tracks to be square.
        if ($dbResponse[0]["track_width"] !== $dbResponse[0]["track_height"])
        {
            return false;
        }

        $rsedTrack = new \RSC\RallySportEDTrackData();

        if (!$rsedTrack->set_name($dbResponse[0]["track_name"]) ||
            !$rsedTrack->set_side_length($dbResponse[0]["track_width"]))
        {
            return false;
        }

        if (!$metadataOnly)
        {
            if (!$rsedTrack->set_container(gzdecode($dbResponse[0]["track_container_gzip"])) ||
                !$rsedTrack->set_manifesto(gzdecode($dbResponse[0]["track_manifesto_gzip"])))
            {
                return false;
            }

            $this->increment_track_download_count($trackResourceID);
        }

        $trackResource = Resource\TrackResource::with($rsedTrack,
                                                      $dbResponse[0]["creation_timestamp"],
                                                      $dbResponse[0]["download_count"],
                                                      Resource\TrackResourceID::from_string($dbResponse[0]["resource_id"]),
                                                      Resource\UserResourceID::from_string($dbResponse[0]["creator_resource_id"]),
                                                      $dbResponse[0]["resource_visibility"]);

        if (!$trackResource)
        {
            return false;
        }

        return $trackResource;
    }
}

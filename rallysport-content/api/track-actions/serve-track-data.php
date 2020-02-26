<?php namespace RSC\API\Tracks;
      use RSC\DatabaseConnection;
      use RSC\API;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * track or a set of tracks to the client.
 * 
 */

require_once __DIR__."/../response.php";
require_once __DIR__."/../common-scripts/resource/resource.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";
require_once __DIR__."/../common-scripts/database-connection/track-database.php";

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
function serve_track_data_as_zip_file(Resource\TrackResourceID $trackResourceID = NULL) : void
{
    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we only support serving individual tracks' data.
    if (!$trackResourceID)
    {
        exit(API\Response::code(400)->error_message("A track ID must be provided."));
    }

    $targetTrackIDs = ($trackResourceID? [$trackResourceID->string()] : []);
    $targetVisibilityLevels = [Resource\ResourceVisibility::PUBLIC];
    $targetUploaderIDs = [];
    $tracks = (new DatabaseConnection\TrackDatabase())->get_tracks(0,
                                                                   0,
                                                                   $targetUploaderIDs,
                                                                   $targetVisibilityLevels,
                                                                   $targetTrackIDs,
                                                                   false);

    if (!is_array($tracks) || !count($tracks) || !$tracks[0])
    {
        exit(API\Response::code(404)->error_message("No matching tracks found."));
    }

    // Build a RallySportED Loader-compatible zip archive out of the track's
    // data.
    $zipArchive = new \RSC\ZipFile();
    {
        $trackName = strtoupper($tracks[0]->data()->name());
        $fileTimestamp = time();

        // We'll include Rally-Sport's default HITABLE.TXT file.
        if (!($hitableData = file_get_contents(__DIR__."/../../tracks/server-data/HITABLE.TXT")))
        {
            exit(API\Response::code(500)->error_message("Internal server error."));
        }

        $zipArchive->add_file("{$trackName}/{$trackName}.DTA",
                              $tracks[0]->data()->container(),
                              $fileTimestamp);

        $zipArchive->add_file("{$trackName}/{$trackName}.\$FT",
                              $tracks[0]->data()->manifesto(),
                              $fileTimestamp);

        $zipArchive->add_file("{$trackName}/HITABLE.TXT",
                              $hitableData,
                              $fileTimestamp);
    }

    // We ask the client to cache the response data only if they are for a
    // single track - otherwise, when new tracks are added, they would not
    // show up in the cached response.
    exit(API\Response::code(200)->binary_file("{$trackName}.ZIP",
                                              $zipArchive->string(),
                                              ($trackResourceID? 2592000 : 0)));
}

// Prints into the PHP output stream a stringified JSON object containing the
// track's data.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
function serve_track_data_as_json(string /*ResourceViewType*/ $viewType,
                                  Resource\TrackResourceID $trackResourceID = NULL) : void
{
    $metadataOnly = (strpos($viewType, "metadata") !== FALSE);

    // A NULL resource ID indicates that we should serve the data for all known
    // tracks. However, for now, we can only serve metadata if requested for all
    // tracks at once, not the full track data.
    if (!$metadataOnly && !$trackResourceID)
    {
        exit(API\Response::code(400)->error_message("A track ID must be provided."));
    }

    $targetTrackIDs = ($trackResourceID? [$trackResourceID->string()] : []);
    $targetVisibilityLevels = [Resource\ResourceVisibility::PUBLIC];
    $targetUploaderIDs = [];
    $tracks = (new DatabaseConnection\TrackDatabase())->get_tracks(0,
                                                                   0,
                                                                   $targetUploaderIDs,
                                                                   $targetVisibilityLevels,
                                                                   $targetTrackIDs,
                                                                   $metadataOnly);

    if (!is_array($tracks) || !count($tracks) || !$tracks[0])
    {
        exit(API\Response::code(404)->error_message("No matching tracks found."));
    }

    // Massage the data so its output is in the desired format.
    $tracksMassaged = array_reduce($tracks, function($acc, $element) use ($viewType)
    {
        $acc[] = $element->view($viewType);
        return $acc;
    }, []);

    // We ask the client to cache the response data only if they are for a
    // single track - otherwise, when new tracks are added, they would not
    // show up in the cached response.
    exit(API\Response::code(200)->json($tracksMassaged, ($trackResourceID? 2592000 : 0)));
}

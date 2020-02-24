<?php namespace RSC\API\Tracks;
      use RSC\DatabaseConnection;
      use RSC\API;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to add a new track into RSC's database.
 * 
 */

require_once __DIR__."/../response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";
require_once __DIR__."/../common-scripts/rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../common-scripts/database-connection/track-database.php";
require_once __DIR__."/../common-scripts/svg-image-from-kierros-data.php";
require_once __DIR__."/../common-scripts/is-valid-uploaded-file.php";
require_once __DIR__."/../session.php";

// Takes in a $_FILES[][...] array describing an uploaded file whose data should
// be added into the database as a new track. We expect the file to be a RallySportED
// track ZIP file, but will also attempt to verify its validity as such.
//
// Note: This function should always return using exit() together with a Response
// object.
//
function add_new_track(array $uploadedFileInfo) : void
{
    if (!API\Session\is_client_logged_in())
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=add&error=Must be logged in to add a track"));
    }

    if (!$uploadedFileInfo ||
        !\RSC\is_valid_uploaded_file($uploadedFileInfo, \RSC\RallySportEDTrackData::MAX_BYTE_SIZE))
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=add&error=Invalid track file"));
    }

    $newTrack = Resource\TrackResource::with(\RSC\RallySportEDTrackData::from_zip_file($uploadedFileInfo["tmp_name"]),
                                             time(),
                                             0,
                                             Resource\TrackResourceID::random(),
                                             API\Session\logged_in_user_id(),
                                             Resource\ResourceVisibility::PUBLIC);

    if (!$newTrack)
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=add&error=Incompatible track data"));
    }

    if (!$newTrack->data()->set_display_name($_POST["track_display_name"] ?? NULL))
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=add&error=Invalid track title"));
    }

    /// TODO: Test to make sure the track's name is unique in the TRACKS table.

    if (!(new DatabaseConnection\TrackDatabase())->add_new_track(
                                                    $newTrack->id(),
                                                    $newTrack->visibility(),
                                                    $newTrack->creator_id(),
                                                    $newTrack->download_count(),
                                                    $newTrack->creation_timestamp(),
                                                    $newTrack->data()->internal_name(),
                                                    $newTrack->data()->display_name(),
                                                    $newTrack->data()->side_length(),
                                                    $newTrack->data()->side_length(),
                                                    $newTrack->data()->container(),
                                                    $newTrack->data()->manifesto(),
                                                    \RSC\svg_image_from_kierros_data($newTrack->data()->container("kierros"),
                                                                                     $newTrack->data()->side_length())))
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=add&error=Database error"));
    }

    // Successfully added.
    exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?id={$newTrack->id()->string()}"));
}

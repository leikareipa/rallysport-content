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
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=add",
                                                           "Must be logged in to add a track"));
    }

    if (!$uploadedFileInfo ||
        !\RSC\is_valid_uploaded_file($uploadedFileInfo, \RSC\RallySportEDTrackData::MAX_BYTE_SIZE))
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=add",
                                                           "Invalid track file"));
    }

    $newTrack = Resource\TrackResource::with(\RSC\RallySportEDTrackData::from_zip_file($uploadedFileInfo["tmp_name"]),
                                             time(),
                                             0,
                                             Resource\TrackResourceID::random(),
                                             API\Session\logged_in_user_id(),
                                             Resource\ResourceVisibility::PUBLIC);

    if (!$newTrack)
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=add",
                                                           "Incompatible track data"));
    }

    // All uploaded tracks should be unique wrt. the tracks currently in the
    // database; so verify that a track too similar hasn't already been
    // uploaded.
    {
        $trackDataHash = DatabaseConnection\TrackDatabase::hash_of_track_data($newTrack);

        // Hashing the data would fail if the track resource instance contains
        // only metadata. That shouldn't be the case in the instances we've
        // got, but...
        if (!$trackDataHash)
        {
            exit(API\Response::code(500)->error_message("Internal server error. Track upload failed."));
        }

        if (!(new DatabaseConnection\TrackDatabase())->is_resource_hash_unique($trackDataHash))
        {
            exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=add",
                                                               "A track too similar to that has already been uploaded"));
        }

        if (!(new DatabaseConnection\TrackDatabase())->is_track_name_unique($newTrack->data()->name()))
        {
            exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=add",
                                                               "A track by that name has already been uploaded"));
        }
    }

    // Create an image that represents the track's shape. This'll be displayed
    // in HTML views of the track.
    $kierrosSVGImage = \RSC\svg_image_from_kierros_data($newTrack->data()->container("kierros"),
                                                        $newTrack->data()->side_length());

    if (!(new DatabaseConnection\TrackDatabase())->add_new_track($newTrack,
                                                                 $kierrosSVGImage,
                                                                 $trackDataHash))
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=add",
                                                           "Database error"));
    }

    // Successfully added.
    exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=new-track-uploaded&id={$newTrack->id()->string()}"));
}

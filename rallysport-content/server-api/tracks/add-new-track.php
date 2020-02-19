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

require_once __DIR__."/../../server-api/response.php";
require_once __DIR__."/../../common-scripts/resource/resource-id.php";
require_once __DIR__."/../../common-scripts/database-connection/track-database.php";
require_once __DIR__."/../../common-scripts/svg-image-from-kierros-data.php";
require_once __DIR__."/../../server-api/session.php";

// Attempts to add to the Rally-Sport Content database a new track, whose data
// are specified by the function call parameters.
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On success, returns the HTML status code 201 without a body.
//
// Note: The function should always return using exit() together with a Response
// object.
//
function add_new_track(Resource\TrackResource $track) : void
{
    /// TODO: Test to make sure the track's name is unique in the TRACKS table.

    if (!(new DatabaseConnection\TrackDatabase())->add_new_track(
                                                    $track->id(),
                                                    $track->visibility(),
                                                    $track->creator_id(),
                                                    $track->data()->internal_name(),
                                                    $track->data()->display_name(),
                                                    $track->data()->side_length(),
                                                    $track->data()->side_length(),
                                                    $track->data()->container(),
                                                    $track->data()->manifesto(),
                                                    \RSC\svg_image_from_kierros_data($track->data()->container("kierros"),
                                                                                     $track->data()->side_length())))
    {
        exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?form=add&error=Database error"));
    }

    exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/?id={$track->id()->string()}"));
}

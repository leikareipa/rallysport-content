<?php namespace RSC\API\Tracks;
      use RSC\DatabaseConnection;
      use RSC\API;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script attempts to delete a track from RSC's database.
 * 
 */

require_once __DIR__."/../response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";
require_once __DIR__."/../common-scripts/database-connection/track-database.php";
require_once __DIR__."/../session.php";

function delete_track(Resource\TrackResourceID $resourceID) : void
{
    if (!$resourceID)
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=delete&id={$resourceID->string()}",
                                                           "Invalid track resource ID"));
    }

    if (!API\Session\is_client_logged_in())
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=delete&id={$resourceID->string()}",
                                                           "Must be logged in to delete a track"));
    }

    $trackResource = Resource\TrackResource::from_database($resourceID->string(), true);

    if (!$trackResource)
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=delete&id={$resourceID->string()}",
                                                           "Invalid track resource"));
    }

    // Only the user who uploaded the track can delete it.
    if ($trackResource->creator_id()->string() !== API\Session\logged_in_user_id()->string())
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=delete&id={$resourceID->string()}",
                                                           "Your account is not authorized to delete this track"));
    }

    if (!(new DatabaseConnection\TrackDatabase())->delete_track($trackResource->id()))
    {
        exit(API\Response::code(303)->load_form_with_error("/rallysport-content/tracks/?form=delete&id={$resourceID->string()}",
                                                           "Database error"));
    }

    // Successfully deleted.
    exit(API\Response::code(303)->redirect_to("/rallysport-content/tracks/"));
}

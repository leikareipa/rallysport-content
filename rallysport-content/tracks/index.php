<?php namespace RSC;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs TRACK-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/../api/page-dispatch/pages/form/form.php";
require_once __DIR__."/../api/page-dispatch/pages/tracks/all-public-tracks.php";
require_once __DIR__."/../api/page-dispatch/pages/tracks/specific-public-track.php";
require_once __DIR__."/../api/page-dispatch/pages/tracks/public-tracks-uploaded-by-user.php";
require_once __DIR__."/../api/track-actions/add-new-track.php";
require_once __DIR__."/../api/track-actions/delete-track.php";
require_once __DIR__."/../api/track-actions/serve-track-data.php";
require_once __DIR__."/../api/response.php";
require_once __DIR__."/../api/common-scripts/resource/resource-id.php";
require_once __DIR__."/../api/common-scripts/resource/resource-visibility.php";
require_once __DIR__."/../api/common-scripts/resource/resource-view-url-params.php";

require_once __DIR__."/../api/page-dispatch/pages/form/forms/unknown-form-identifier.php";
require_once __DIR__."/../api/page-dispatch/pages/form/forms/add-track.php";
require_once __DIR__."/../api/page-dispatch/pages/form/forms/new-track-uploaded.php";
require_once __DIR__."/../api/page-dispatch/pages/form/forms/delete-track.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        $resourceID = NULL;

        // Find which track we're requested to operate on. If no track ID is
        // provided, we assume the query relates to all tracks in the database.
        if (Resource\ResourceViewURLParams::target_id())
        {
            $resourceID = Resource\TrackResourceID::from_string(Resource\ResourceViewURLParams::target_id());

            if (!$resourceID)
            {
                exit(API\Response::code(400)->error_message("Invalid track resource ID."));
            }
        }

        // Satisfy the GET request by outputting the relevant data.
        if ($_GET["form"] ?? false)
        {
            switch ($_GET["form"])
            {
                case "add":
                {
                    if (!API\Session\is_client_logged_in())
                    {
                        API\Response::code(303)->redirect_to("/rallysport-content/?form=login");
                    }
                    else
                    {
                        $page = API\BuildPage\form(API\Form\AddTrack::class);
                        exit(API\Response::code(200)->html($page->html()));
                    }

                    break;
                }
                case "delete":
                {
                    if (!API\Session\is_client_logged_in())
                    {
                        exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=login"));
                    }
                    else
                    {
                        $page = API\BuildPage\form(API\Form\DeleteTrack::class);
                        exit(API\Response::code(200)->html($page->html()));
                    }

                    break;
                }
                case "new-track-uploaded":
                {
                    $page = API\BuildPage\form(API\Form\NewTrackUploaded::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
                default:
                {
                    $page = API\BuildPage\form(API\Form\UnknownFormIdentifier::class);
                    exit(API\Response::code(200)->html($page->html()));
                }
            }
        }
        else if ($_GET["zip"] ?? false)
        {
            API\Tracks\serve_track_data_as_zip_file($resourceID);
        }
        else if ($_GET["json"] ?? false)
        {
            API\Tracks\serve_track_data_as_json("data-array", $resourceID);
        }
        else if ($_GET["metadata"] ?? false)
        {
            API\Tracks\serve_track_data_as_json("metadata-array", $resourceID);
        }
        else // Provide a HTML view into the track data.
        {
            if (Resource\ResourceViewURLParams::creator_id())
            {
                $uploaderId = Resource\UserResourceID::from_string(Resource\ResourceViewURLParams::creator_id());
                $page = API\BuildPage\Tracks\public_tracks_uploaded_by_user($uploaderId);
                exit(API\Response::code(200)->html($page->html()));
            }
            else if (Resource\ResourceViewURLParams::target_id())
            {
                $trackId = Resource\TrackResourceID::from_string(Resource\ResourceViewURLParams::target_id());
                $page = API\BuildPage\Tracks\specific_public_track($trackId);
                exit(API\Response::code(200)->html($page->html()));
            }
            else
            {
                $page = API\BuildPage\Tracks\all_public_tracks();
                exit(API\Response::code(200)->html($page->html()));
            }
        }

        break;
    }
    case "DELETE":
    {
        API\Tracks\delete_track(Resource\TrackResourceID::from_string(Resource\ResourceViewURLParams::target_id()));

        break;
    }
    case "POST":
    {
        API\Tracks\add_new_track($_FILES["rallysported_track_file"] ?? NULL);

        break;
    }
    default:
    {
        exit(API\Response::code(405)->allowed("GET, HEAD, POST, DELETE"));
    }
}

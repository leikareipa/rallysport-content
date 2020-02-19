<?php namespace RSC\API\Tracks;
      use RSC\HTMLPage;
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

require_once __DIR__."/../../server-api/response.php";
require_once __DIR__."/../../common-scripts/resource/resource-id.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/track-metadata.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/track-metadata-container.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides metadata about the requested track, identified by the
// 'trackResourceID' parameter (or of all public tracks in the database if
// this parameter is NULL).
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
//  - On success, the response body will consist of the HTML page's source
//    code as a string.
//
function view_track(Resource\TrackResourceID $trackResourceID = NULL) : void
{
    $tracks = ($trackResourceID? [(new DatabaseConnection\TrackDatabase())->get_track_resource($trackResourceID, true)]
                               : (new DatabaseConnection\TrackDatabase())->get_all_public_track_resources(true));

    if (!is_array($tracks) || !count($tracks) || !$tracks[0])
    {
        exit(API\Response::code(404)->error_message("No matching tracks found."));
    }

    // Build a HTML page that displays the requested tracks' metadata.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadata::class);

        if (count($tracks) == 1)
        {
            $creatorID = $tracks[0]->creator_id()->string();
            $plainTextTitle = "A track uploaded by {$creatorID}";
            $htmlTitle = "
            A track uploaded by
            <a href='/rallysport-content/users/?id={$creatorID}'>
                {$creatorID}
            </a>";
        }
        else
        {
            $plainTextTitle = $htmlTitle = "A random selection of tracks uploaded by users";
        }

        $htmlPage->head->title = $plainTextTitle;
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::open($htmlTitle));
        foreach ($tracks as $trackResource)
        {
            if (!$trackResource)
            {
                exit(API\Response::code(404)->error_message("An error occurred while processing track data."));
            }

            $htmlPage->body->add_element($trackResource->view("metadata-html"));
        }
        $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::close());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($htmlPage->html()));
}

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides metadata about all public tracks uploaded by the given
// user.
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
//  - On success, the response body will consist of the HTML page's source
//    code as a string.
//
function view_user_tracks(Resource\UserResourceID $userResourceID) : void
{
    $tracks = (new DatabaseConnection\TrackDatabase())->get_all_public_track_resources_uploaded_by_user($userResourceID, true);

    if (!is_array($tracks) || !count($tracks))
    {
        exit(API\Response::code(404)->error_message("No matching tracks found."));
    }

    // Build a HTML page that displays the requested tracks' metadata.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadata::class);

        if (count($tracks) == 1)
        {
            $plainTextTitle = "A track uploaded by {$userResourceID->string()}";
            $htmlTitle = "
            A track uploaded by
            <a href='/rallysport-content/users/?id={$userResourceID->string()}'>
                {$userResourceID->string()}
            </a>";
        }
        else
        {
            $plainTextTitle = "Tracks uploaded by ".$userResourceID->string();
            $htmlTitle = "
            Tracks uploaded by
            <a href='/rallysport-content/users/?id={$userResourceID->string()}'>
                {$userResourceID->string()}
            </a>";
        }

        $htmlPage->head->title = $plainTextTitle;
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::open($htmlTitle));
        foreach ($tracks as $trackResource)
        {
            if (!$trackResource)
            {
                exit(API\Response::code(404)->error_message("An error occurred while fetching track data."));
            }

            $htmlPage->body->add_element($trackResource->view("metadata-html"));
        }
        $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::close());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($htmlPage->html()));
}

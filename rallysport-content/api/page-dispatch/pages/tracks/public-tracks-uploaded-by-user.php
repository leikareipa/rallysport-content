<?php namespace RSC\API\PageDisplay\Tracks;
      use RSC\DatabaseConnection;
      use RSC\HTMLPage;
      use RSC\Resource;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../response.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/track-metadata.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/track-metadata-container.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides a listing of all the public tracks in the database uploaded
// by a given user.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->html(...).
//
function public_tracks_uploaded_by_user(Resource\UserResourceID $userResourceID) : void
{
    if (!$userResourceID)
    {
        exit(API\Response::code(404)->error_message("Invalid user ID."));
    }

    // Note: The page only displays track metadata, so we request that the
    // database sends metadata only.
    $tracks = (new DatabaseConnection\TrackDatabase())->get_all_public_track_resources_uploaded_by_user($userResourceID, true);

    if (!is_array($tracks) || !count($tracks))
    {
        exit(API\Response::code(404)->error_message("No matching tracks found."));
    }

    $totalTrackCount = count($tracks);

    // We'll display at most 20 tracks, in random order.
    shuffle($tracks);
    $tracks = array_slice($tracks, 0, 20);

    // Build a HTML page that lists the requested tracks.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadata::class);

        $htmlPage->head->title = "Tracks";
        $containerTitle =
        "
        Tracks uploaded by
        <a href='/rallysport-content/users/?id={$userResourceID->string()}'>
            <i class='far fa-fw fa-sm fa-user'></i>{$userResourceID->string()}
        </a>
        ";
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());
        $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::open($containerTitle));
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

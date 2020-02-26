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
// The page displays information about a specific public track in the database.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->html(...).
//
function specific_public_track(Resource\TrackResourceID $trackResourceID) : void
{
    if (!$trackResourceID)
    {
        exit(API\Response::code(404)->error_message("Invalid track ID."));
    }

    // Note: The page only displays track metadata, so we request that the
    // database sends metadata only.
    $tracks = (new DatabaseConnection\TrackDatabase())->get_tracks(0,
                                                                   0,
                                                                   [],
                                                                   [Resource\ResourceVisibility::PUBLIC],
                                                                   [$trackResourceID->string()],
                                                                   true);

    // If the database query failed.
    if (!is_array($tracks) || (count($tracks) !== 1))
    {
        $track = NULL;
    }
    else
    {
        $track = $tracks[0];
    }

    // Build a HTML page that displays the requested track.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackMetadata::class);
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());
        if (!$track)
        {
            $htmlPage->body->add_element("<div>No such track found</div>");
        }
        else
        {
            $htmlPage->head->title = "Tracks";
            $inPageTitle =
            "
            A track uploaded by
            <a href='/rallysport-content/users/?id={$track->creator_id()->string()}'>
                <i class='far fa-fw fa-sm fa-user'></i>{$track->creator_id()->string()}
            </a>
            ";

            $htmlPage->body->add_element("<div style='margin: 30px;'>{$inPageTitle}</div>");
            $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::open());
            $htmlPage->body->add_element($track->view("metadata-html"));
            $htmlPage->body->add_element(HTMLPage\Component\TrackMetadataContainer::close());
        }
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($htmlPage->html()));
}

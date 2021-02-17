<?php namespace RSC\API\BuildPage\Tracks;
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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/track-resource-metadata.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/resource-metadata-container.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/resource-page-number-selector.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page provides a listing of all the public tracks in the database.
function all_public_tracks() : HTMLPage\HTMLPage
{
    $trackDB = new DatabaseConnection\TrackDatabase();

    // We'll query the database for tracks by this uploader and with this
    // visibility.
    $uploaderConditional = [/*Empty, so query for all uploaders.*/];
    $visibilityConditional = [Resource\ResourceVisibility::PUBLIC];

    // The track view is split into sub-pages, where each sub-page displays n
    // tracks.
    $totalTrackCount = $trackDB->tracks_count($uploaderConditional, $visibilityConditional);
    $numPages = ceil($totalTrackCount / Resource\ResourceViewURLParams::items_per_page());
    $startIdx = (min(($numPages - 1), Resource\ResourceViewURLParams::page_number()) * Resource\ResourceViewURLParams::items_per_page());
    
    $tracks = $trackDB->get_tracks(Resource\ResourceViewURLParams::items_per_page(),
                                   $startIdx,
                                   $uploaderConditional,
                                   $visibilityConditional);

    // If we either failed to fetch track data, or there was none to fetch.
    if (!is_array($tracks))
    {
        $tracks = [];
    }

    // Build a HTML page that lists the requested tracks.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\ResourcePageNumberSelector::class);
        $htmlPage->use_component(HTMLPage\Component\ResourceMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackResourceMetadata::class);

        $htmlPage->head->title = "Tracks";
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        if (empty($tracks))
        {
            $htmlPage->body->add_element("<div>No tracks found</div>");
        }
        else
        {
            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::open());

            foreach ($tracks as $trackResource)
            {
                if (!$trackResource)
                {
                    exit(API\Response::code(404)->error_message("An error occurred while processing track data."));
                }
                else
                {
                    $htmlPage->body->add_element($trackResource->view("metadata-html"));
                }
            }

            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::close());
            $htmlPage->body->add_element(HTMLPage\Component\ResourcePageNumberSelector::html($totalTrackCount));
        }
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

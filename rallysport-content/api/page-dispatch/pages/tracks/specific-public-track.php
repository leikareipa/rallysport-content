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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page displays information about a specific public track in the database.
function specific_public_track(Resource\TrackResourceID $trackResourceID) : HTMLPage\HTMLPage
{
    if (!$trackResourceID)
    {
        exit(API\Response::code(404)->error_message("Invalid track ID."));
    }

    // We'll query the database for a specific public track.
    $visibilityConditional = [Resource\ResourceVisibility::PUBLIC];
    $userIDConditional = [$trackResourceID->string()];

    // Note: The page only displays track metadata, so we request that the
    // database sends metadata only.
    $tracks = (new DatabaseConnection\TrackDatabase())->get_tracks(0,
                                                                   0,
                                                                   [],
                                                                   $visibilityConditional,
                                                                   $userIDConditional,
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
        $htmlPage->use_component(HTMLPage\Component\ResourceMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackResourceMetadata::class);
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        if (!$track)
        {
            $htmlPage->body->add_element("<div>No such track found.</div>");
        }
        else
        {
            $htmlPage->head->title = $track->data()->name();

            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::open());
            $htmlPage->body->add_element($track->view("metadata-html"));
            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::close());
        }
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

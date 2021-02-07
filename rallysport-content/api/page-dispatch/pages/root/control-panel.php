<?php namespace RSC\API\BuildPage\Root;
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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/own-uploaded-tracks-list.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page provides a control panel with which the (logged-in) user can access
// restricted features of Rally-Sport Content, like creating and modifying
// resources.
function control_panel() : HTMLPage\HTMLPage
{
    if (!($loggedInUserID = API\Session\logged_in_user_id()))
    {
        exit(API\Response::code(404)->error_message("Invalid user session."));
    }

    // Build a HTML page that displays the control panel.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\OwnUploadedTracksList::class);

        $htmlPage->head->title = "Home";
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());

        // Build a table that displays the tracks the user has uploaded.
        {
            $trackDB = new DatabaseConnection\TrackDatabase();

            $unprocessedTracks = $trackDB->get_tracks(0, 0, [$loggedInUserID->string()], [Resource\ResourceVisibility::PROCESSING]);
            $publicTracks = $trackDB->get_tracks(0, 0, [$loggedInUserID->string()], [Resource\ResourceVisibility::PUBLIC]);

            $tracks = array_merge(($publicTracks? $publicTracks : []),
                                  ($unprocessedTracks? $unprocessedTracks : []));

            $htmlPage->body->add_element(HTMLPage\Component\OwnUploadedTracksList::html($tracks));
        }

        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

<?php namespace RSC\API\PageDisplay\Root;
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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides a control panel with which the (logged-in) user can access
// restricted features of Rally-Sport Content, like creating and modifying
// resources.
//
// Note: This function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
function control_panel() : void
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
        $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);
        $htmlPage->use_component(HTMLPage\Component\OwnUploadedTracksList::class);

        $htmlPage->head->title = "Home";
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());

        // Build a table that displays the tracks the user has uploaded.
        {
            $tracks = (new DatabaseConnection\TrackDatabase())->get_all_public_track_resources_uploaded_by_user($loggedInUserID, true);

            if (!is_array($tracks) || !count($tracks))
            {
                exit(API\Response::code(404)->error_message("No matching tracks found."));
            }

            $htmlPage->body->add_element(HTMLPage\Component\OwnUploadedTracksList::html($tracks));
        }

        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($htmlPage->html()));
}

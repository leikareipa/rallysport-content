<?php namespace RSC\API\Root;
      use RSC\HTMLPage;
      use RSC\DatabaseConnection;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../server-api/response.php";
require_once __DIR__."/../../common-scripts/resource/resource-id.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/track-metadata.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/track-metadata-container.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/own-uploaded-tracks-list.php";
require_once __DIR__."/../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides a control panel with which the (logged-in) user can access
// restricted features of Rally-Sport Content, like uploading a new track or
// modifying an existing resource they've uploaded.
//
// Note: This function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
function view_control_panel() : void
{
    if (!($loggedInUserID = API\Session\logged_in_user_id()))
    {
        exit(API\Response::code(404)->error_message("Invalid user session."));
    }

    // Build a HTML page that displays the control panel.
    {
        $view = new HTMLPage\HTMLPage();

        $view->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $view->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $view->use_component(HTMLPage\Component\OwnUploadedTracksList::class);

        $view->head->title = "Control panel";
        
        $view->body->add_element(HTMLPage\Component\RallySportContentHeader::html());

        // Build a table that displays the tracks the user has uploaded.
        {
            $tracksMetadata = (new DatabaseConnection\TrackDatabase())->get_user_tracks_metadata($loggedInUserID);
            if (!$tracksMetadata || !is_array($tracksMetadata) || !count($tracksMetadata))
            {
                $tracksMetadata = [];
            }

            $view->body->add_element(HTMLPage\Component\OwnUploadedTracksList::html($tracksMetadata));
        }

        $view->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($view->html()));
}

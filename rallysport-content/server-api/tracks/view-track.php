<?php namespace RSC\API\Tracks;
      use RSC\HTMLPage;
      use RSC\DatabaseConnection;
      use RSC\API;

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
function view_track_metadata(\RSC\TrackResourceID $trackResourceID = NULL) : void
{
    // Used - optionally - to specify whether we should limit the tracks we
    // show to only those whose uploader is identified by this user resource
    // ID. Will be automatically assigned depending on whether the "by" URL
    // parameter is present.
    $uploaderResourceID = NULL;
    
    // The presence of the "by" parameter indicates that we should only display
    // tracks that were uploaded by the user whose resource ID is provided as
    // the parameter's value.
    if ($_GET["by"])
    {
        $trackResourceID = NULL; // Indicate that we want all tracks by this user.
        $uploaderResourceID = \RSC\UserResourceID::from_string($_GET["by"]);

        if (!$uploaderResourceID)
        {
            exit(API\Response::code(404)->error_message("Invalid user ID."));
        }
    }

    $trackInfo = (new DatabaseConnection\TrackDatabase())->get_track_metadata($trackResourceID, $uploaderResourceID);
    if (!$trackInfo || !is_array($trackInfo) || !count($trackInfo))
    {
        exit(API\Response::code(404)->error_message("No matching track data found."));
    }

    // Build a HTML page that displays the requested tracks' metadata.
    {
        $view = new HTMLPage\HTMLPage();

        $view->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $view->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $view->use_component(HTMLPage\Component\TrackMetadataContainer::class);
        $view->use_component(HTMLPage\Component\TrackMetadata::class);

        if (count($trackInfo) == 1)
        {
            $userId = ($trackInfo[0]["creatorID"] ?? "an unknown user");
            
            $plainTextTitle = "A track uploaded by {$userId}";
            $htmlTitle = "A track uploaded by <a href='/rallysport-content/users/?id={$userId}'>{$userId}</a>";
        }
        else if ($_GET["by"])
        {
            $plainTextTitle = "All tracks uploaded by ".$uploaderResourceID->string();
            $htmlTitle = "
            All tracks uploaded by
            <a href='/rallysport-content/users/?id={$uploaderResourceID->string()}'>
                {$uploaderResourceID->string()}
            </a>
            ";
        }
        else
        {
            $plainTextTitle = $htmlTitle = "A random selection of tracks uploaded by users";
        }

        $view->head->title = $plainTextTitle;
        
        $view->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $view->body->add_element(HTMLPage\Component\TrackMetadataContainer::open($htmlTitle));
        foreach ($trackInfo as $track) $view->body->add_element(HTMLPage\Component\TrackMetadata::html($track));
        $view->body->add_element(HTMLPage\Component\TrackMetadataContainer::close());
        $view->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($view->html()));
}

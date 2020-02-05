<?php namespace RSC\API;
      use RSC\HTMLPage;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * track or a set of tracks to the client.
 * 
 */

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/track-metadata.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/track-metadata-container.php";
require_once __DIR__."/../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides metadata about the requested track, identified by the
// 'trackResourceID' parameter (or of all public tracks in the database if
// this parameter is NULL).
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->json([...]).
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
function view_track_metadata(\RSC\ResourceID $trackResourceID = NULL) : void
{
    $trackInfo = (new DatabaseConnection\TrackDatabase())->get_track_metadata($trackResourceID);
    if (!$trackInfo || !is_array($trackInfo) || !count($trackInfo))
    {
        exit(Response::code(404)->error_message("No matching track data found."));
    }

    // Build a HTML page that displays the requested tracks' metadata.
    {
        $view = new HTMLPage\HTMLPage();

        $view->head->title = "Custom tracks for Rally-Sport";
        $view->head->css .= HTMLPage\Fragment\TrackMetadataContainer::css();
        $view->head->css .= HTMLPage\Fragment\TrackMetadata::css();

        $view->body->add_script(...HTMLPage\Fragment\TrackMetadataContainer::scripts());
        $view->body->add_script(...HTMLPage\Fragment\TrackMetadata::scripts());
        
        $view->body->add_element(HTMLPage\Fragment\TrackMetadataContainer::open());
        foreach ($trackInfo as $track) $view->body->add_element(HTMLPage\Fragment\TrackMetadata::html($track));
        $view->body->add_element(HTMLPage\Fragment\TrackMetadataContainer::close());
    }

    exit(Response::code(200)->html($view->html()));
}

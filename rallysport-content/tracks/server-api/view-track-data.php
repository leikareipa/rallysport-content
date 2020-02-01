<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * track or a set of tracks to the client.
 * 
 */

require_once __DIR__."/../../common-scripts/return.php";
require_once __DIR__."/../../common-scripts/resource-id.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/track-metadata.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/track-metadata-container.php";
require_once __DIR__."/../../common-scripts/track-database-connection.php";

function view_track_metadata(ResourceID $trackResourceID = NULL)
{
    $trackInfo = (new TrackDatabaseConnection())->get_track_metadata($trackResourceID);
    if (!$trackInfo || !is_array($trackInfo) || !count($trackInfo))
    {
        exit(ReturnObject::script_failed("No matching tracks found."));
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

    exit(ReturnObject::html($view->html()));
}

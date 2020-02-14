<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// Represents a HTML element in a HTMLPage object that provides metadata about
// the tracks of Rally-Sport Content.
//
// Sample usage:
//
//   1. Create the page object: $page = new HTMLPage();
//
//   2. Import the element's fragment class into the page object: $page->use_component(TrackMetadata::class);
//
//   3. Insert an instance of the element onto the page: $page->body->add_element(TrackMetadata::html($trackInfo));
//      The $trackInfo parameter contains the actual track metadata as fetched
//      from Rally-Sport Content's track database.
//
//   4. Optionally, you can use the TrackMetadataContainer to hold multiple
//      track metadata elements.
//
abstract class TrackMetadata extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/track-metadata.css");
    }

    static public function scripts() : array
    {
        return [
            file_get_contents(__DIR__."/js/track-metadata/request-track-deletion.js"),
        ];
    }

    static public function html(array $trackMetadata)
    {
        $trackDisplayName     = ($trackMetadata["displayName"]       ?? "unknown");
        $trackInternalName    = strtolower($trackMetadata["internalName"] ?? "unknown");
        $trackWidth           = ($trackMetadata["width"]             ?? "0");
        $trackHeight          = ($trackMetadata["height"]            ?? "0");
        $kierrosSVG           = ($trackMetadata["kierrosSVG"]        ?? "No preview image");
        $trackResourceID      = ($trackMetadata["resourceID"]        ?? "unknown");
        $trackUploaderID      = ($trackMetadata["creatorID"]         ?? "unknown");
        $trackTimestamp       = ($trackMetadata["creationTimestamp"] ?? "0");
        $trackDownloadCount   = ($trackMetadata["downloadCount"]     ?? "?");
        $trackVisibilityLevel = ($trackMetadata["visibilityLevel"]   ?? "0");

        $titleIcon = ($trackVisibilityLevel == \RSC\ResourceVisibility::UNLISTED)
                     ? "<span class='tag'><i class='fas fa-fw fa-sm fa-user-friends'></i> ".\RSC\ResourceVisibility::label((int)$trackVisibilityLevel)."</span>"
                     : (($trackVisibilityLevel == \RSC\ResourceVisibility::PRIVATE)
                     ? "<span class='tag'><i class='fas fa-fw fa-sm fa-user'></i> ".\RSC\ResourceVisibility::label((int)$trackVisibilityLevel)."</span>"
                     : "");

        return "
        <div class='track-metadata'>

            <div class='card'>

                <div class='media'>
                    <a href='/rallysported/?track={$trackResourceID}'>{$kierrosSVG}</a>
                </div>

                <div class='info-box'>
                    {$trackDisplayName}
                </div>

            </div>

        </div>
        ";
    }
}

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
//   2. Import the element's fragment class into the page object: $page->use_fragment(TrackMetadata::class);
//
//   3. Insert an instance of the element onto the page: $page->body->add_element(TrackMetadata::html($trackInfo));
//      The $trackInfo parameter contains the actual track metadata as fetched
//      from Rally-Sport Content's track database.
//
//   4. Optionally, you can use the TrackMetadataContainer to hold multiple
//      track metadata elements.
//
abstract class UserMetadata extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/user-metadata.css");
    }

    static public function html(array $userMetadata)
    {
        $userResourceID        = ($userMetadata["resourceID"]        ?? "unknown");
        $userCreationTimestamp = ($userMetadata["creationTimestamp"] ?? "unknown");
        $userNumTracksUploaded = random_int(0, 15); /// TODO.

        return "
        <tr>
            <td style='font-weight: ".((($_SESSION["user_resource_id"] ?? false) == $userResourceID)? "bold" : "normal").";'>{$userResourceID}</td>
            <td style='text-align: right'><a href='/rallysport-content/tracks/?by={$userResourceID}'>{$userNumTracksUploaded}</a></td>
            <td style='text-align: right'>".date("j.n.Y", $userCreationTimestamp)."</td>
        </tr>
        ";
    }
}

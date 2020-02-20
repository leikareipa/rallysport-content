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

    static public function html(\RSC\Resource\TrackResource $track)
    {
        $kierrosSVG = (new \RSC\DatabaseConnection\TrackDatabase())->get_track_svg($track->id());

        return "
        <div class='track-metadata'>

            <div class='card'>

                <div class='media'>
                    <a href='/rallysported/?track={$track->id()->string()}'>{$kierrosSVG}</a>
                </div>

                <div class='info-box'>
                    &lsquo;{$track->data()->display_name()}&rsquo;
                </div>

            </div>

        </div>
        ";
    }
}

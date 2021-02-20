<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/resource-metadata-action-menu-widget.php";

// Represents a HTML element in a HTMLPage object that provides metadata about
// the tracks of Rally-Sport Content.
//
// Sample usage:
//
//   1. Create the page object: $page = new HTMLPage();
//
//   2. Import the element's fragment class into the page object: $page->use_component(TrackResourceMetadata::class);
//
//   3. Insert an instance of the element onto the page: $page->body->add_element(TrackResourceMetadata::html($trackInfo));
//      The $trackInfo parameter contains the actual track metadata as fetched
//      from Rally-Sport Content's track database.
//
//   4. Optionally, you can use the ResourceMetadataContainer to hold multiple
//      track metadata elements.
//
abstract class TrackResourceMetadata extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/resource-metadata.css").
               file_get_contents(__DIR__."/css/track-resource-metadata.css").
               ResourceMetadataActionMenuWidget::css();
    }

    static public function html(Resource\TrackResource $track) : string
    {
        $kierrosSVG = (new \RSC\DatabaseConnection\TrackDatabase())->get_track_svg($track->id());

        $trackCssClassName = str_replace(".", "-", $track->id()->string());

        $optionsPopupMenu = ResourceMetadataActionMenuWidget::html($trackCssClassName, [
            ["label"=>"Download for RallySportED",
             "icon"=>"fas fa-download",
             "href"=>"/rallysport-content/tracks/?zip=1&id={$track->id()->string()}"],

            ["label"=>"Open in RallySportED",
             "icon"=>"fas fa-hammer",
             "href"=>"/rallysported/?fromContent={$track->id()->string()}"],

            ["label"=>"Play in DOSBox",
             "icon"=>"fas fa-play",
             "href"=>"/rallysported/?fromContent={$track->id()->string()}#play"]
        ]);

        return "
        <div class='resource-metadata track {$trackCssClassName}'>

            <div class='card'>

                <div class='media'>

                    {$kierrosSVG}

                    {$optionsPopupMenu}

                </div>

                <div class='info-box'>

                    <div class='title'
                         title='Uploaded on ".date("j F Y", $track->creation_timestamp())."'>

                        <a href='/rallysport-content/tracks/?id={$track->id()->string()}'>

                            <i class='fas fa-fw fa-road'></i> &ldquo;{$track->data()->name()}&rdquo;
                        
                        </a>

                    </div>

                    <div class='icon-row right'>

                        <div class='resource-metadata-action-menu-activator'
                             title='Options'>
            
                            <i class='fas fa-fw fa-bars'></i>
            
                        </div>

                    </div>

                </div>

            </div>

        </div>
        ";
    }
}

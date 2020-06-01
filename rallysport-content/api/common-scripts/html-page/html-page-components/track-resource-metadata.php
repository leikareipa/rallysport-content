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
               file_get_contents(__DIR__."/css/track-resource-metadata.css");
    }

    static public function html(Resource\TrackResource $track) : string
    {
        $kierrosSVG = (new \RSC\DatabaseConnection\TrackDatabase())->get_track_svg($track->id());

        if ($track->visibility() === Resource\ResourceVisibility::PUBLIC)
        {
            $iconRow =
            "
            <a href='/rallysport-content/tracks/?id={$track->id()->string()}'
               title='Permalink'>
                <i class='fas fa-fw fa-link'></i>
            </a>

            <a href='/rallysported/?track={$track->id()->string()}'
               title='Open a copy in RallySportED'>
                <i class='fas fa-fw fa-hammer'></i>
            </a>

            <a href='/rallysport-content/users/?id={$track->creator_id()->string()}'
               title='Uploaded by {$track->creator_id()->string()}'>
                <i class='fas fa-fw fa-user'></i>
            </a>

            <a href='/rallysport-content/tracks/?zip=1&id={$track->id()->string()}'
               title='Download as a ZIP'>
                <i class='fas fa-fw fa-file-download'></i>
                {$track->download_count()}
            </a>
            ";
        }
        else if ($track->visibility() === Resource\ResourceVisibility::PROCESSING)
        {
            $iconRow = "<span style='color: black;'>Processing...</span>";
        }
        else
        {
            $iconRow = "";
        }

        return "
        <div class='resource-metadata track'>

            <div class='card'>

                <div class='media'>
                    {$kierrosSVG}
                </div>

                <div class='info-box'>

                    <div class='title'
                         title='Uploaded on ".date("j F Y", $track->creation_timestamp())."'>
                        <i class='fas fa-fw fa-road'></i> {$track->data()->name()}
                    </div>

                    <div class='icon-row right'>

                        {$iconRow}

                    </div>

                </div>

            </div>

        </div>
        ";
    }
}

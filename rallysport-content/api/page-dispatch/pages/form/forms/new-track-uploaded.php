<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/resource/resource-view-url-params.php";
require_once __DIR__."/../../../../common-scripts/rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form that informs the user about their successful uploading
// of a new track.
abstract class NewTrackUploaded extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Finished uploading";
    }

    static public function inner_css() : string
    {
        return file_get_contents(__DIR__."/../../../../common-scripts/html-page/html-page-components/css/track-metadata.css");
    }

    static public function inner_html() : string
    {
        if (!\RSC\Resource\ResourceViewURLParams::target_id())
        {
            exit(\RSC\API\Response::code(404)->error_message("Missing a track resource ID."));
        }

        $trackResource = \RSC\Resource\TrackResource::from_database(\RSC\Resource\ResourceViewURLParams::target_id(), true);

        if (!$trackResource)
        {
            exit(\RSC\API\Response::code(404)->error_message("Invalid track resource."));
        }

        return "
        <form class='html-page-form'>

            <div>".$trackResource->view("metadata-html")."</div>

            <div class='footnote'>
                * The above track is now publically available on Rally-Sport Content.
            </div>

            <a href='/rallysport-content/'
               class='round-button bottom-right icon-right-arrow'
               title='Go home'>
            </a>

        </form>
        ";
    }
}

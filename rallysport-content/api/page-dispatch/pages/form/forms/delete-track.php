<?php namespace RSC\API\Form;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/resource/resource-view-url-params.php";
require_once __DIR__."/../../../../common-scripts/rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form with which the user can delete a track they've
// uploaded.
abstract class DeleteTrack extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Confirm track deletion";
    }

    static public function inner_css() : string
    {
        return file_get_contents(__DIR__."/../../../../common-scripts/html-page/html-page-components/css/track-metadata.css");
    }

    static public function inner_scripts() : array
    {
        return [file_get_contents(__DIR__."/../../../../common-scripts/html-page/html-page-components/js/request-track-deletion.js")];
    }

    static public function inner_html() : string
    {
        if (!\RSC\Resource\ResourceViewURLParams::target_id())
        {
            exit(\RSC\API\Response::code(404)->error_message("Track deletion requires a target resource ID."));
        }

        $trackResource = \RSC\Resource\TrackResource::from_database(\RSC\Resource\ResourceViewURLParams::target_id(), true);

        if (!$trackResource)
        {
            exit(\RSC\API\Response::code(404)->error_message("Invalid track resource."));
        }

        return "
        <form class='html-page-form'
              onsubmit='submit_button.disabled = true; request_track_deletion(\"".$trackResource->id()->string()."\"); return false;'>

            <div>".$trackResource->view("metadata-html")."</div>

            <div class='footnote'>* The above track will be deleted from Rally-Sport Content
            if you confirm. Note that track deletion cannot be undone!</div>

            <button name='submit_button'
                    type='submit'
                    class='round-button bottom-right'
                    title='Confirm track deletion'>
            </button>

        </form>
        ";
    }
}
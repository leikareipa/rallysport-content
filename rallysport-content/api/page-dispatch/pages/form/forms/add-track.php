<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";
require_once __DIR__."/../../../../common-scripts/resource/resource-visibility.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class AddTrack extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Upload a track";
    }

    static public function inner_html() : string
    {
        return "
        <form onsubmit='submit_button.disabled = true'
              enctype='multipart/form-data'
              class='html-page-form'
              method='POST'
              action='/rallysport-content/tracks/'>

            <label for='track_file'>Track ZIP file</label>
            <input type='hidden' name='MAX_FILE_SIZE' value='".\RSC\RallySportEDTrackData::MAX_BYTE_SIZE."'>
            <input type='file' accept='.zip' id='track-file' name='rallysported_track_file' required>

            <div class='footnote'>* Select a ZIP file containing the track's data as exported
            from RallySportED-js. Also, please read
            <a style='text-decoration: underline;' href='/rallysport-content/help/?topic=upload-a-track'>this article</a>
            to understand the implications of uploading a track.
            </div>

            <button name='submit_button'
                    type='submit'
                    class='form-button bottom-right'
                    title='Submit the form'>
            </button>

        </form>
        ";
    }
}

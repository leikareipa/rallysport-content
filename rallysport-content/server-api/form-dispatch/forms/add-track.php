<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/rallysported-track-data/rallysported-track-data.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/form.php";
require_once __DIR__."/../../../common-scripts/resource/resource-visibility.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class AddTrack extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Upload a track";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".static::title()."</header>

            <form enctype='multipart/form-data' class='html-page-form' method='POST' action='/rallysport-content/tracks/'>

                <label for='track-title'>Track title</label>
                <input type='text' id='track-title' name='track_display_name' required>

                <label for='track_file'>Track ZIP file*</label>
                <input type='hidden' name='MAX_FILE_SIZE' value='".\RSC\RallySportEDTrackData::MAX_BYTE_SIZE."'>
                <input type='file' accept='.zip' id='track-file' name='rallysported_track_file' required>

                <div class='footnote'>* Select a ZIP file containing the track's data as exported
                from RallySportED-js.</div>

                <button type='submit' class='round-button bottom-right' title='Submit the form'><i class='fas fa-check'></i></button>

            </form>

        </div>
        ";
    }
}

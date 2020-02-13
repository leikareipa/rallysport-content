<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page-components/form.php";
require_once __DIR__."/../../../common-scripts/resource/resource-visibility.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class AddTrack extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Add a new track";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".AddTrack::title()."</header>

            <form enctype='multipart/form-data' class='html-page-form' method='POST' action='/rallysport-content/tracks/'>

                <label for='track-title'>Track name</label>
                <input type='text' id='track-title' name='track_display_name' required>

                <label for='track-visibility'>Initial visibility</label>
                <select id='track-visibility' name='track_visibility' required>
                    <option value='".\RSC\ResourceVisibility::PUBLIC."'>".\RSC\ResourceVisibility::label(\RSC\ResourceVisibility::PUBLIC)."</option>
                    <option value='".\RSC\ResourceVisibility::UNLISTED."'>".\RSC\ResourceVisibility::label(\RSC\ResourceVisibility::UNLISTED)."</option>
                    <option value='".\RSC\ResourceVisibility::PRIVATE."'>".\RSC\ResourceVisibility::label(\RSC\ResourceVisibility::PRIVATE)."</option>
                </select>

                <label for='track_file'>File</label>
                <input type='hidden' name='MAX_FILE_SIZE' value='102400'>
                <input type='file' accept='.zip' id='track-file' name='rallysported_track_file' required>

                <button type='submit'>Upload to Rally-Sport Content</button>

            </form>

        </div>
        ";
    }
}

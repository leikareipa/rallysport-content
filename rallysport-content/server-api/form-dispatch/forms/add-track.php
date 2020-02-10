<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page-components/html-page-form.php";
require_once __DIR__."/../../../common-scripts/resource/resource-visibility.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class AddTrack extends \RSC\HTMLPage\Component\HTMLPageForm
{
    static public function title() : string
    {
        return "Add a new track";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container' id='add-track'>

            <header>".AddTrack::title()."</header>

            <form class='html-page-form' method='POST' action='/rallysport-content/tracks/'>

                <label for='track-title'>Track name</label>
                <input type='text' id='track-title' name='track_title' required>

                <label for='track-visibility'>Initial visibility</label>
                <select id='track-visibility' name='track_visibility' required>
                    <option value='".\RSC\ResourceVisibility::PUBLIC."'>".\RSC\ResourceVisibility::label(\RSC\ResourceVisibility::PUBLIC)."</option>
                    <option value='".\RSC\ResourceVisibility::UNLISTED."'>".\RSC\ResourceVisibility::label(\RSC\ResourceVisibility::UNLISTED)."</option>
                    <option value='".\RSC\ResourceVisibility::PRIVATE."'>".\RSC\ResourceVisibility::label(\RSC\ResourceVisibility::PRIVATE)."</option>
                </select>

                <label for='track_file'>File</label>
                <input type='file' accept='.zip' id='track-file' name='track_file' required>

                <button type='submit'>Upload to Rally-Sport Content</button>

            </form>

        </div>
        ";
    }
}

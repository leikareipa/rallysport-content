<?php namespace RSC\HTMLPage\Fragment;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/html-page-fragment.php";
require_once __DIR__."/../../resource/resource-visibility.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
class Form_UploadTrack extends HTMLPageFragment
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/form-upload-track.css");
    }

    static public function html()
    {
        return "
        <div class='form-upload-track-container'>

            <header>Upload a track</header>

            <form id='form-upload-track' method='POST' action='/rallysport-content/tracks/'>

                <label for='track-title'>Track title</label>
                <input type='text' id='track-title' name='track_title' required>

                <label for='track-visibility'>Initial visibility</label>
                <select id='track-visibility' name='track_visibility' required>
                    <option value='".\RSC\ResourceVisibility::EVERYONE."'>Everyone</option>
                    <option value='".\RSC\ResourceVisibility::UNLISTED."'>Unlisted</option>
                    <option value='".\RSC\ResourceVisibility::PRIVATE."'>Only me</option>
                </select>

                <label for='track_file'>File</label>
                <input type='file' accept='.zip' id='track-file' name='track_file' required>

                <button type='submit'>Upload</button>

            </form>

        </div>
        ";
    }
}

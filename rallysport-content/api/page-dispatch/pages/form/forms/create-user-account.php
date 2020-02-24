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

// Represents a HTML form with which the user can create a new user account.
abstract class CreateUserAccount extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "New user registration";
    }

    static public function inner_html() : string
    {
        return "
        <form onsubmit='submit_button.disabled = true'
              enctype='multipart/form-data'
              class='html-page-form'
              method='POST'
              action='/rallysport-content/users/'>

            <label for='user-id'>Email</label>
            <input type='email' id='user-id' name='email' required>

            <label for='password'>Password</label>
            <input type='text' id='password' name='password' required>

            <label for='track_file'>Track ZIP file*</label>
            <input type='hidden' name='MAX_FILE_SIZE' value='".\RSC\RallySportEDTrackData::MAX_BYTE_SIZE."'>
            <input type='file' accept='.zip' id='sample-track-file' name='sample_track_file' required>

            <div class='footnote'>* For verification, please provide a track you've recently
            created using RallySportED-js.</div>

            <button disabled
                    name='submit-button'
                    type='submit'
                    class='round-button bottom-right blocked'
                    title='Submit the form'>
            </button>

        </form>
        ";
    }
}
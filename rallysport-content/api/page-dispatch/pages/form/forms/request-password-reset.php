<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form with which the user can request that their password
// be reset.
abstract class RequestPasswordReset extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Request a password reset";
    }

    static public function inner_html() : string
    {
        return "
        <form onsubmit='submit_button.disabled = true'
              enctype='multipart/form-data'
              class='html-page-form'
              method='POST'
              action='/rallysport-content/request-password-reset.php'>

            <label for='email'>Email</label>
            <input type='email'
                   id='email'
                   name='email'
                   placeholder='E.g. user@address.com'
                   required>

            <label for='track_file'>Track ZIP file</label>
            <input type='hidden'
                   name='MAX_FILE_SIZE'
                   value='".\RSC\RallySportEDTrackData::MAX_BYTE_SIZE."'>
            <input type='file'
                   accept='.zip'
                   id='track-file'
                   name='rallysported_track_file'
                   required>

            <div class='footnote'>
                * Please provide the email address and RallySportED-js track file you
                registered with, and you'll be emailed a code for resetting your password.
                If you no longer have the track file you registered with, please
                <a href='mailto:rsc@tarpeeksihyvaesoft.com'>contact us</a>
                from the email account you registered with.
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

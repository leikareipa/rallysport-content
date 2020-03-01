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
require_once __DIR__."/../../../../common-scripts/user/user-password-characteristics.php";

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

            <label for='email'>Email</label>
            <input type='email'
                   id='email'
                   name='email'
                   placeholder='E.g. user@address.com'
                   required>

            <label for='password'>Password</label>
            <input type='text'
                   id='password'
                   name='password'
                   minlength='".\RSC\UserPasswordCharacteristics::MIN_LENGTH."'
                   maxlength='".\RSC\UserPasswordCharacteristics::MAX_LENGTH."'
                   required>

            <label for='track_file'>Track ZIP file*</label>
            <input type='hidden'
                   name='MAX_FILE_SIZE'
                   value='".\RSC\RallySportEDTrackData::MAX_BYTE_SIZE."'>
            <input type='file'
                   accept='.zip'
                   id='track-file'
                   name='rallysported_track_file'
                   required>

            <div class='footnote'>* For verification, please provide a track you've recently
            created using RallySportED-js. Note: if you ever need to reset your password, you may
            be asked to provide the same track for verification, again, so don't forget which
            one you chose!</div>

            <button name='submit-button'
                    type='submit'
                    class='round-button bottom-right'
                    title='Submit the form'>
            </button>

        </form>
        ";
    }
}

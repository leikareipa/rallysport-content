<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page-components/form.php";
require_once __DIR__."/../../../common-scripts/resource/resource-visibility.php";

// Represents a HTML form with which the user can create a new user account.
abstract class CreateUserAccount extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "New user registration";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".CreateUserAccount::title()."</header>

            <form class='html-page-form' method='POST' action='/rallysport-content/users/'>

                <label for='user-id'>Email</label>
                <input type='email' id='user-id' name='email' required>

                <label for='password'>Password</label>
                <input type='text' id='password' name='password' required>

                <label for='track_file'>Sample track*</label>
                <input type='file' accept='.zip' id='sample-track-file' name='sample_track_file' required>

                <div class='footnote'>* For automated verification, please provide a track you've
                created in RallySportED-js.</div>

                <button type='submit'>Register on Rally-Sport Content</button>

            </form>

        </div>
        ";
    }
}

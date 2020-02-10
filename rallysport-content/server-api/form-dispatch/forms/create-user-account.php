<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page-components/html-page-form.php";
require_once __DIR__."/../../../common-scripts/resource/resource-visibility.php";

// Represents a HTML form with which the user can create a new user account.
abstract class CreateUserAccount extends \RSC\HTMLPage\Component\HTMLPageForm
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

                <button type='submit'>Register on Rally-Sport Content</button>

            </form>

        </div>
        ";
    }
}

<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form with which the user can log into their user account.
abstract class UserLogin extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "User login";
    }

    static public function inner_html() : string
    {
        return "
        <form onsubmit='submit_button.disabled = true'
                class='html-page-form' method='POST' action='/rallysport-content/login.php'>

            <label for='user-id'>User ID</label>
            <input type='text' id='user-id' name='user_id' placeholder='E.g. user.xxx-xxx-xxx' required>

            <label for='password'>Password</label>
            <input type='password' id='password' name='password' required>

            <button name='submit_button'
                    type='submit'
                    class='round-button bottom-right'
                    title='Submit the form'>
            </button>

        </form>
        ";
    }
}

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
              class='html-page-form'
              method='POST'
              action='/rallysport-content/request-password-reset.php'>

            <label for='user-id'>User ID</label>
            <input type='text'
                   id='user-id'
                   name='user_id'
                   placeholder='E.g. user.xxx-xxx-xxx'
                   required>

            <label for='email'>Email</label>
            <input type='email'
                   id='email'
                   name='email'
                   placeholder='E.g. user@address.com'
                   required>

            <div class='footnote'>
                * Please provide the user ID and email you registered with. You'll be
                sent an email with further instructions for resetting your password.
            </div>

            <button name='submit_button'
                    type='submit'
                    class='round-button bottom-right'
                    title='Submit the form'>
            </button>

        </form>
        ";
    }
}

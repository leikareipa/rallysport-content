<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form with which the user can reset their password.
abstract class ResetPassword extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Reset your password";
    }

    static public function inner_html() : string
    {
        if (!isset($_GET["token"]))
        {
            return "
            <div class='html-page-form-error-string in-form'>
                Token code missing, cannot reset the password. Please follow
                the link you received via email.
            </div>
            ";
        }

        return "
        <form onsubmit='submit_button.disabled = true'
              class='html-page-form'
              method='POST'
              action='/rallysport-content/reset-password.php'>

            <label for='email'>Email</label>
            <input type='email'
                   id='email'
                   name='email'
                   placeholder='E.g. user@address.com'
                   required>

            <label for='new-password'>New password</label>
            <input type='text'
                   id='new-password'
                   name='new_password'
                   required>

            <input type='hidden'
                   name='token'
                   value='".($_GET["token"] ?? "")."'>

            <div class='footnote'>
                * To reset your password, please enter your registered email,
                and a new password to replace your old one.
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

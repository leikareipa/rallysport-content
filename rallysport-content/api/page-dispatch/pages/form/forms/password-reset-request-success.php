<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form informing the user that their request to reset their
// password was successful.
abstract class PasswordResetRequestSuccess extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Password reset request succeeded";
    }

    static public function inner_html() : string
    {
        return "
        <form class='html-page-form'>

            <div class='footnote'>
                * An email has been dispatched to you. It contains the instructions
                for resetting your password.
            </div>

            <a href='/rallysport-content/'
               class='round-button bottom-right icon-right-arrow'
               title='Return home'>
            </a>

        </form>
        ";
    }
}

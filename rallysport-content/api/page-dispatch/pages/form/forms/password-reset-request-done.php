<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form informing the user that their request to reset their
// password was completed.
abstract class PasswordResetRequestDone extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Password reset request completed";
    }

    static public function inner_html() : string
    {
        return "
        <form class='html-page-form'>

            <div class='footnote'>
                * If the email address and track file you provided are valid,
                you'll receive an email with further instructions on how to
                reset your password. If you don't receive this email, please
                <a href='mailto:rsc@tarpeeksihyvaesoft.com'>contact us</a>.
            </div>

            <a href='/rallysport-content/'
               class='round-button bottom-right icon-right-arrow'
               title='Return home'>
            </a>

        </form>
        ";
    }
}

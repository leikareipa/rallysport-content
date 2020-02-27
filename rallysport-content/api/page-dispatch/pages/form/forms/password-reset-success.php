<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form informing the user that their password was successfully
// reset.
abstract class PasswordResetSuccess extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Your password has been reset";
    }

    static public function inner_html() : string
    {
        return "
        <form class='html-page-form'>

            <div class='footnote'>
                * From now on, you can log in to Rally-Sport Content using the
                new password you provided.
            </div>

            <a href='/rallysport-content/'
               class='round-button bottom-right icon-right-arrow'
               title='Return home'>
            </a>

        </form>
        ";
    }
}

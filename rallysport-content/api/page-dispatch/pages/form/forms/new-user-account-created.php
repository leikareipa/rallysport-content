<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form that informs the user about successful account creation.
abstract class NewUserAccountCreated extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Account created";
    }

    static public function inner_html() : string
    {
        $userIDString = ($_GET["new-user-id"] ?? "Unknown");

        return "
        <form class='html-page-form'>

            <label for='user-id'>Your user ID</label>
            <input type='text' id='user-id' name='email' value={$userIDString} readonly>

            <div class='footnote'>
                * You can now log in to Rally-Sport Content using the email and
                password you registered with.
            </div>

            <a href='/rallysport-content/?form=login'
               class='form-button bottom-right icon-right-arrow'
               title='Proceed to log in'>
            </a>

        </form>
        ";
    }
}

<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form that informs the user about successful account creation.
abstract class NewUserAccountCreated extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "New account created!";
    }

    static public function inner_html() : string
    {
        return "
        <form class='html-page-form'
              method='GET'
              action='/rallysport-content/'>

            <label for='user-id'>Your user ID*</label>
            <input type='text' id='user-id' name='email' value=".($_GET["user-id"] ?? "Unknown")." readonly>

            <div class='footnote'>* Don't lose this ID! You'll need it to log in.</div>

        </form>
        ";
    }
}

<?php namespace RSC\HTMLPage\Fragment;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/html-page-form.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class Form_NewAccountCreated extends HTMLPageForm
{
    static public function title() : string
    {
        return "New account created!";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".Form_NewAccountCreated::title()."</header>

            <form class='html-page-form' method='GET' action='/rallysport-content/'>

                <label for='user-id'>User ID*</label>
                <input type='text' id='user-id' name='email' value=".($_GET["user-id"] ?? "Unknown")." readonly>

                <div style='margin-top: 5px; text-align: right; font-size: 90%;'>* Don't lose this ID! You'll need
                it to log in to Rally-Sport Content.</div>

            </form>

        </div>
        ";
    }
}

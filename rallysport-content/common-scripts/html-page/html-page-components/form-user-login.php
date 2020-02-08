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
abstract class Form_UserLogin extends HTMLPageForm
{
    static public function title() : string
    {
        return "User login";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".Form_UserLogin::title()."</header>

            <form class='html-page-form' method='POST' action='/rallysport-content/login.php'>

                <label for='user-id'>User ID</label>
                <input type='text' id='user-id' name='user_id' placeholder='E.g. user.xxx-xxx-xxx' required>

                <label for='password'>Password</label>
                <input type='password' id='password' name='password' required>

                <button type='submit'>Log in to Rally-Sport Content</button>

            </form>

        </div>
        ";
    }
}

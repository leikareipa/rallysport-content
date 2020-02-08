<?php namespace RSC\HTMLPage\Fragment;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/html-page-form.php";
require_once __DIR__."/../../resource/resource-visibility.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class Form_AddUser extends HTMLPageForm
{
    static public function title() : string
    {
        return "Create a new user account";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".Form_AddUser::title()."</header>

            <form class='html-page-form' method='POST' action='/rallysport-content/users/'>

                <label for='user-id'>Email</label>
                <input type='email' id='user-id' name='email' required>

                <label for='password'>Password</label>
                <input type='password' id='password' name='password' required>

                <button type='submit'>Create account</button>

            </form>

        </div>
        ";
    }
}

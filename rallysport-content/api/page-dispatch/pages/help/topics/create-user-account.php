<?php namespace RSC\API\HelpTopic;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/user/user-password-characteristics.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/help-topic.php";

// Provides instructions on creating a new user account.
abstract class CreateUserAccount extends \RSC\HTMLPage\Component\HelpTopic
{
    static public function id() : string
    {
        return "create-user-account";
    }
    
    static public function inner_title() : string
    {
        return "Creating a user account";
    }

    static public function inner_html() : string
    {
        return "
        <p>Although everyone can access the content on Rally-Sport Content, only
        registered users are allowed to submit new content.
        </p>

        <p>Registration is free &ndash; you can find the form to sign up
        <a href='/rallysport-content/users/?form=add'>here</a>.
        </p>

        <p>To register, you'll need to provide the following:
            <ul>
                <li> A track you've recently created in
                     <a href='/rallysported/'>RallySportED-js</a></li>
                <li> A valid email address</li>
                <li> A password</li>
            </ul>
        </p>

        <p>The password must be 
        ".\RSC\UserPasswordCharacteristics::MIN_LENGTH."&ndash;".\RSC\UserPasswordCharacteristics::MAX_LENGTH."
        characters in length; and virtually all characters are allowed. Although
        lost passwords can be reset, try not to forget yours.
        </p>

        <p>The email address should be one you expect to have reliable access
        to, as there's currently no convenient way to change an account's email
        address. The address will be stored in Rally-Sport Content's database in
        hashed form, which means it can't be directly seen even by Rally-Sport
        Content's administrators. You won't be (and can't be) sent email by
        Rally-Sport Content except when you explicitly request it &ndash; e.g.
        to reset your password.
        </p>

        <p>The track must be a ZIP file exported from <a href='/rallysported/'>RallySportED-js</a>,
        and unique to your registration. You may be asked to submit the same
        track as verification, later (e.g. when requesting to reset your
        password), so keep track of which file you used.
        </p>
        ";
    }
}

<?php namespace RSC\API\HelpTopic;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/user/user-password-characteristics.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/help-topic.php";

// Provides instructions on resetting the password of a user account.
abstract class ResetUserPassword extends \RSC\HTMLPage\Component\HelpTopic
{
    static public function id() : string
    {
        return "reset-password";
    }

    static public function inner_title() : string
    {
        return "Resetting a user account password";
    }

    static public function inner_html() : string
    {
        return "
        <p>If you've lost your account password, or for some other reason
        want to reset your current password, you can request a password
        reset using the form
        <a href='/rallysport-content/?form=request-password-reset'>here</a>.
        On successful completion of the form, you'll be emailed a code with
        which you can set a new password for the account.
        </p>

        <p>To complete the form linked to, above, you'll need to provide the
        following:

            <ul>
            
                <li>The email address you registered with
                </li>

                <li>The <a href='/rallysported/'>RallySportED-js</a> track you
                submitted on registration
                </li>

            </ul>

        </p>

        <p>If you no longer have access to the track you registered with,
        please <a href='mailto:rsc@tarpeeksihyvaesoft.com'>contact us</a>
        from the email account you registered with.
        </p>

        <p>If you don't have access to the email account you registered with,
        please <a href='mailto:rsc@tarpeeksihyvaesoft.com'>contact us</a> via
        another email account and be prepared to answer a few questions about
        your account on Rally-Sport Content.
        </p>
        ";
    }
}

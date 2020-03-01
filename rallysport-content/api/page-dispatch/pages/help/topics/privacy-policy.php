<?php namespace RSC\API\HelpTopic;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/user/user-password-characteristics.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/help-topic.php";

// Provides Rally-Sport Content's privacy policy.
abstract class PrivacyPolicy extends \RSC\HTMLPage\Component\HelpTopic
{
    static public function id() : string
    {
        return "privacy-policy";
    }

    static public function inner_title() : string
    {
        return "Privacy policy";
    }

    static public function inner_html() : string
    {
        return "
        <p>As a service, Rally-Sport Content provides means to share and access user-created
        content for the DOS game Rally-Sport; with a focus on content produced using
        <a href='https://www.github.com/leikareipa/rallysported/'>the RallySportED toolset</a>.
        </p>

        <p>Rally-Sport Content is created and maintained by
        <a href='https://www.tarpeeksihyvaesoft.com/'>Tarpeeksi Hyvae Soft</a>, makers of the
        RallySportED toolset. Rally-Sport Content is not associated with the Rally-Sport
        game nor with its creator, Jukka Jäkälä.
        </p>

        <p>Rally-Sport Content does not collect information about its users; with the
        exception of such information that is required to provide optional user registration.
        Should you decide to register a user account on Rally-Sport Content, the following
        information provided by you to Rally-Sport Content will be collected and stored:

            <ul>

                <li>Your email address. It'll be stored as a strong one-way cryptographic
                hash that cannot be used directly by Rally-Sport Content to send you email
                &ndash; only to verify that an email address you may provide later (e.g.
                to log in to your account) matches that which your account was
                registered with.
                </li>

                <li>Your account password. It'll be stored as a strong one-way cryptographic
                hash, which is used to verify that a password you may provide later (e.g. to
                log in to your account) matches that which your account was registered with.
                </li>

            </ul>

        </p>

        <p>The Rally-Sport Content website does not use cookies other than those which
        are strictly necessary for the site to function. The cookies used consist of the
        following:

            <ul>

                <li>PHP session ID (PHPSESSID). A randon, non-persistent (cleared when you
                close the browsing window, and within a short period of time otherwise)
                identifier used for log-in functionality; created by PHP's
                <a href='https://www.php.net/manual/en/function.session-start.php'>session_start</a>
                function. This cookie will be set/refreshed when you access the Rally-Sport
                Content website.
                </li>

            </ul>
            
        </p>
        ";
    }
}

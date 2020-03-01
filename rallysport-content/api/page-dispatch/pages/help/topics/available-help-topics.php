<?php namespace RSC\API\HelpTopic;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/help-topic.php";

// Lists the help topics that the user can access.
abstract class AvailableHelpTopics extends \RSC\HTMLPage\Component\HelpTopic
{
    static public function id() : string
    {
        return "available-help-topics";
    }

    static public function inner_title() : string
    {
        return "Available help topics";
    }

    static public function inner_html() : string
    {
        $helpTopic = function(string $helpTopicClassName) : string
        {
            return 
            "
            <li>
                <a href='/rallysport-content/help/?topic={$helpTopicClassName::id()}'>
                    ".$helpTopicClassName::inner_title()."
                </a>
            </li>
            ";
        };

        return "
        The following help topics are available:

        <ul>

            ".$helpTopic(PrivacyPolicy::class)."

            ".$helpTopic(CreateUserAccount::class)."

            ".$helpTopic(ResetUserPassword::class)."

            ".$helpTopic(UploadATrack::class)."

        </ul>
        ";
    }
}

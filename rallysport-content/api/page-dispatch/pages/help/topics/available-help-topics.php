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
    static public function inner_title() : string
    {
        return "Available help topics";
    }

    static public function inner_html() : string
    {
        $helpURL = function(string $topicID) : string
        {
            return "/rallysport-content/help/?topic={$topicID}";
        };

        return "
        The following help topics are available:

        <ul>

            <li>
                <a href='{$helpURL("create-user-account")}'>
                    Creating a user account
                </a>
            </li>

        </ul>
        ";
    }
}

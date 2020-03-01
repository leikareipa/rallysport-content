<?php namespace RSC\API\HelpTopic;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/help-topic.php";

// Represents a HTML form with which the user can upload a new track resource
// onto the server.
abstract class CreateUserAccount extends \RSC\HTMLPage\Component\HelpTopic
{
    static public function inner_title() : string
    {
        return "Creating a user account";
    }

    static public function inner_html() : string
    {
        return "
        Hello there.
        ";
    }
}

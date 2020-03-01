<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs HELP-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/../api/response.php";
require_once __DIR__."/../api/page-dispatch/pages/help/help-topic.php";

require_once __DIR__."/../api/page-dispatch/pages/help/topics/available-help-topics.php";
require_once __DIR__."/../api/page-dispatch/pages/help/topics/create-user-account.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        switch ($_GET["topic"])
        {
            case "create-user-account":   API\PageDisplay\help_topic(API\HelpTopic\CreateUserAccount::class); break;
            case "available-help-topics": API\PageDisplay\help_topic(API\HelpTopic\AvailableHelpTopics::class); break;
            default:                      exit(API\Response::code(303)->redirect_to("/rallysport-content/help/?topic=available-help-topics"));
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

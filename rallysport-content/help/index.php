<?php namespace RSC;

session_start();

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

require_once __DIR__."/../api/page-dispatch/pages/help/topics/privacy-policy.php";
require_once __DIR__."/../api/page-dispatch/pages/help/topics/upload-a-track.php";
require_once __DIR__."/../api/page-dispatch/pages/help/topics/reset-password.php";
require_once __DIR__."/../api/page-dispatch/pages/help/topics/available-help-topics.php";
require_once __DIR__."/../api/page-dispatch/pages/help/topics/create-user-account.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        switch ($_GET["topic"] ?? "")
        {
            case API\HelpTopic\PrivacyPolicy::id():
            {
                $page = API\BuildPage\help_topic(API\HelpTopic\PrivacyPolicy::class);
                exit(API\Response::code(200)->html($page->html()));
            }
            case API\HelpTopic\ResetUserPassword::id():
            {
                $page = API\BuildPage\help_topic(API\HelpTopic\ResetUserPassword::class);
                exit(API\Response::code(200)->html($page->html()));
            }
            case API\HelpTopic\UploadATrack::id():
            {
                $page = API\BuildPage\help_topic(API\HelpTopic\UploadATrack::class);
                exit(API\Response::code(200)->html($page->html()));
            }
            case API\HelpTopic\CreateUserAccount::id():
            {
                $page = API\BuildPage\help_topic(API\HelpTopic\CreateUserAccount::class);
                exit(API\Response::code(200)->html($page->html()));
            }
            case API\HelpTopic\AvailableHelpTopics::id():
            {
                $page = API\BuildPage\help_topic(API\HelpTopic\AvailableHelpTopics::class);
                exit(API\Response::code(200)->html($page->html()));
            }
            default:
            {
                exit(API\Response::code(303)->redirect_to("/rallysport-content/help/?topic=available-help-topics"));
            }
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

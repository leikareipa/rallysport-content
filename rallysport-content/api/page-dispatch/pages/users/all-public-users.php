<?php namespace RSC\API\BuildPage\Users;
      use RSC\DatabaseConnection;
      use RSC\HTMLPage;
      use RSC\Resource;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../response.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/user-resource-metadata.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/resource-metadata-container.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/resource-page-number-selector.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page provides a listing of all the public users in the database.
function all_public_users() : HTMLPage\HTMLPage
{
    $userDB = new DatabaseConnection\UserDatabase();

    // We'll query the database for all public users.
    $visibilityConditional = [Resource\ResourceVisibility::PUBLIC];
    $userIDConditional = [];

    // The user view is split into sub-pages, where each sub-page displays n
    // users.
    $totalUserCount = $userDB->users_count($userIDConditional, $visibilityConditional);
    $numPages = ceil($totalUserCount / Resource\ResourceViewURLParams::items_per_page());
    $startIdx = (min(($numPages - 1), Resource\ResourceViewURLParams::page_number()) * Resource\ResourceViewURLParams::items_per_page());
    
    $users = $userDB->get_users(Resource\ResourceViewURLParams::items_per_page(),
                                $startIdx,
                                $visibilityConditional,
                                $userIDConditional);

    // If we either failed to fetch user data, or there was none to fetch.
    if (!is_array($users))
    {
        $users = [];
    }

    // Build a HTML page that displays the requested users' metadata.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\ResourcePageNumberSelector::class);
        $htmlPage->use_component(HTMLPage\Component\ResourceMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\UserResourceMetadata::class);

        $htmlPage->head->title = "Users";
        $inPageTitle = "Registered users (sorted by date of registration)";
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        if (empty($users))
        {
            $htmlPage->body->add_element("<div>No users found</div>");
        }
        else
        {
            $htmlPage->body->add_element("<div style='margin: 30px 20px;'>{$inPageTitle}</div>");
            $htmlPage->body->add_element(HTMLPage\Component\ResourcePageNumberSelector::html($totalUserCount));
            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::open());

            foreach ($users as $userResource)
            {
                if (!$userResource)
                {
                    exit(API\Response::code(404)->error_message("An error occurred while processing user data."));
                }
                else
                {
                    $htmlPage->body->add_element($userResource->view("metadata-html"));
                }
            }

            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::close());
            $htmlPage->body->add_element(HTMLPage\Component\ResourcePageNumberSelector::html($totalUserCount));
        }
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

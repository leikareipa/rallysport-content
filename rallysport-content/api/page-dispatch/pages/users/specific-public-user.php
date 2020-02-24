<?php namespace RSC\API\PageDisplay\Users;
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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/user-metadata.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/user-metadata-container.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides information about a specific public user in the database.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->html(...).
//
function specific_public_user(Resource\UserResourceID $userResourceID = NULL) : void
{
    if (!$userResourceID)
    {
        exit(API\Response::code(404)->error_message("Invalid user ID."));
    }

    $user = (new DatabaseConnection\UserDatabase())->get_user_resource($userResourceID, Resource\ResourceVisibility::PUBLIC);

    // Build a HTML page that displays the requested users' metadata.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);
        $htmlPage->use_component(HTMLPage\Component\UserMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\UserMetadata::class);

        $htmlPage->head->title = "Users";
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());
        if (!$user)
        {
            $htmlPage->body->add_element("<div>No such user found</div>");
        }
        else
        {
            $htmlPage->body->add_element(HTMLPage\Component\UserMetadataContainer::open());
            $htmlPage->body->add_element($user->view("metadata-html"));
            $htmlPage->body->add_element(HTMLPage\Component\UserMetadataContainer::close());
        }
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($htmlPage->html()));
}

<?php namespace RSC\API\Page\Users;
      use RSC\DatabaseConnection;
      use RSC\HTMLPage;
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
// The page provides a listing of all the public users in the database.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(Response::code(200)->html(...).
//
function all_public_users() : void
{
    $users = (new DatabaseConnection\UserDatabase())->get_all_public_user_resources();

    if (!is_array($users) || !count($users))
    {
        exit(API\Response::code(404)->error_message("No matching users found."));
    }

    // We'll display the users in random order.
    shuffle($users);

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
        $htmlPage->body->add_element(HTMLPage\Component\UserMetadataContainer::open());
        foreach ($users as $userResource)
        {
            $htmlPage->body->add_element($userResource->view("metadata-html"));
        }
        $htmlPage->body->add_element(HTMLPage\Component\UserMetadataContainer::close());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($htmlPage->html()));
}

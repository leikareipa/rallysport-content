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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/database-connection/user-database.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page provides information about a specific public user in the database.
function specific_public_user(Resource\UserResourceID $userResourceID = NULL) : HTMLPage\HTMLPage
{
    if (!$userResourceID)
    {
        exit(API\Response::code(404)->error_message("Invalid user ID."));
    }

    // We'll query the database for a specific public user.
    $visibilityConditional = [Resource\ResourceVisibility::PUBLIC];
    $userIDConditional = [$userResourceID->string()];

    $users = (new DatabaseConnection\UserDatabase())->get_users(0,
                                                                0,
                                                                $visibilityConditional,
                                                                $userIDConditional);

    // If the database query failed.
    if (!is_array($users) || (count($users) !== 1))
    {
        $user = NULL;
    }
    else
    {
        $user = $users[0];
    }

    // Build a HTML page that displays the requested users' metadata.
    {
        $htmlPage = new HTMLPage\HTMLPage();

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\ResourceMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\UserResourceMetadata::class);
        
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        if (!$user)
        {
            $htmlPage->body->add_element("<div>No such user found</div>");
        }
        else
        {
            $htmlPage->head->title = $user->id()->string();

            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::open());
            $htmlPage->body->add_element($user->view("metadata-html"));
            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::close());
        }
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

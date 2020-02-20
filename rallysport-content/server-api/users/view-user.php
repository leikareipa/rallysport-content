<?php namespace RSC\API\Users;
      use RSC\HTMLPage;
      use RSC\DatabaseConnection;
      use RSC\API;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * user or a set of user to the client.
 * 
 */

require_once __DIR__."/../../server-api/response.php";
require_once __DIR__."/../../common-scripts/resource/resource-id.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/user-metadata.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/user-metadata-container.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";
require_once __DIR__."/../../common-scripts/database-connection/user-database.php";

// Constructs a HTML page in memory, and sends it to the client for display.
// The page provides metadata about the requested user, identified by the
// 'userResourceID' parameter (or of all public users in the database if
// this parameter is NULL).
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
// Returns: a response from the Response class (HTML status code + body).
//
//  - On failure, the response body will be a JSON string whose 'errorMessage'
//    attribute provides a brief description of the error. No user data will
//    be returned in this case.
//
//  - On success, the response body will consist of the HTML page's source
//    code as a string.
//
function view_user_metadata(Resource\UserResourceID $userResourceID = NULL) : void
{
    $users = ($userResourceID? [(new DatabaseConnection\UserDatabase())->get_user_resource($userResourceID)]
                             : (new DatabaseConnection\UserDatabase())->get_all_public_user_resources());

    if (!is_array($users) || !count($users))
    {
        exit(API\Response::code(404)->error_message("No matching users found."));
    }

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

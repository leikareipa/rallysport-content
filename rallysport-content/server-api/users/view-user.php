<?php namespace RSC\API\Users;
      use RSC\HTMLPage;
      use RSC\DatabaseConnection;
      use RSC\API;

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
//    attribute provides a brief description of the error. No track data will
//    be returned in this case.
//
//  - On success, the response body will consist of the HTML page's source
//    code as a string.
//
function view_user_metadata(\RSC\ResourceID $userResourceID = NULL) : void
{
    $userInfo = (new DatabaseConnection\UserDatabase())->get_user_metadata($userResourceID);
    if (!$userInfo || !is_array($userInfo) || !count($userInfo))
    {
        exit(API\Response::code(404)->error_message("No matching user data found."));
    }

    // Build a HTML page that displays the requested tracks' metadata.
    {
        $view = new HTMLPage\HTMLPage();

        $view->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $view->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $view->use_component(HTMLPage\Component\UserMetadataContainer::class);
        $view->use_component(HTMLPage\Component\UserMetadata::class);

        $view->head->title = "Registered users on Rally-Sport Content";
        
        $view->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $view->body->add_element(HTMLPage\Component\UserMetadataContainer::open());
        foreach ($userInfo as $user)
        {
            $view->body->add_element(HTMLPage\Component\UserMetadata::html($user));
        }
        $view->body->add_element(HTMLPage\Component\UserMetadataContainer::close());
        $view->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    exit(API\Response::code(200)->html($view->html()));
}

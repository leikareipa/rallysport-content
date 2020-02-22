<?php namespace RSC\API\Users;
      use RSC\DatabaseConnection;
      use RSC\API;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality to serve data (or metadata) of a given
 * user or a set of users to the client.
 * 
 */

require_once __DIR__."/../response.php";
require_once __DIR__."/../common-scripts/resource/resource-id.php";
require_once __DIR__."/../common-scripts/database-connection/user-database.php";

// Prints into the PHP output stream a stringified JSON object containing
// public information about the given user, or of all users in the database if
// the user resource ID is NULL.
//
// Note: The function should always return using exit() together with a
// Response object, e.g. exit(API\Response::code(200)->json([...]).
//
function serve_user_data_as_json(string /*ResourceViewType*/ $viewType,
                                 Resource\UserResourceID $userResourceID = NULL) : void
{
    if (!$userResourceID)
    {
        exit(API\Response::code(400)->error_message("Invalid user ID."));
    }

    $users = ($userResourceID? [(new DatabaseConnection\UserDatabase())->get_user_resource($userResourceID, Resource\ResourceVisibility::PUBLIC)]
                               : (new DatabaseConnection\UserDatabase())->get_all_public_user_resources());

    if (!is_array($users) || !count($users) || !$users[0])
    {
        exit(API\Response::code(404)->error_message("No matching users found."));
    }

    // We'll print out the user data in random order.
    shuffle($users);

    // Massage the data so it's output is in the desired format.
    $usersMassaged = array_reduce($users, function($acc, $element) use ($viewType)
    {
        $acc[] = $element->view($viewType);
        return $acc;
    }, []);

    // We ask the client to cache the response data only if they are for a
    // single user - otherwise, when new users are added, they would not
    // show up in the cached response.
    exit(API\Response::code(200)->json($usersMassaged, ($userResourceID? 2592000 : 0)));
}

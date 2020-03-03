<?php namespace RSC\API\Session;
      use RSC\DatabaseConnection;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Handles client-server sessions.
 * 
 * Note: This file must be included in a script in which session_start() has
 * been called.
 * 
 */

require_once __DIR__."/common-scripts/resource/user-resource.php";
require_once __DIR__."/common-scripts/resource/resource-id.php";
require_once __DIR__."/common-scripts/resource/resource-visibility.php";
require_once __DIR__."/common-scripts/database-connection/user-database.php";

// Returns TRUE if the current client is logged in; FALSE otherwise.
function is_client_logged_in() : bool
{
    return (logged_in_user_id() !== NULL);
}

// Returns the resource ID of the user logged in on the current session; or
// NULL if the client is not logged in (or if they should be considered as
// not being logged in).
function logged_in_user_id()
{
    $userID = Resource\UserResourceID::from_string($_SESSION["user_resource_id"] ?? "");

    if ($userID)
    {
        $userDB = new DatabaseConnection\UserDatabase();

        // Prevent this session from using the target account if the account
        // has been logged into by another session.
        if (!$userDB->is_account_logged_into_by_session($userID, session_id()))
        {
            return NULL;
        }

        // Prevent usage of an account that has become disabled while being
        // logged in.
        if (!$userDB->is_active_user_account($userID))
        {
            log_client_out();
            return NULL;
        }
    }

    return ($userID? $userID : NULL);
}

// Note: This should be called only after you've verified that the given user
// has provided valid credentials for logging in.
function log_client_in(\RSC\Resource\UserResourceID $userResourceID) : void
{
    if ((new DatabaseConnection\UserDatabase())->set_account_session_id($userResourceID, session_id()))
    {
        $_SESSION["user_resource_id"] = $userResourceID->string();
    }

    return;
}

function log_client_out(string $targetUserID = NULL) : void
{
    $userID = Resource\UserResourceID::from_string($targetUserID ?? $_SESSION["user_resource_id"] ?? "");

    $_SESSION["user_resource_id"] = NULL;

    if ($userID)
    {
        (new DatabaseConnection\UserDatabase())->set_account_session_id($userID, NULL);
    }

    return;
}

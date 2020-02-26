<?php namespace RSC\API\Session;
      use RSC\DatabaseConnection;

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

require_once __DIR__."/common-scripts/database-connection/user-database.php";

// Returns TRUE if the current client is logged in; FALSE otherwise.
function is_client_logged_in() : bool
{
    return (logged_in_user_id() !== NULL);
}

// Returns the resource ID of the user logged in on the current session; or
// NULL if the client is not logged in.
function logged_in_user_id()
{
    $id = \RSC\Resource\UserResourceID::from_string($_SESSION["user_resource_id"] ?? "");

    // Prevent usage of an account that has become disabled while being logged in.
    if ($id &&
        !(new DatabaseConnection\UserDatabase())->is_active_user_account($id))
    {
        log_client_out();
        return NULL;
    }

    return ($id? $id : NULL);
}

// Note: This should be called only after you've verified that the given user
// has provided valid credentials for logging in.
function log_client_in(\RSC\Resource\UserResourceID $userResourceID) : void
{
    $_SESSION["user_resource_id"] = $userResourceID->string();

    return;
}

function log_client_out() : void
{
    $_SESSION["user_resource_id"] = NULL;

    return;
}

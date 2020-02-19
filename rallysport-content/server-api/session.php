<?php namespace RSC\API\Session;

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

function is_client_logged_in() : bool
{
    return isset($_SESSION["user_resource_id"]);
}

// Returns the resource ID of the user logged in on the current session; or
// NULL if no user is logged in.
function logged_in_user_id()
{
    if (!is_client_logged_in())
    {
        return NULL;
    }

    return \RSC\Resource\UserResourceID::from_string($_SESSION["user_resource_id"]);
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

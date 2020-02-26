<?php namespace RSC;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

session_start();

require_once __DIR__."/api/response.php";
require_once __DIR__."/api/session.php";
require_once __DIR__."/api/common-scripts/resource/resource-id.php";
require_once __DIR__."/api/common-scripts/resource/resource-type.php";
require_once __DIR__."/api/common-scripts/database-connection/user-database.php";

if (API\Session\is_client_logged_in())
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=login",
                                                       "You were already logged in"));
}

$email = ($_POST["email"] ?? NULL);
$password = ($_POST["password"] ?? NULL);

if (!isset($email) ||
    !isset($password))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=login",
                                                       "Missing the email address or password"));
}

$userResourceID = (new DatabaseConnection\UserDatabase())->get_user_id_with_credentials($email, $password);

if (!$userResourceID) // Failed login.
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=login",
                                                       "Unknown email address or password"));
}
else // Successful login.
{
    API\Session\log_client_in($userResourceID);

    exit(API\Response::code(303)->redirect_to("/rallysport-content/"));
}

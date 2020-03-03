<?php namespace RSC;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/api/session.php";
require_once __DIR__."/api/response.php";
require_once __DIR__."/api/common-scripts/database-connection/user-database.php";
require_once __DIR__."/api/common-scripts/user/user-password-characteristics.php";

// The token is a random string generated when the user requested a password
// reset. Only requests that provide a matching token are considered valid.
$token = ($_POST["token"] ?? NULL);
$email = ($_POST["email"] ?? NULL);
$newPassword = ($_POST["new_password"] ?? NULL);

// The token is absolutely required for resetting a password, and we can do
// nothing without it.
if (!isset($token))
{
    exit(API\Response::code(303)->redirect_to("/rallysport-content/"));
}

if (!isset($newPassword) ||
    !isset($email))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=reset-password&token={$token}",
                                                       "Missing one or more required parameters"));
}

if (!\RSC\UserPasswordCharacteristics::would_be_valid_password($newPassword))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=reset-password&token={$token}",
                                                       "Malformed password"));
}

if (!(new DatabaseConnection\UserDatabase())->reset_user_password($email, $token, $newPassword))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=reset-password&token={$token}",
                                                       "Something went wrong while attempting to reset the password"));
}
else // Password successfully reset.
{
    $targetUserID = (new DatabaseConnection\UserDatabase())->get_user_id_with_credentials($email, $newPassword);
    API\Session\log_client_out($targetUserID->string());

    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=password-reset-success"));
}

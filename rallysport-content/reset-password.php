<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/api/response.php";
require_once __DIR__."/api/common-scripts/database-connection/user-database.php";

$token = ($_POST["token"] ?? NULL);
$email = ($_POST["email"] ?? NULL);
$newPassword = ($_POST["new_password"] ?? NULL);

// The 'token' parameter is absolutely required for resetting a password,
// and we can do nothing without it.
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

if (!(new DatabaseConnection\UserDatabase())->reset_user_password($email, $token, $newPassword))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=reset-password&token={$token}",
                                                       "Something went wrong while attempting to reset the password"));
}
else // Password successfully reset.
{
    exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=password-reset-success"));
}

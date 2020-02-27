<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/api/response.php";
require_once __DIR__."/api/emailer.php";
require_once __DIR__."/api/common-scripts/database-connection/user-database.php";

$email = ($_POST["email"] ?? NULL);
$userID = Resource\UserResourceID::from_string($_POST["user_id"] ?? NULL);

if (!isset($email) ||
    !isset($userID))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                       "Missing the email or user ID"));
}

$userDB = new DatabaseConnection\UserDatabase();

if (!$userDB->is_correct_user_email($email, $userID))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                       "Incorrect user ID or email"));
}

$resetToken = $userDB->generate_token_for_password_reset($userID);

if (!is_array($resetToken))
{
    exit(API\Response::code(303)->load_form_with_error("/rallysport-content/?form=request-password-reset",
                                                       "The request for a password reset failed"));
}

RallySportContentEmailer::send_password_reset_link($email, $resetToken);

exit(API\Response::code(303)->redirect_to("/rallysport-content/?form=password-reset-request-success"));

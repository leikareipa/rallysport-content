<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Provides functionality for sending email in Rally-Sport Content's name.
abstract class RallySportContentEmailer
{
    public const SENDER_ADDRESS = "Rally-Sport Content <rsc@tarpeeksihyvaesoft.com>";

    private static function send_email(string $to, string $title, string $message) : bool
    {
        $acceptedForDelivery = mail($to, $title, $message,
                                    "From: {static::SENDER_ADDRESS}\r\n".
                                    "Reply-To: {static::SENDER_ADDRESS}");

        return $acceptedForDelivery;
    }

    // Emails a link for resetting a user's password.
    //
    // $resetToken = an array of the form ["value"=>..., "expires"=>...], where
    // 'value' is a random code with which the password can be reset, and
    // 'expires' provides a timestamp for when the token's code is no longer
    // valid.
    //
    public static function send_password_reset_link(string $to, array $resetToken) : bool
    {
        $resetLink = "https://www.tarpeeksihyvaesoft.com/rallysport-content/?form=reset-password&token={$resetToken["value"]}";

        $message =
        "A request has been made to reset your password on Rally-Sport Content.\r\n".
        "To reset your password, please visit the following link:\r\n\r\n".
        "{$resetLink}\r\n\r\n".
        "The link will expire when you've changed your password, or in roughly\r\n".
        "24 hours from when this email was sent to you.";

        return static::send_email($to,
                                  "Resetting your password on Rally-Sport Content",
                                  $message);
    }
}

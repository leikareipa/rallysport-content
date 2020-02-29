<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Metaproperties of user passwords in Rally-Sport Content.
abstract class UserPasswordCharacteristics
{
    // Minimum/maximum character length for a user password.
    public const MIN_LENGTH = 20;
    public const MAX_LENGTH = 100;

    // Characters that are not allowed to occur in user passwords.
    public const FORBIDDEN_CHARACTERS = '\t\r\n\0';

    // Returns TRUE if the given plaintext password is validly formed; FALSE
    // otherwise.
    public static function would_be_valid_password(string $password) : bool
    {
        if ((strlen($password) < static::MIN_LENGTH) ||
            (strlen($password) > static::MAX_LENGTH) ||
            preg_match("/[".static::FORBIDDEN_CHARACTERS."]/i", $password))
        {
            return false;
        }

        return true;
    }
}

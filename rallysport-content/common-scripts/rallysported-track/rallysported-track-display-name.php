<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

 // Represents the display name of a track created in RallySportED.
class RallySportEDTrack_DisplayName
{
    // Minimum/maximum number of characters in a valid display name.
    public const MIN_LENGTH = 1;
    public const MAX_LENGTH = 15;

    // UTF-8-encoded string representing the display name.
    private $displayName;

    public function __construct()
    {
        $this->displayName = "Unknown";

        return;
    }

    public function string() : string
    {
        return $this->displayName;
    }

    public function set_name($newDisplayName) : bool
    {
        if (self::is_valid_display_name($newDisplayName))
        {
            $this->displayName = $newDisplayName;

            return true;
        }
        else
        {
            return false;
        }
    }

    // Returns true if the given display name is valid for a RallySportED
    // track; false otherwise.
    static function is_valid_display_name(string $displayName) : bool
    {
        // Display names are allowed to consist of 1-15 ASCII + Finnish umlaut
        // alphabet characters.
        if ((mb_strlen($displayName, "UTF-8") < self::MIN_LENGTH) ||
            (mb_strlen($displayName, "UTF-8") > self::MAX_LENGTH) ||
            preg_match("/[^A-Za-z-.,():\/ \x{c5}\x{e5}\x{c4}\x{e4}\x{d6}\x{f6}]/u", $displayName))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

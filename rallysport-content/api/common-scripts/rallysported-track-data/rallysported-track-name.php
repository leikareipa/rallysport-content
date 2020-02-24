<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

 // Represents the name of a track created in RallySportED.
class RallySportEDTrackData_Name
{
    // Minimum/maximum number of characters in a valid name.
    public const MIN_LENGTH = 1;
    public const MAX_LENGTH = 8;

    // ASCII string representing the name.
    private $name;

    public function __construct()
    {
        $this->name = "UNKNOWN";

        return;
    }

    public function string() : string
    {
        return $this->name;
    }

    public function set($newName) : bool
    {
        $newName = ucfirst(strtolower($newName));

        if (self::is_valid_name($newName))
        {
            $this->name = $newName;

            return true;
        }
        else
        {
            return false;
        }
    }

    // Returns true if the given name is valid for a RallySportED
    // track; false otherwise.
    static function is_valid_name(string $name) : bool
    {
        // Track names are allowed to consist of 1-8 ASCII alphabet characters.
        if ((strlen($name) < self::MIN_LENGTH) ||
            (strlen($name) > self::MAX_LENGTH) ||
            preg_match("/[^a-zA-Z]/", $name))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

 // Represents the internal name of a track created in RallySportED.
class RallySportEDTrackData_InternalName
{
    // Minimum/maximum number of characters in a valid internal name.
    public const MIN_LENGTH = 1;
    public const MAX_LENGTH = 8;

    // ASCII string representing the internal name.
    private $internalName;

    public function __construct()
    {
        $this->internalName = "UNKNOWN";

        return;
    }

    public function string() : string
    {
        return $this->internalName;
    }

    public function set_name($newInternalName) : bool
    {
        $newInternalName = strtoupper($newInternalName);

        if (self::is_valid_internal_name($newInternalName))
        {
            $this->internalName = $newInternalName;

            return true;
        }
        else
        {
            return false;
        }
    }

    // Returns true if the given internal name is valid for a RallySportED
    // track; false otherwise.
    static function is_valid_internal_name(string $internalName) : bool
    {
        // Internal track names are allowed to consist of 1-8 ASCII alphabet characters.
        if ((strlen($internalName) < self::MIN_LENGTH) ||
            (strlen($internalName) > self::MAX_LENGTH) ||
            preg_match("/[^a-zA-Z]/", $internalName))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

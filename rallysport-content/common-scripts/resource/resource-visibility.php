<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 */

// Enumerates the states of public visibility that a resource (e.g. a track or
// a user) can be assigned.
abstract class ResourceVisibility
{
    // Note: These values may be stored separate from their labels, so their
    // meaning should never change (0 should always equal the intent of NONE,
    // etc.).
    public const NONE     = 0; // Nobody can publically access this resource (e.g. because it has been deleted).
    public const UNLISTED = 1; // The resource will not be shown in listing of its resource type, but can be accessed publically through its resource ID.
    public const PRIVATE  = 2; // The resource can be accessed publically only by the user account that uploaded this resource.
    public const PUBLIC   = 3; // The visibility of this resource is not limited.

    static public function label(int $visibilityLevel) : string
    {
        switch ($visibilityLevel)
        {
            case self::NONE:     return "None";
            case self::UNLISTED: return "Unlisted";
            case self::PRIVATE:  return "Private";
            case self::PUBLIC:   return "Public";
            default:             return "Unknown";
        }
    }
}

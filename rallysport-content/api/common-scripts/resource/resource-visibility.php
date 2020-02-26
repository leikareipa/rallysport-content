<?php namespace RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 */

// Enumerates the states of visibility that a resource (e.g. a track or a user)
// can be assigned.
abstract class ResourceVisibility
{
    // Note: These values may be stored separate from their labels, so their
    // meaning should never change (0 should always equal the intent of DELETED,
    // etc.).
    public const DELETED    = 0; // Nobody can publically access this resource, because it has been deleted.
    public const UNLISTED   = 1; // The resource will not be shown in listing of its resource type, but can be accessed publically through its resource ID.
    public const PRIVATE    = 2; // The resource can be accessed publically only by the user account that uploaded this resource.
    public const PUBLIC     = 3; // The visibility of this resource is not limited.
    public const PROCESSING = 4; // The resource has not yet been made available since it was uploaded, e.g. because it's being reviewed by administration (for e.g. tracks) or awaiting email verification (for e.g. users).
    public const HIDDEN     = 5; // The resource will not be shown to anyone but administrators.

    static public function label(int $visibilityLevel) : string
    {
        switch ($visibilityLevel)
        {
            case self::DELETED:    return "Deleted";
            case self::UNLISTED:   return "Unlisted";
            case self::PRIVATE:    return "Private";
            case self::PUBLIC:     return "Public";
            case self::PROCESSING: return "Processing";
            //case self::HIDDEN    // We let self::HIDDEN be handled by the default case, since this type of resource shouldn't be publically acknowledged.
            default:               return "Unknown";
        }
    }

    // Returns TRUE if the given visibility level is a recognized value, FALSE
    // otherwise.
    static public function is_valid_visibility_level(int $visibilityLevel) : bool
    {
        switch ($visibilityLevel)
        {
            case self::DELETED:
            case self::UNLISTED:
            case self::PRIVATE:
            case self::PUBLIC:
            case self::PROCESSING:
            case self::HIDDEN:     return true;
            default:               return false;
        }
    }
}

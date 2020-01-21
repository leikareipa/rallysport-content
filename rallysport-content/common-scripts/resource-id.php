<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 * This script provides functionality for dealing with RSC's resource IDs.
 * 
 * A resource ID is a string that uniquely identifies a particular RSC resource.
 * The string consist of two elements: <resource type>+<ID>; for example, the
 * resource ID "track+7hp-sfm-rk2" identifies a track resource of ID "7hp-sfm-rk2",
 * where the ID consists of the three fragments "7hp", "sfm", and "rk2".
 * 
 * Usage:
 * 
 *  1. Construct a new resource ID: $id = new ResourceID("resourceType"). The
 *     "resourceType" string identifies the type of resource, e.g. "track",
 *     "user", etc.
 * 
 *  2. If needed, get the resource ID as a string: $idString = $id->string().
 * 
 */

class ResourceID
{
    private $resourceIDString;

    // The set of characters that the ID element of a resource ID is allowed to
    // use.
    const CHARSET = "23789acefghkmnoprst";

    const RESOURCE_TYPE_SEPARATOR = "+";
    const ID_FRAGMENT_SEPARATOR = "-";
    const ID_FRAGMENT_LENGTH = 3;
    const NUM_ID_FRAGMENTS = 3;
    
    function __construct(string $resourceType)
    {
        $this->resourceIDString = $this->generate_random_resource_id($resourceType);

        return;
    }

    function string() : string
    {
        return $this->resourceIDString;
    }

    // Returns a random resource ID string, along the lines of "resourceType+xxx-xxx-xxx".
    // The string is not guaranteed to represent a _unique_ resource ID.
    private function generate_random_resource_id(string $resourceType) : string
    {
        $randomIDFragment = function() : string
        {
            $randomFragment = "";
            $charsetLength = (strlen(self::CHARSET) - 1);

            for ($i = 0; $i < self::ID_FRAGMENT_LENGTH; $i++)
            {
                $randomFragment .= self::CHARSET[random_int(0, $charsetLength)];
            }

            return $randomFragment;
        };

        $randomResourceID = ($resourceType . self::RESOURCE_TYPE_SEPARATOR);

        for ($i = 0; $i < self::NUM_ID_FRAGMENTS; $i++)
        {
            $randomResourceID .= $randomIDFragment();

            if ($i < (self::NUM_ID_FRAGMENTS - 1))
            {
                $randomResourceID .= self::ID_FRAGMENT_SEPARATOR;
            }
        }

        return $randomResourceID;
    }
}

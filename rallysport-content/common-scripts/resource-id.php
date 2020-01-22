<?php namespace RallySportContent;

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
    const CHARSET = "23789acefghkmnprstzw";

    // These two constants should not be changed without a very good reason.
    const RESOURCE_TYPE_SEPARATOR = "+";
    const ID_FRAGMENT_SEPARATOR = "-";

    const ID_FRAGMENT_LENGTH = 3;
    const NUM_ID_FRAGMENTS = 3;
    
    function __construct(string $resourceType, string $resourceID = NULL)
    {
        // Verify fundamental assumptions.
        {
            if (!strlen(self::CHARSET))
            {
                throw new \Exception("Empty resource ID character set.");
            }

            if ((strlen(self::RESOURCE_TYPE_SEPARATOR) != 1) ||
                (strlen(self::ID_FRAGMENT_SEPARATOR) != 1))
            {
                throw new \Exception("Malformed resource ID separator.");
            }

            if ((stristr(self::CHARSET, self::RESOURCE_TYPE_SEPARATOR) !== FALSE) ||
                (stristr(self::CHARSET, self::ID_FRAGMENT_SEPARATOR) !== FALSE))
            {
                throw new \Exception("A resource ID separator must be a symbol that is not included in the resource ID character set.");
            }
            
            if (self::ID_FRAGMENT_SEPARATOR == self::RESOURCE_TYPE_SEPARATOR)
            {
                throw new \Exception("The resource type separator cannot be the same as the ID fragment separator.");
            }

            if (self::ID_FRAGMENT_LENGTH <= 0)
            {
                throw new \Exception("ID fragments cannot be empty.");
            }

            if (self::NUM_ID_FRAGMENTS <= 0)
            {
                throw new \Exception("There must be at least one ID fragment.");
            }
        }

        if (!isset($resourceID))
        {
            $this->resourceIDString = $this->generate_random_resource_id($resourceType);
        }
        else
        {
            $this->resourceIDString = ($resourceType . self::RESOURCE_TYPE_SEPARATOR . $resourceID);
        }

        return;
    }

    function string() : string
    {
        return $this->resourceIDString;
    }

    function resource_type() : string
    {
        return explode(self::RESOURCE_TYPE_SEPARATOR, $this->resourceIDString)[0];
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

    static public function resource_type_separator() : string
    {
        return self::RESOURCE_TYPE_SEPARATOR;
    }
}

<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 * This script provides functionality for dealing with RSC's resource IDs.
 * 
 * A resource ID is a string that uniquely identifies a particular RSC resource.
 * 
 * The string consist of two elements: <resource type> and <resource key>. The
 * type identifies the kind of resource, e.g. "track" or "user"; and the key
 * uniquely identifies the resource from others of its type (two resources may
 * have the same key so long as their types differ).
 * 
 * For instance, the resource ID "track.7hp-sfm-rk2" identifies a track resource.
 * Its type is "track", and its key is "7hp-sfm-rk2" (which consists of three
 * fragments: "7hp", "sfm", and "rk2").
 * 
 * Usage:
 * 
 *  1a. Construct a new random resource ID (for a track resource, in this case):
 *      $id = TrackResourceID::random().
 * 
 *  1b. Construct a new resource ID from an existing resource ID string (for a
 *      track resource, in this case): $id = TrackResourceID::from_string($idString).
 * 
 *  2.  Verify that the resource ID object is valid: if (!$id) { your error-handling here... }
 * 
 *  3.  Operate on the resource ID object.
 * 
 */

require_once __DIR__."/resource-type.php";
require_once __DIR__."/forbidden-resource-key.php";

class TrackResourceID extends ResourceID
{
    protected const RESOURCE_TYPE = ResourceType::TRACK;
}

class UserResourceID extends ResourceID
{
    protected const RESOURCE_TYPE = ResourceType::USER;
}

// Generic resource ID.
abstract class ResourceID
{
    // Of \RSC\ResourceType - e,g, "track", "user", etc. This is expected to
    // be set by child classes.
    protected const RESOURCE_TYPE = "";

    private $resourceIDString;

    // The set of characters that the resource key is allowed to use.
    public const RESOURCE_KEY_CHARSET = "23789acefghjkmnprstuv";

    // These two constants should not be changed ever.
    public const RESOURCE_TYPE_SEPARATOR = ".";
    public const RESOURCE_KEY_FRAGMENT_SEPARATOR = "-";

    public const RESOURCE_KEY_FRAGMENT_LENGTH = 3;
    public const NUM_RESOURCE_KEY_FRAGMENTS = 3;

    // Create a resource ID object with a random key. Returns the created
    // resource ID object; or NULL on error.
    static public function random() : ResourceID
    {
        try
        {
            $id = new static(static::RESOURCE_TYPE, "random");
        }
        catch (\Exception $e)
        {
            return NULL;
        }

        return $id;
    }

    // Create a resource ID object from the given ID string. The string can be
    // of the form "yyyy.xxx-xxx-xxx" or "xxx-xxx-xxx", where "yyyy" is the type
    // element and "xxx-xxx-xxx" the key element. If no type element is given,
    // one will be appended.
    //
    // Returns the created resource ID object; or NULL on error.
    //
    static public function from_string(string $resourceIDString)
    {
        try
        {
            // If the given ID string doesn't appear to contain a type element,
            // we'll insert it manually.
            if (stripos($resourceIDString, self::RESOURCE_TYPE_SEPARATOR) === FALSE)
            {
                $id = new static(static::RESOURCE_TYPE, $resourceIDString);
            }
            else
            {
                $id = new static($resourceIDString);
            }

            if (($id->resource_type() !== static::RESOURCE_TYPE) ||
                !$id->resource_key())
            {
                throw new \Exception("Invalid resource ID string.");
            }
        }
        catch (\Exception $e)
        {
            return NULL;
        }

        return $id;
    }

    // Create a resource ID object from the given type and key. Returns the
    // created resource ID object; or NULL on error.
    static public function from_type_and_key(string /*of ResourceType*/ $resourceType, string $resourceKey)
    {
        try
        {
            $id = new static($resourceType, $resourceKey);
        }
        catch (\Exception $e)
        {
            return NULL;
        }

        return $id;
    }
    
    // Throws if a valid resource ID object could not be created.
    function __construct(string /*of ResourceType*/ $resourceType, string $resourceKey = NULL)
    {
        // Verify fundamental assumptions about class constants.
        {
            if (!strlen(self::RESOURCE_KEY_CHARSET))
            {
                throw new \Exception("Empty resource ID character set.");
            }

            if ((strlen(self::RESOURCE_TYPE_SEPARATOR) != 1) ||
                (strlen(self::RESOURCE_KEY_FRAGMENT_SEPARATOR) != 1))
            {
                throw new \Exception("Malformed resource ID separator.");
            }

            if ((stristr(self::RESOURCE_KEY_CHARSET, self::RESOURCE_TYPE_SEPARATOR) !== FALSE) ||
                (stristr(self::RESOURCE_KEY_CHARSET, self::RESOURCE_KEY_FRAGMENT_SEPARATOR) !== FALSE))
            {
                throw new \Exception("A resource ID separator must be a symbol that is not included in the resource ID character set.");
            }
            
            if (self::RESOURCE_KEY_FRAGMENT_SEPARATOR == self::RESOURCE_TYPE_SEPARATOR)
            {
                throw new \Exception("The type separator cannot be the same as the key fragment separator.");
            }

            if (self::RESOURCE_KEY_FRAGMENT_LENGTH <= 0)
            {
                throw new \Exception("Key fragments cannot be empty.");
            }

            if (self::NUM_RESOURCE_KEY_FRAGMENTS <= 0)
            {
                throw new \Exception("There must be at least one key fragment.");
            }

            /// TODO: Make sure the key fragment separator doesn't occur in the key charset.
        }

        // Create the resource ID.
        if (!isset($resourceKey))
        {
            // If no resource key is given, we'll assume the given resource type
            // contains the full resource ID.
            $this->resourceIDString = $resourceType;
        }
        else if ($resourceKey === "random")
        {
            $this->resourceIDString = $this->generate_random_resource_id($resourceType);
        }
        else
        {
            $this->resourceIDString = ($resourceType . self::RESOURCE_TYPE_SEPARATOR . $resourceKey);
        }

        // Verify that the resource ID is valid.
        {
            if (($this->resource_type() !== ResourceType::TRACK) &&
                ($this->resource_type() !== ResourceType::USER))
            {
                throw new \Exception("Malformed resource ID type.");
            }

            $expectedKeyLength = (self::RESOURCE_KEY_FRAGMENT_LENGTH * self::NUM_RESOURCE_KEY_FRAGMENTS + (self::NUM_RESOURCE_KEY_FRAGMENTS - 1));
            if (strlen($this->resource_key()) != $expectedKeyLength)
            {
                throw new \Exception("Malformed resource ID key.");
            }

            foreach (str_split($this->resource_key()) as $chr)
            {
                if (($chr !== self::RESOURCE_KEY_FRAGMENT_SEPARATOR) &&
                    strpos(self::RESOURCE_KEY_CHARSET, $chr) === FALSE)
                {
                    throw new \Exception("Malformed resource ID key.");
                }
            }
        }

        return;
    }

    function string() : string
    {
        return $this->resourceIDString;
    }

    // Returns the type substring of this resource ID, or NULL if no type
    // substring exists.
    function resource_type()
    {
        if (stripos($this->resourceIDString, self::RESOURCE_TYPE_SEPARATOR) === FALSE)
        {
            return NULL;
        }

        return (explode(self::RESOURCE_TYPE_SEPARATOR, $this->resourceIDString)[0] ?? NULL);
    }

    // Returns the key substring of this resource ID, or NULL if no key
    // substring exists.
    function resource_key()
    {
        if (stripos($this->resourceIDString, self::RESOURCE_TYPE_SEPARATOR) === FALSE)
        {
            return NULL;
        }

        return (explode(self::RESOURCE_TYPE_SEPARATOR, $this->resourceIDString)[1] ?? NULL);
    }

    // Returns a random resource ID string, along the lines of "resourceType+xxx-xxx-xxx".
    // The string is not guaranteed to represent a _unique_ resource ID.
    private function generate_random_resource_id(string $resourceType) : string
    {
        $randomKeyFragment = function() : string
        {
            $randomFragment = "";
            $charsetLength = (strlen(self::RESOURCE_KEY_CHARSET) - 1);

            for ($i = 0; $i < self::RESOURCE_KEY_FRAGMENT_LENGTH; $i++)
            {
                $randomFragment .= self::RESOURCE_KEY_CHARSET[random_int(0, $charsetLength)];
            }

            return $randomFragment;
        };

        // Generate a random resource key.
        $numLoops = 0;
        do
        {
            $randomResourceKey = "";

            for ($i = 0; $i < self::NUM_RESOURCE_KEY_FRAGMENTS; $i++)
            {
                $randomResourceKey .= $randomKeyFragment();
    
                if ($i < (self::NUM_RESOURCE_KEY_FRAGMENTS - 1))
                {
                    $randomResourceKey .= self::RESOURCE_KEY_FRAGMENT_SEPARATOR;
                }
            }

            if (++$numLoops > 10000)
            {
                $randomResourceKey = "invalid";
                break;
            }
        } while (ForbiddenResourceKey::matches($randomResourceKey));

        $randomResourceID = ($resourceType . self::RESOURCE_TYPE_SEPARATOR . $randomResourceKey);

        return $randomResourceID;
    }
}

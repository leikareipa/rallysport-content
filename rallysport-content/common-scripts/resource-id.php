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
 *      $id = ResourceID::random(ResourceType::TRACK).
 * 
 *  1b. Construct a new resource ID from an existing resource ID string (for a
 *      track resource, in this case): $id = ResourceID::from_string($idString, ResourceType::TRACK).
 *      Note that if the resource string provides a type element, it must match
 *      the type specified by the 2nd parameter.
 * 
 *  2.  Verify that the resource ID object is valid: if (!$id) { your error-handling here... }
 * 
 *  3.  Operate on the resource ID object.
 * 
 */

// The types of resource we can generate a resource ID for.
abstract class ResourceType
{
    public const TRACK = "track";
    public const USER = "user";
}

// When generating a new resource ID (e.g. "track.xxx-xxx-xxx"), match its key
// element (e.g. "xxx-xxx-xxx") against this list of forbidden words - if the
// key contains any of these words, the ID should be discarded and re-generated.
//
// Note that although the key element might only contain three characters/letters
// per segment (e.g. "abc-def-ghi" has the three segments "abc", "def", and
// "ghi"), words longer than three characters can form when the segments are
// read together. For this reason, the ::matches() utility function is provided
// - simply call it with the key you want to test, and if the function returns
// false, the key does not contain any of the forbidden words.
//
abstract class ForbiddenResourceKey
{

    static public function matches(string $key) : bool
    {
        $concatenatedKey = str_replace(ResourceID::RESOURCE_KEY_FRAGMENT_SEPARATOR, "", $key);

        foreach (self::FORBIDDEN_WORDS as $forbiddenWord)
        {
            if (preg_match("/{$forbiddenWord}/i", $concatenatedKey))
            {
                return true;
            }
        }

        return false;
    }

    // The forbidden words, in regex format.
    public const FORBIDDEN_WORDS = [
        "f[a4][g9]",
        "[a4][s5][s5]",
        "fu[kg9qc]r?",
        "c[o0u][ckx]",
        "d[i1][kcx]",
        "p[e3]n[i1][s5]",
        "c[l1][t7]",
        "[g9][a4]y",
        "[g9][e3a4][i1y]",
        "ke[ij]",
        "h[o0]m[o0]",
        "f[a4]p",
        "tu[g9]",
        "j[e3]w",
        "j[o0][o0]",
        "juu",
        "tag",
        "p[o0][o0]",
        "[s5]h[i1][t7]",
        "p[i1][s5][s5]",
        "nu[t7]",
        "[g9][e3]n[i1]t[a4][l1]",
        "[s5][e3]m[e3]n",
        "[s5][t7]d",
        "[s5][e3]x",
        "[s5][e3]k[s5z2]",
        "[g9][i1][z2][z2]",
        "j[i1][z2][z2]",
        "[t7][i1][t7]",
        "[b6]r[e3][a4][s5][t7]",
        "n[a4]d",
        "v[a4]?[g9][i1]n[a4]",
        "v[g9]n",
        "v[a4]j",
        "c[l1][i1][t7]",
        "c[l1][t7]",
        "[o0]r[a4][l1]",
        "[a4]nu[s5]",
        "[a4]n[a4][l1]",
        "[a4]n[l1]",
        "[s5]u[ckg9]",
        "tup",
        "[ck]um",
        "[b68][i1][t7]ch",
        "wh[o0]r[e3]",
        "n[i1][g9]",
        "n[g9]r",
        "kkk",
        "c[o0]mm[i1][e3]",
        "n[a4][z2][i1]",
        "911",
        "112",
        "666",
        "vv",
    ];
}

class ResourceID
{
    private $resourceIDString;

    // The set of characters that the resource key is allowed to use.
    public const RESOURCE_KEY_CHARSET = "23789acefghjkmnprstuv";

    // These two constants should not be changed ever.
    public const RESOURCE_TYPE_SEPARATOR = ".";
    public const RESOURCE_KEY_FRAGMENT_SEPARATOR = "-";

    public const RESOURCE_KEY_FRAGMENT_LENGTH = 3;
    public const NUM_RESOURCE_KEY_FRAGMENTS = 3;

    // Create a resource ID object of the given type and with a random key. On
    // error, returns NULL.
    public function random(string /*of ResourceType*/ $resourceType)
    {
        try
        {
            $id = new ResourceID($resourceType, "random");
        }
        catch (\Exception $e)
        {
            return NULL;
        }

        return $id;
    }

    // Create a resource ID object of the given type from the given ID string.
    // The string can be of the form "yyyy.xxx-xxx-xxx" or "xxx-xxx-xxx", where
    // "yyyy" is the type element and "xxx-xxx-xxx" the key element - if no type
    // element is given, it will be appended to the ID based on the resource
    // type specified by the 2nd parameter. If a type element is specified by
    // the 1st parameter, it must match that named by the 2nd parameter (so
    // "track.xxx-xxx-xxx" must be accompanied by ResourceType::TRACK).
    //
    // On error, returns NULL.
    //
    public function from_string(string $resourceIDString, string /*of ResourceType*/ $resourceType)
    {
        try
        {
            // If the given ID string doesn't appear to contain a type element,
            // we'll insert it manually.
            if (stripos($resourceIDString, self::RESOURCE_TYPE_SEPARATOR) === FALSE)
            {
                $id = new ResourceID($resourceType, $resourceIDString);
            }
            else
            {
                $id = new ResourceID($resourceIDString);
            }

            if (($id->resource_type() !== $resourceType) ||
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

    // Create a resource ID object from the given type and key. On error, returns
    // NULL.
    public function from_type_and_key(string /*of ResourceType*/ $resourceType, string $resourceKey)
    {
        try
        {
            $id = new ResourceID($resourceType, $resourceKey);
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

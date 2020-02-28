<?php namespace RSC\Resource;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/resource.php";

// A user who has registered on Rally-Sport Content.
class UserResource extends Resource
{
    public const RESOURCE_TYPE = ResourceType::USER;

    // Fetches and returns as a UserResource object a user resource of the
    // given ID from the database. You must explicitly state the expected resource
    // visibility level - an error results if it does not match the resource's
    // visibility level in the database. On error, returns NULL.
    public static function from_database(string $resourceIDString,
                                         int /*ResourceVisibility*/ $visibility = ResourceVisibility::PUBLIC)
    {
        $users = (new DatabaseConnection\UserDatabase())->get_users(0,
                                                                    0,
                                                                    [$visibility],
                                                                    [$resourceIDString]);

        // If the database query failed.
        if (!is_array($users) || (count($users) !== 1))
        {
            return NULL;
        }
        else
        {
            return $users[0];
        }
    }

    // Creates and returns an instance of this class with the given arguments
    // as its data. On error, returns NULL.
    public static function with(UserResourceID $resourceID = NULL,
                                int $creationTimestamp = 0,
                                int /*ResourceVisibility*/ $visibility = ResourceVisibility::PRIVATE)
    {
        if (!$resourceID)
        {
            return NULL;
        }

        $instance = new UserResource();

        $instance->id = $resourceID;
        $instance->creatorID = $resourceID;
        $instance->data = NULL;
        $instance->visibility = $visibility;
        $instance->creationTimestamp = $creationTimestamp;

        return $instance;
    }
}

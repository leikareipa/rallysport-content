<?php namespace RSC\Resource;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

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
        $resourceID = UserResourceID::from_string($resourceIDString);
        $userResource = (new DatabaseConnection\UserDatabase())->get_user_resource($resourceID, $visibility);

        if (!$userResource)
        {
            return NULL;
        }
        else
        {
            return $userResource;
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

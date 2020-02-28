<?php namespace RSC\Resource;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/resource.php";

// A track made using RallySportED.
class TrackResource extends Resource
{
    public const RESOURCE_TYPE = ResourceType::TRACK;

    // Fetches and returns as a TrackResource object a track resource of the
    // given ID from the database. If $metadataOnly is true, only metadata will
    // be fetched (the actual track data, like its manifesto and container,
    // will not). You must also explicitly state the expected resource visibility
    // level(s) - an error results if it does not match the resource's visibility
    // level in the database. On error, returns NULL.
    public static function from_database(string $resourceIDString,
                                         bool $metadataOnly = false,
                                         array $visibility = [ResourceVisibility::PUBLIC])
    {
        $tracks = (new DatabaseConnection\TrackDatabase())->get_tracks(0,
                                                                       0,
                                                                       [],
                                                                       $visibility,
                                                                       [$resourceIDString],
                                                                       $metadataOnly);

        // If the database query failed.
        if (!is_array($tracks) || (count($tracks) !== 1))
        {
            return NULL;
        }
        else
        {
            return $tracks[0];
        }
    }

    // Creates and returns an instance of this class with the given arguments
    // as its data. On error, returns NULL.
    public static function with(\RSC\RallySportEDTrackData $data = NULL,
                                int $creationTimestamp = 0,
                                int $downloadCount = 0,
                                TrackResourceID $resourceID = NULL,
                                UserResourceID $creatorID = NULL,
                                int /*ResourceVisibility*/ $visibility = ResourceVisibility::PRIVATE)
    {
        if (!$resourceID ||
            !$creatorID ||
            !$data)
        {
            return NULL;
        }

        $instance = new TrackResource();

        $instance->id = $resourceID;
        $instance->creatorID = $creatorID;
        $instance->data = $data;
        $instance->visibility = $visibility;
        $instance->creationTimestamp = $creationTimestamp;
        $instance->downloadCount = $downloadCount;

        return $instance;
    }
}

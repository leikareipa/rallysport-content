<?php namespace RSC\Resource;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// A track made using RallySportED.
class TrackResource extends Resource
{
    public const RESOURCE_TYPE = ResourceType::TRACK;

    // Fetches and returns as a TrackResource object a track resource of the
    // given ID from the database. If $metadataOnly is true, only metadata will
    // be fetched (the actual track data, like its manifesto and container,
    // will not). On error, returns NULL.
    public static function from_database(string $resourceIDString, bool $metadataOnly = false)
    {
        $resourceID = TrackResourceID::from_string($resourceIDString);
        $trackResource = (new DatabaseConnection\TrackDatabase())->get_track_resource($resourceID, $metadataOnly);

        if (!$trackResource)
        {
            return NULL;
        }
        else
        {
            return $trackResource;
        }
    }

    // Creates and returns an instance of this class with the given arguments
    // as its data. On error, returns NULL.
    public static function with(\RSC\RallySportEDTrackData $data = NULL,
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

        return $instance;
    }
}

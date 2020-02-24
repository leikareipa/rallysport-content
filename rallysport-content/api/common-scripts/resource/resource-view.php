<?php namespace RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/resource.php";
require_once __DIR__."/../html-page/html-page-components/track-metadata.php";

// Provides views into resources. A view could be, for instance, a HTML element
// or an array that provides information about the resource.
//
// Sample usage:
//
//   $resource = new TrackResource(...);
//   ResourceView::view($resource, "metadata-array");
//
class ResourceView
{
    public static function view(Resource $resource, string $viewType)
    {
        switch ($resource::RESOURCE_TYPE)
        {
            case ResourceType::TRACK: return self::view_track_resource($resource, $viewType);
            case ResourceType::USER:  return self::view_user_resource($resource, $viewType);
            default: return NULL;
        }
    }

    private static function view_track_resource(Resource $resource, string $viewType)
    {
        switch ($viewType)
        {
            case "metadata-html":
            {
                return \RSC\HTMLPage\Component\TrackMetadata::html($resource);
            }
            case "metadata-array":
            {
                return [
                    "id"           => $resource->id()->string(),
                    "creatorID"    => $resource->creator_id()->string(),
                    "displayName"  => $resource->data()->display_name(),
                    "internalName" => $resource->data()->internal_name(),
                ];
            }
            case "data-array":
            {
                return [
                    "container" => base64_encode($resource->data()->container()),
                    "manifesto" => $resource->data()->manifesto(),
                    "meta"      => [
                        "internalName" => $resource->data()->internal_name(),
                        "displayName"  => $resource->data()->display_name(),
                        "width"        => $resource->data()->side_length(),
                        "height"       => $resource->data()->side_length(),
                        "id       "    => $resource->id()->string(),
                        "creatorID"    => $resource->creator_id()->string(),
                    ],
                ];
            }
            default: return NULL;
        }
    }

    private static function view_user_resource(Resource $resource, string $viewType)
    {
        switch ($viewType)
        {
            case "metadata-html":
            {
                return \RSC\HTMLPage\Component\UserMetadata::html($resource);
            }
            case "metadata-array":
            {
                return [
                    "id" => $resource->id()->string()
                ];
            }
            default: return NULL;
        }
    }
}

<?php namespace RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/resource.php";
require_once __DIR__."/../html-page/html-page-components/track-resource-metadata.php";

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
                return \RSC\HTMLPage\Component\TrackResourceMetadata::html($resource);
            }
            case "metadata-array":
            {
                return [
                    "name"      => $resource->data()->name(),
                    "id"        => $resource->id()->string(),
                    "creatorID" => $resource->creator_id()->string(),
                    "width"     => $resource->data()->side_length(),
                    "height"    => $resource->data()->side_length(),
                ];
            }
            case "data-array":
            {
                // Note: For reasons of compatibility with RallySportED-js, we
                // provide the track's name as two variables, internalName and
                // displayName. However, since Rally-Sport Content makes no
                // such distinction, we just enter the track's name twice.
                return [
                    "container" => base64_encode($resource->data()->container()),
                    "manifesto" => $resource->data()->manifesto(),
                    "meta"      => [
                        "internalName" => $resource->data()->name(),
                        "displayName"  => $resource->data()->name(),
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
                return \RSC\HTMLPage\Component\UserResourceMetadata::html($resource);
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

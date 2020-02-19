<?php namespace RSC\Resource;

require_once __DIR__."/resource.php";
require_once __DIR__."/resource-id.php";
require_once __DIR__."/resource-visibility.php";
require_once __DIR__."/../rallysported-track-data/rallysported-track-data.php";

$trackData = \RSC\RallySportEDTrackData::from_zip_file("HAKKUU.zip");
if (!$trackData)
{
    exit;
}

$newTrack = TrackResource::with(\RSC\RallySportEDTrackData::from_zip_file("HAKKUU.zip"),
                                \RSC\TrackResourceID::random(),
                                \RSC\UserResourceID::random(),
                                \RSC\ResourceVisibility::PUBLIC);

echo $newTrack->id()->string()."\n".$newTrack->view(ResourceViewType::METADATA_HTML)."\n";

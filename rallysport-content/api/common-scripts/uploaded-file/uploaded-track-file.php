<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/uploaded-file.php";
require_once __DIR__."/../rallysported-track-data/rallysported-track-data.php";

// Helper functions for dealing with user-uploaded RallySportED track files.
abstract class UploadedTrackFile extends UploadedFile
{
    static public function is_valid_file(array $fileInfo) : bool
    {
        return (self::data($fileInfo) == true);
    }

    static public function data(array $fileInfo)
    {
        if (!self::is_uploaded_file($fileInfo) ||
            (self::byte_size($fileInfo) > RallySportEDTrackData::MAX_BYTE_SIZE))
        {
            return NULL;
        }

        $trackData = RallySportEDTrackData::from_zip_file(self::filename($fileInfo));

        if (!$trackData)
        {
            return NULL;
        }

        return $trackData;
    }
}

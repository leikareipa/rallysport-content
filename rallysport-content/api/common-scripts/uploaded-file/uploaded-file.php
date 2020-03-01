<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// A base class for helper functions for dealing with user-uploaded files.
// Functions whose parameter list includes 'array $fileInfo' expect the
// $fileInfo array to be an element of PHP's $_FILES[].
abstract class UploadedFile
{
    // Returns TRUE if the given uploaded file is valid; FALSE otherwise.
    // Note that this function considers the validity of the file from
    // the perspective of the particular type of resource, e.g. for
    // UploadedTrackFile this would be whether the file is a well-formed
    // track file, and not just whether it's a successful upload of just
    // any file.
    abstract static public function is_valid_file(array $fileInfo) : bool;

    // Returns the given uploaded file's data; or NULL on error.
    abstract static public function data(array $fileInfo);

    // Returns the size in bytes of the uploaded file.
    static public function byte_size(array $fileInfo) : int
    {
        return filesize(self::filename($fileInfo));
    }

    static public function filename(array $fileInfo) : string
    {
        return $fileInfo["tmp_name"];
    }

    // Returns TRUE if the given alleged uploaded file is an actual valid
    // uploaded file; FALSE otherwise. Overriding classes should use the
    // is_valid_file() function, instead, as it considers the validity of
    // the file as a specific type of resource.
    static protected function is_uploaded_file(array $fileInfo) : bool
    {
        if (!is_array($fileInfo) ||
           (($fileInfo["error"] ?? !UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) ||
           !is_uploaded_file(self::filename($fileInfo)))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

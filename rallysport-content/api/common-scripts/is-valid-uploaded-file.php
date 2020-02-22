<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Takes in a POSTed $_FILES["file"] entry, and verifies that it appears like
// a valid upload.
function is_valid_uploaded_file(array $fileInfo, int $maxFileSize = 0) : bool
{
    if (!is_array($fileInfo) ||
        (($fileInfo["error"] ?? !UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) ||
        !is_uploaded_file($fileInfo["tmp_name"]) ||
        ($maxFileSize && (filesize($fileInfo["tmp_name"]) > $maxFileSize)))
    {
        return false;
    }
    else
    {
        return true;
    }
}

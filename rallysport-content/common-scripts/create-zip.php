<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Creates a zip archive with the given files and returns the zip's data as a
// string. On failure, an empty string is returned.
//
// Sample usage:
//
//   create_zip_from_file_data(["filename1.dta"=>"data-as-string...", "filename2.dta"=>"data-as-string..."]);
//
// TODO: Add error-reporting. An empty string for all states of failure is a
// bit ambiguous.
//
function create_zip_from_file_data(array $srcFiles) : string
{
    $failed = ""; // Returned if we consider the function to have failed.
    $zipString = "";

    if (!is_array($srcFiles) || !count($srcFiles))
    {
        return $failed;
    }

    // For now, we'll use PHP's ZipArchive to first create a zip file on disk
    // and then read its contents into the zip string. Ideally, though, we'd
    // generate the zip in memory in the first place.
    if (!($tmpZipFile = tmpfile()) ||
        !($tmpZipFileName = realpath(stream_get_meta_data($tmpZipFile)["uri"])))
    {
        return $failed;
    }

    $zip = new \ZipArchive();
    if ($zip->open($tmpZipFileName, \ZipArchive::OVERWRITE) !== TRUE)
    {
        return $failed;
    }

    foreach ($srcFiles as $fileName => $fileData)
    {
        if (!$fileName ||
            !$fileData ||
            !$zip->addFromString($fileName, $fileData))
        {
            return $failed;
        }
    }

    $zip->close();

    $zipString = file_get_contents($tmpZipFileName);
    if (!$zipString)
    {
        return $failed;
    }

    return $zipString;
}

<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functions to call in tandem with exit() to provide the
 * caller with a standard return response in the PHP output stream.
 * 
 * Usage:
 * 
 *  - To indicate failure, call exit(ReturnObject::script_failed("Error message")).
 * 
 *  - To indicate success, call exit(ReturnObject::script_succeeded($optionalOutputData)).
 * 
 */

class ReturnObject
{
    static function script_succeeded(array $returnObject = [], string $returnObjectKey = "payload")
    {
        if ($returnObjectKey == "succeeded")
        {
            return script_failed("The return object key is using a reserved value.");
        }

        header("Content-Type: application/json");
        echo json_encode(["succeeded"=>true, $returnObjectKey=>$returnObject], JSON_UNESCAPED_UNICODE);

        return 0;
    }

    static function script_failed(string $errorMessage = "Undefined error")
    {
        header("Content-Type: application/json");
        header("Cache-Control: no-store");
        echo json_encode(["succeeded"=>false, "errorMessage"=>$errorMessage], JSON_UNESCAPED_UNICODE);

        return 1;
    }

    // Initiates a client download of the given file data with the given file
    // name.
    static function file(string $fileName, string $fileData)
    {
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: binary"); 
        header("Content-Disposition: attachment; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . strlen($fileData));
        echo $fileData;

        return 0;
    }

    static function html(string $html)
    {
        echo $html;

        return 0;
    }
}

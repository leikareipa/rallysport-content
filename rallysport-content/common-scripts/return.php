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

        echo json_encode(["succeeded"=>true, $returnObjectKey=>$returnObject], JSON_UNESCAPED_UNICODE);

        return 0;
    }

    static function script_failed(string $errorMessage = "Undefined error")
    {
        // Error messages should not be cached.
        header("Cache-Control: no-store");
        
        echo json_encode(["succeeded"=>false, "errorMessage"=>$errorMessage], JSON_UNESCAPED_UNICODE);

        return 1;
    }
}

<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functions to call in tandem with exit() to provide the
 * client with a standard return response.
 * 
 * Usage:
 * 
 *  - To indicate failure, call exit(ReturnObject::script_failed("Error message")).
 * 
 *  - To indicate success, call exit(ReturnObject::script_succeeded()).
 * 
 */

class ReturnObject
{
    static function script_succeeded(string $returnJSONString = "{}")
    {
        echo json_encode(["succeeded"=>true, "returnData"=>$returnJSONString], JSON_UNESCAPED_UNICODE);
    }

    static function script_failed(string $errorMessage = "Undefined error")
    {
        echo json_encode(["succeeded"=>false, "errorMessage"=>$errorMessage], JSON_UNESCAPED_UNICODE);
    }
}

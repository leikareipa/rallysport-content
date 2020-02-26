<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/form.php";

// Represents a HTML form conveying a generic error message about the given
// form identifier (the "?form=" URL parameter) having an unrecognized value.
abstract class UnknownFormIdentifier extends \RSC\HTMLPage\Component\Form
{
    static public function title() : string
    {
        return "Unknown form identifier";
    }

    static public function html() : string
    {
        return "Unknown form identifier";
    }
}

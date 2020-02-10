<?php namespace RSC\API\Form;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page-components/html-page-form.php";

// Represents a HTML form with which the user can log into their user account.
abstract class UnknownFormIdentifier extends \RSC\HTMLPage\Component\HTMLPageForm
{
    static public function title() : string
    {
        return "Unknown form identifier";
    }

    static public function html() : string
    {
        return "<div style='display: inline-block'>Unknown form identifier</div>";
    }
}

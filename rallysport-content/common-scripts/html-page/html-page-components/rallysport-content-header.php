<?php namespace RSC\HTMLPage\Fragment;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-visibility.php";
require_once __DIR__."/login-widget.php";
require_once __DIR__."/../../resource/resource-id.php";

// A basic header element intended to be displayed on Rally-Sport Content's
// HTML pages.
abstract class RallySportContentHeader extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-header.css")
               . LoginWidget::css();
    }

    static public function scripts() : array
    {
        return array_merge([], // The scripts of this component (none right now, so an empty array).
                           LoginWidget::scripts());
    }

    static public function html()
    {
        return "
        <header id='rallysport-content-header'>

            Rally-Sport Content

            ".LoginWidget::html()."

        </header>
        ";
    }
}

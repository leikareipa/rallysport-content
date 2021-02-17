<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/login-widget.php";
require_once __DIR__."/search-widget.php";
require_once __DIR__."/navibar-widget.php";
require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-visibility.php";
require_once __DIR__."/../../resource/resource-id.php";

// A set of controls for executing searches in Rally-Sport Content resources.
abstract class AdvancedSearch extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/advanced-search.css")
                                 .SearchWidget::css();
    }

    static public function scripts() : array
    {
        return array_merge([], // <- The scripts of this component (none right now, so an empty array).
                           SearchWidget::scripts());
    }

    static public function html() : string
    {
        return "
        <div id='rallysport-content-advanced-search'>
        
            ".SearchWidget::html()."
        
        </div>
        ";
    }
}

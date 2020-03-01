<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/login-widget.php";
require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-visibility.php";
require_once __DIR__."/../../resource/resource-id.php";

// A basic header element intended to be displayed on Rally-Sport Content's
// help central HTML pages.
abstract class RallySportContentHelpHeader extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/rallysport-content-help-header.css");
    }

    static public function html() : string
    {
        return "
        <header id='rallysport-content-help-header'>

            <div class='title'>
                <a href='/rallysport-content/'>
                    Rally-Sport Content <i style='color: mediumseagreen;' class='fas fa-notes-medical'></i>
                </a>
            </div>

            <div style='margin: 16px 0 20px 0;'>
                <a href='/rallysport-content/help/'
                class='subtitle'>
                    Help Central
                </a>
                <span style='margin: 0 5px'>|</span>
                <a href='mailto:rsc@tarpeeksihyvaesoft.com'
                class='subtitle'>
                    Get in touch
                </a>
            </div>

        </header>
        ";
    }
}

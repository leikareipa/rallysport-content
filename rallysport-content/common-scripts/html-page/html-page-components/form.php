<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// A base class for creating HTML forms.
abstract class Form extends HTMLPage\HTMLPageComponent
{
    // The form's base CSS. Do not override this in derived classes - override
    // inner_css(), instead.
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/form.css").
               file_get_contents(__DIR__."/../../../common-scripts/html-page/html-page-components/css/round-button.css").
               static::inner_css();
    }

    // The form's base scripts. Do not override this in derived classes -
    // override inner_scripts(), instead.
    static public function scripts() : array
    {
        return static::inner_scripts();
    }

    // For individual forms to override if they want to include their own
    // CSS on top of the form's base CSS.
    static public function inner_css() : string
    {
        return "";
    }

    // For individual forms to override if they want to include their own
    // scripts on to of the form's base scripts.
    static public function inner_scripts() : array
    {
        return [""];
    }

    // Override this to customize your form's title.
    static public function title() : string
    {
        return "Untitled sample form";
    }

    // Returns the form's HTML as a string. To customize your derived form's
    // HTML, don't override this - override inner_html, instead.
    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".static::title()."</header>

            ".static::inner_html()."
        </div>
        ";
    }

    // Override this to provide your custom form HTML.
    static public function inner_html() : string
    {
        return "
        <form class='html-page-form'>

            Hello there.

        </form>
        ";
    }
}

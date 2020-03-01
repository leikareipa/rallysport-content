<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// A base class for creating HTML pages that display documentation/help to the
// user.
abstract class HelpTopic extends HTMLPage\HTMLPageComponent
{
    // An ID string to be used e.g. in URLs to identify this help topic.
    // Note: each topic MUST provide a unique ID by overriding this function.
    abstract static public function id() : string; // EXAMPLE: { return "create-user-account"; }

    // The topic's base CSS. Do not override this in derived classes - override
    // inner_css(), instead.
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/help-topic.css").
               static::inner_css();
    }

    // The topic's base scripts. Do not override this in derived classes -
    // override inner_scripts(), instead.
    static public function scripts() : array
    {
        return static::inner_scripts();
    }

    // For individual topics to override if they want to include their own
    // CSS on top of the topic's base CSS.
    static public function inner_css() : string
    {
        return "";
    }

    // For individual topics to override if they want to include their own
    // scripts on to of the topic's base scripts.
    static public function inner_scripts() : array
    {
        return [""];
    }

    // The page's base title. Do not override this in derived classes -
    // override inner_title(), instead.
    static public function title() : string
    {
        return static::inner_title()." - Help Central";
    }

    // For individual topics to override to insert into the page's title the
    // name of that particular topic. Note: each topic MUST provide an innher
    // title by overriding this function.
    abstract static public function inner_title() : string; // EXAMPLE: { return "Undefined help topic"; }

    // Returns the topic's HTML as a string. To customize your derived topic's
    // HTML, don't override this - override inner_html(), instead.
    static public function html() : string
    {
        return "
        <div class='help-topic-container'>

            <header>
                Help topic: ".static::inner_title()."
            </header>

            <div class='help-topic'>
                ".static::inner_html()."
            </div>
            
        </div>
        ";
    }

    // Override this to provide your custom topic HTML. Note: each topic MUST
    // provide an inner html by overriding this function.
    abstract static public function inner_html() : string;
    /* EXAMPLE:
    {
        return "
        <div>

            Hello there.

        </div>
        ";
    }*/
}

<?php namespace RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * 
 */

require_once __DIR__."/html-page-body.php";
require_once __DIR__."/html-page-head.php";

// Allows the user to construct a HTML page in memory.
//
// Sample usage:
//
//   1. Create the page object: $page = new HTMLPage();
//
//   2. Set the page's title: $page->head->title = "Hello there.";
//
//   3. Insert a HTML element into the body: $page->body->add_element("<div>Hello again.</div>");
//
//   4. Output the page's HTML source code: echo $page->html();
//
class HTMLPage
{
    public $body;
    public $head;

    public function __construct()
    {
        $this->head = new HTMLPageHead();
        $this->body = new HTMLPageBody();

        return;
    }

    // Pass to this function the class name of a fragment you intent to use
    // on the page - e.g. PageFooter::class, if PageFooter is the fragment's
    // class name.
    public function use_component(string $fragmentClass) : void
    {
        $this->head->css .= $fragmentClass::css();
        $this->body->add_script(...$fragmentClass::scripts());

        return;
    }

    public function html() : string
    {
        return "
        <!doctype html>
        <html>
            ".$this->head->html()."
            ".$this->body->html()."
        </html>
        ";
    }
}

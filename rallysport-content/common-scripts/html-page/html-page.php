<?php namespace RallySportContent\HTMLPage;

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

    public function html()
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

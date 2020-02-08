<?php namespace RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Represents the HTML <head></head> segment of a HTMLPage object.
//
// You will generally not use this class by itself; instead, you would create a
// HTMLPage object, which will in turn create an instance of this class as part
// of that page.
//
class HTMLPageHead
{
    public $css;  // The page's entire CSS as one string.
    public $title;
    private $elements;

    public function __construct()
    {
        $this->css = file_get_contents(__DIR__."/html-page-components/css/html-page.css");
        $this->elements = [];

        return;
    }

    public function add_element(string $element)
    {
        $this->elements[] = $element;

        return;
    }

    // Outputs the head's contents as a HTML source code string.
    public function html()
    {
        return "
        <head>
            <meta name='viewport' content='width=device-width'>
            <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
            <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.6.1/css/all.css' integrity='sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP' crossorigin='anonymous'>
            <title>{$this->title} - Rally-Sport Content</title>
            <style>
                {$this->css}
            </style>
        </head>
        ";
    }
}

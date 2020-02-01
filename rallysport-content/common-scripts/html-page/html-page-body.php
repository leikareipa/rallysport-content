<?php namespace RallySportContent\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Represents the HTML <body></body> segment of a HTMLPage object.
//
// You will generally not use this class by itself; instead, you would create a
// HTMLPage object, which will in turn create an instance of this class as part
// of that page.
//
class HTMLPageBody
{
    private $elements; // Array of HTML elements as strings; e.g. "<div>Hello</div>".
    private $scripts;  // Array of <script> elements as strings; e.g. "<script>null;</script>".

    public function __construct()
    {
        $this->elements = [];
        $this->scripts = [];

        return;
    }

    public function add_element(string $element)
    {
        $this->elements[] = $element;

        return;
    }

    public function add_script(string $script)
    {
        $this->scripts[] = "
        <script>
            {$script}
        </script>
        ";

        return;
    }

    // Outputs the body's contents as a HTML source code string.
    public function html()
    {
        return "
        <body>
            ".implode("\n", $this->elements)."
            ".implode("\n", $this->scripts)."
        </body>
        ";
    }
}

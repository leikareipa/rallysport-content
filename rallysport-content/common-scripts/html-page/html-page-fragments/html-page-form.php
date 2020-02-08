<?php namespace RSC\HTMLPage\Fragment;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-fragment.php";

// A base class for creating HTML forms.
abstract class HTMLPageFragment_Form extends HTMLPage\HTMLPageFragment
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/html-page-form.css");
    }

    static public function title() : string
    {
        return "Untitled form";
    }
}

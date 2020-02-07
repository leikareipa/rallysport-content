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
    static public function title() : string
    {
        return "Untitled form";
    }
}

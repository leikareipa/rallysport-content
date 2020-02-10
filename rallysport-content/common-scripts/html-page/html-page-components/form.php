<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// A base class for creating HTML forms. Override the methods as needed.
abstract class Form extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/form.css");
    }

    static public function title() : string
    {
        return "Untitled sample form";
    }

    static public function html() : string
    {
        return "
        <div class='html-page-form-container'>

            <header>".Form::title()."</header>

            <form class='html-page-form'>

                Hello there.

            </form>

        </div>
        ";
    }
}

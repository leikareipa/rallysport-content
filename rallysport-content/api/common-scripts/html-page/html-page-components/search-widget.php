<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../../session.php";

// Displays a widget with which the user can enter search queries (which are
// directed to /search/q=xxxx).
abstract class SearchWidget extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/search-widget.css");
    }

    static public function html() : string
    {
        return "
        <div class='search-widget'>

            <form class='search-form'
                  method='GET'
                  action='/rallysport-content/search/'>

                <div class='input-field-container'>

                    <input type='text'
                           class='input-field'
                           name='q'
                           value='".($_GET["q"] ?? "")."'
                           placeholder='Find a track by name...'
                           spellcheck='false'>

                </div>

                <button type='submit'
                        class='submit-button'>

                    <i class='fas fa-fw fa-search'></i>

                </button>

            </form>

        </div>
        ";
    }
}

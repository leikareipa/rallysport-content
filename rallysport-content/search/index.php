<?php namespace RSC;

session_start();

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Directs SEARCH-related network requests to the server's REST API.
 * 
 */

require_once __DIR__."/../api/page-dispatch/pages/search/advanced-search.php";
require_once __DIR__."/../api/response.php";
require_once __DIR__."/../api/session.php";

switch ($_SERVER["REQUEST_METHOD"])
{
    case "HEAD":
    case "GET":
    {
        // If the user provided a search term, we'll execute the search and display
        // the results to the user.
        if ($_GET["q"] ?? false)
        {
            /// TODO.

            exit(API\Response::code(404)->error_message("Search functionality is not yet implemented."));
        }
        else
        {
            API\PageDisplay\Search\advanced_search();
        }

        break;
    }
    default: exit(API\Response::code(405)->allowed("GET, HEAD"));
}

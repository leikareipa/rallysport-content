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
require_once __DIR__."/../api/page-dispatch/pages/search/search-results.php";
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
            $page = API\BuildPage\Search\search_results($_GET["q"]);
            exit(API\Response::code(200)->html($page->html()));
        }
        else
        {
            $page = API\BuildPage\Search\advanced_search();
            exit(API\Response::code(200)->html($page->html()));
        }

        break;
    }
    default:
    {
        exit(API\Response::code(405)->allowed("GET, HEAD"));
    }
}

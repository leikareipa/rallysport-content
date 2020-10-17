<?php namespace RSC\API\BuildPage\Search;
      use RSC\HTMLPage;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../response.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page provides a way for the user to search for specific resources.
function advanced_search() : HTMLPage\HTMLPage
{
    $htmlPage = new HTMLPage\HTMLPage();

    $htmlPage->head->title = "Advanced search";

    $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);
    
    // Build the page's body.
    {
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());

        $htmlPage->body->add_element("<div>This page will provide advanced search functionality</div>");

        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

<?php namespace RSC\API\PageDisplay;
      use RSC\HTMLPage;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 */

require_once __DIR__."/../../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-help-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";

// Displays a HTML page that provides the user with help, e.g. documentation of
// a particular feature.
//
// Sample usage:
//
//   help_topic(API\HelpTopic\CreateUserAccount::class);
//
//   The above will display to the client a HTML page as defined by the
//   CreateUserAccount class.
//
function help_topic(string $helpTopicClassName) : void
{
    $htmlPage = new HTMLPage\HTMLPage();

    $htmlPage->use_component($helpTopicClassName);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentHelpHeader::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);

    $htmlPage->head->title = $helpTopicClassName::title();
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHelpHeader::html());
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());
    $htmlPage->body->add_element($helpTopicClassName::html());
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());

    exit(API\Response::code(200)->html($htmlPage->html()));
}

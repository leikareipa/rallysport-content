<?php namespace RSC\API\BuildPage;
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

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page displays a help topic, whose type is identified by $helpTopicClassName.
//
// Sample usage:
//
//   help_topic(API\HelpTopic\CreateUserAccount::class);
//
//   The above will return a HTML page containing the information provided by
//   the CreateUserAccount class.
//
function help_topic(string $helpTopicClassName) : HTMLPage\HTMLPage
{
    $htmlPage = new HTMLPage\HTMLPage();

    $htmlPage->use_component($helpTopicClassName);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentHelpHeader::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);

    $htmlPage->head->title = $helpTopicClassName::title();
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHelpHeader::html());
    $htmlPage->body->add_element($helpTopicClassName::html());
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());

    return $htmlPage;
}

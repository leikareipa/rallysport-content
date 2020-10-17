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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page is made up of a single form, whose type is given by $formClassName.
//
// Sample usage:
//
//   form(API\Form\CreateUserAccount::class);
//
//   The above will return a HTML page containing the form defined by the
//   CreateUserAccount class.
//
function form(string $formClassName) : HTMLPage\HTMLPage
{
    $htmlPage = new HTMLPage\HTMLPage();

    $htmlPage->use_component($formClassName);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
    $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);

    $htmlPage->head->title = $formClassName::title();
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());
    if (isset($_GET["error"]))
    {
        $htmlPage->body->add_element("<div class='html-page-form-error-string'>".htmlspecialchars($_GET["error"])."</div>");
    }
    $htmlPage->body->add_element($formClassName::html());
    $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());

    return $htmlPage;
}

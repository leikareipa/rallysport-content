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
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";

// Used to display HTML forms to the client.
//
// Sample usage:
//
//   form(API\Form\CreateUserAccount::class);
//
//   The above will display to the client a form defined by the CreateUserAccount class.
//
function form(string $formClassName) : void
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

    exit(API\Response::code(200)->html($htmlPage->html()));
}

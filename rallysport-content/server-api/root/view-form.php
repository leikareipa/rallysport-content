<?php namespace RSC\API\Root;
      use RSC\HTMLPage;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Displays a HTML form identified by /rallysport-content/?form=xxxx.
 * 
 */

require_once __DIR__."/../../server-api/response.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/form-user-login.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";

function view_form(string $formName) : void
{
    $formView;

    switch ($formName)
    {
        case "login": $formView = form(HTMLPage\Fragment\Form_UserLogin::class); break;
        default: $formView = form_unknown(); break;
    }

    exit(API\Response::code(200)->html($formView->html()));
}

function form(string $formClassName) : HTMLPage\HTMLPage
{
    $view = new HTMLPage\HTMLPage();

    $view->use_fragment($formClassName);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentHeader::class);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentFooter::class);

    $view->head->title = $formClassName::title();

    $view->body->add_element(HTMLPage\Fragment\RallySportContentHeader::html());
    if (isset($_GET["error"]))
    {
        $view->body->add_element("<div class='form-error-string'>".htmlentities($_GET["error"])."</div>");
    }
    $view->body->add_element($formClassName::html());
    $view->body->add_element(HTMLPage\Fragment\RallySportContentFooter::html());

    return $view;
}

function form_unknown() : HTMLPage\HTMLPage
{
    $view = new HTMLPage\HTMLPage();

    $view->use_fragment(HTMLPage\Fragment\RallySportContentHeader::class);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentFooter::class);

    $view->head->title = "Unknown form identifier";

    $view->body->add_element(HTMLPage\Fragment\RallySportContentHeader::html());
    $view->body->add_element("<div style='display: inline-block'>Unknown form identifier</div>");
    $view->body->add_element(HTMLPage\Fragment\RallySportContentFooter::html());

    return $view;
}
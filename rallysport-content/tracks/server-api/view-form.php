<?php namespace RSC\API\Tracks;
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

require_once __DIR__."/../../common-scripts/response.php";
require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/form-add-track.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/rallysport-content-header.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-fragments/rallysport-content-footer.php";

function view_form(string $formName) : void
{
    $formView;

    switch ($formName)
    {
        case "add": $formView = form_add(); break;
        default: $formView = form_unknown(); break;
    }

    exit(API\Response::code(200)->html($formView->html()));
}

function form_add() : HTMLPage\HTMLPage
{
    $view = new HTMLPage\HTMLPage();

    $view->use_fragment(HTMLPage\Fragment\Form_AddTrack::class);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentHeader::class);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentFooter::class);

    $view->head->title = HTMLPage\Fragment\Form_AddTrack::title();

    $view->body->add_element(HTMLPage\Fragment\RallySportContentHeader::html());
    $view->body->add_element(HTMLPage\Fragment\Form_AddTrack::html());
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
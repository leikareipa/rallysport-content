<?php namespace RSC\API;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * Displays a HTML form identified by /rallysport-content/?form=xxxx.
 * 
 */

require_once __DIR__."/../common-scripts/response.php";
require_once __DIR__."/../common-scripts/html-page/html-page.php";
require_once __DIR__."/../common-scripts/html-page/html-page-fragments/form-upload-track.php";
require_once __DIR__."/../common-scripts/html-page/html-page-fragments/rallysport-content-header.php";
require_once __DIR__."/../common-scripts/html-page/html-page-fragments/rallysport-content-footer.php";

function view_form_page(string $formName) : void
{
    $formView;

    switch ($formName)
    {
        case "upload_track": $formView = form_page_upload_track(); break;
        default: $formView = form_page_unknown(); break;
    }

    exit(Response::code(200)->html($formView->html()));
}

function form_page_upload_track() : HTMLPage\HTMLPage
{
    $view = new HTMLPage\HTMLPage();

    $view->use_fragment(HTMLPage\Fragment\Form_UploadTrack::class);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentHeader::class);
    $view->use_fragment(HTMLPage\Fragment\RallySportContentFooter::class);

    $view->head->title = "Upload a track";

    $view->body->add_element(HTMLPage\Fragment\RallySportContentHeader::html());
    $view->body->add_element(HTMLPage\Fragment\Form_UploadTrack::html());
    $view->body->add_element(HTMLPage\Fragment\RallySportContentFooter::html());

    return $view;
}

function form_page_unknown() : HTMLPage\HTMLPage
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
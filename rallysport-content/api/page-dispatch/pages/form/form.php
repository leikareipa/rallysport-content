<?php namespace RSC\API\Page;
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

require_once __DIR__."/forms/new-user-account-created.php";
require_once __DIR__."/forms/unknown-form-identifier.php";
require_once __DIR__."/forms/create-user-account.php";
require_once __DIR__."/forms/user-login.php";
require_once __DIR__."/forms/add-track.php";
require_once __DIR__."/forms/delete-track.php";

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
    $generate_form = function(string $formClassName) : HTMLPage\HTMLPage
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
    };

    $form = $generate_form($formClassName);

    exit(API\Response::code(200)->html($form->html()));
}

<?php namespace RSC\API;
      use RSC\HTMLPage;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content (RSC)
 * 
 */

require_once __DIR__."/../../common-scripts/html-page/html-page.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";

require_once __DIR__."/forms/new-user-account-created.php";
require_once __DIR__."/forms/unknown-form-identifier.php";
require_once __DIR__."/forms/create-user-account.php";
require_once __DIR__."/forms/user-login.php";
require_once __DIR__."/forms/add-track.php";

// Used to display HTML forms to the client.
//
// Sample usage:
//
//   dispatch_form(API\Form\CreateUserAccount::class);
//
//   The above will display to the client a form defined by the CreateUserAccount class.
//
function dispatch_form(string $formClassName) : void
{
    $generate_form = function(string $formClassName) : HTMLPage\HTMLPage
    {
        $view = new HTMLPage\HTMLPage();

        $view->use_fragment($formClassName);
        $view->use_fragment(HTMLPage\Component\RallySportContentHeader::class);
        $view->use_fragment(HTMLPage\Component\RallySportContentFooter::class);
    
        $view->head->title = $formClassName::title();
        $view->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        if (isset($_GET["error"]))
        {
            $view->body->add_element("<div class='form-error-string'>".htmlspecialchars($_GET["error"])."</div>");
        }
        $view->body->add_element($formClassName::html());
        $view->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    
        return $view;
    };

    $form = $generate_form($formClassName);

    exit(API\Response::code(200)->html($form->html()));
}

<?php namespace RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/rallysport-content-header.php";
require_once __DIR__."/rallysport-content-footer.php";
require_once __DIR__."/rallysport-content-navibar.php";

// A base class for creating Rally-Sport Content HTML pages.
abstract class RallySportContentPage extends HTMLPage
{
    // Returns the page's HTML code as a string. In your derived page, don't
    // override this - override inner_html() instead.
    public function html() : string
    {
        $page = new HTMLPage();

        $page->use_component(Component\RallySportContentHeader::class);
        $page->use_component(Component\RallySportContentFooter::class);
        $page->use_component(Component\RallySportContentNavibar::class);

        $page->head->title = static::title();

        $page->body->add_element(Component\RallySportContentHeader::html());
        $page->body->add_element(Component\RallySportContentNavibar::html());
        $page->body->add_element(static::inner_html());
        $page->body->add_element(Component\RallySportContentFooter::html());

        return $page->html();
    }

    // Override this to define the page's title.
    public function title() : string
    {
        return "Untitled page";
    }

    // Override this to define the page's HTML.
    public function inner_html() : string
    {
        return "
        <div>Hello there</div>
        ";
    }
}

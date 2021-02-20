<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;
      use RSC\API;

/*
 * 2021 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-id.php";

// A multiple-choice menu that pops up when the corresponding activator button
// is pressed. Intended to be used in resource metadata cards.
//
// Usage:
//
//   1. Insert the popup menu's HTML code into a container inside the resource
//      card:
//
//      "<div ...>
//
//          ".ResourceMetadataActionMenuWidget::html(...)."
//
//       </div>"
//
//   2. Add the popup menu's activator button:
//
//      "<div class='resource-metadata-action-menu-activator'>
//
//           ...
//
//       </div>"
//
abstract class ResourceMetadataActionMenuWidget extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/resource-metadata-action-menu-widget.css");
    }

    // Takes an array describing the items with which to populate the menu, and
    // returns as a string the menu's HTML code.
    //
    // 'resourceClassName' is a CSS class name uniquely identifying the parent
    // resource metadata element - e.g. "track-jwb-an2-t7r" - and with which the
    // parent element is affixed - e.g. "<div class='resource-metadata track-jwb-an2-t7r'>"
    //
    // The 'items' array should have the following form:
    //
    // [
    //     ["label"=>"...",
    //      "icon"=>"...",
    //      "href"=>"..."],
    //
    //     ["label"=>"...",
    //      "icon"=>"...",
    //      "href"=>"..."],
    //
    //     ...
    // ]
    //
    // The "icon" property should provide a Font Awesome CSS name (e.g.
    // "fas fa-question") representing the icon to be shown for this menu item.
    //
    // The "href" property should give the URL to which the browser will navigate
    // when the menu item is clicked.
    //
    static public function html(string $resourceClassName, array $items) : string
    {
        $itemElements = array_reduce($items, function(string $elementsString, array $item){
            return $elementsString . "
                <a class='menu-item'
                    href='{$item["href"]}'>
                    
                    {$item["label"]}
                    <i class='{$item["icon"]} fa-fw'></i>

                </a>";
        }, "");

        return "
        <div class='resource-metadata-action-menu'>

            {$itemElements}

        </div>

        <script>
            window.addEventListener('DOMContentLoaded', ()=>{
                const resourceElement = document.querySelector('.resource-metadata.{$resourceClassName}');
                const popupMenuElement = resourceElement.querySelector('.resource-metadata-action-menu');
                const activatorElement = resourceElement.querySelector('.resource-metadata-action-menu-activator');

                activatorElement.onclick = ()=>{
                    popupMenuElement.classList.toggle('open');
                };
            }, {once: true});
        </script>
        ";
    }
}

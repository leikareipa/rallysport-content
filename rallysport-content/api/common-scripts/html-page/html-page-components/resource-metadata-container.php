<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// Represents a HTML container for ResourceMetadata elements in a HTMLPage object.
//
// Sample usage:
//
//   1.  Create the page object: $page = new HTMLPage();
//
//   2.  Import the container's fragment class into the page object: $page->use_component(ResourceMetadataContainer::class);
//
//   3.  Insert an instance of the container onto the page: $page->body->add_element(ResourceMetadataContainer::open());
//       Any subsequent elements inserted into the body will be placed inside the
//       container, until its ::close() function is used as shown in (5).
//
//   4.  Insert elements inside the container: $page->body->add_element("<div>This is inside the container</div>");
//
//   5.  Close the container: $page->body->add_element(ResourceMetadataContainer::close());
//       Subsequent elements added into the body will now go outside the container
//       again.
//
abstract class ResourceMetadataContainer extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/resource-metadata-container.css");
    }

    static public function open() : string
    {
        return "
        <div class='resource-metadata-container'>
        ";
    }

    static public function close() : string
    {
        return "
        </div>

        <script>
            // For track resources. We populate the track SVG elements with a slight delay, so
            // they don't slow the initial page load.
            //
            /// TODO: Proper error checking.
            window.addEventListener('DOMContentLoaded', ()=>
            {
                const cards = Array.from(document.querySelectorAll('.resource-metadata'));

                (function reveal_svg(card)
                {
                    if (card.dataset.resourceType == 'track')
                    {
                        fetch(`/rallysport-content/tracks/?id=\${card.dataset.resourceId}&svg=1`)
                        .then(response=>response.text())
                        .then(svgString=>
                        {
                            const mediaElement = card.querySelector('.media');
                            const svgTemplate = document.createElement('template');
    
                            svgTemplate.innerHTML = svgString;
                            mediaElement.appendChild(svgTemplate.content.firstChild);
                        });
                    }

                    if (cards.length)
                    {
                        setTimeout(()=>reveal_svg(cards.shift()), 50);
                    }
                })(cards.shift());
            });
        </script>

        <script>
            // When mousing over a resource card, make its z-index higher than the
            // rest of the cards', so that e.g. a drop shadow on the hovered card
            // can cover the other cards.
            window.addEventListener('DOMContentLoaded', ()=>
            {
                const cards = document.querySelectorAll('.resource-metadata');

                for (let i = 0; i < cards.length; i++)
                {
                    cards[i].addEventListener('mouseenter', ()=>
                    {
                        for (let i = 0; i < cards.length; i++)
                        {
                            cards[i].style.zIndex = 0;
                        }

                        cards[i].style.zIndex = '1';
                    });
                }
            });
        </script>
        ";
    }
}

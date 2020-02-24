<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// Represents a HTML container for TrackMetadata elements in a HTMLPage object.
//
// Sample usage:
//
//   1.  Create the page object: $page = new HTMLPage();
//
//   2.  Import the container's fragment class into the page object: $page->use_component(TrackMetadataContainer::class);
//
//   3.  Insert an instance of the container onto the page: $page->body->add_element(TrackMetadataContainer::open());
//       Any subsequent elements inserted into the body will be placed inside the
//       container, until its ::close() function is used as shown in (5).
//
//   4.  Insert elements inside the container: $page->body->add_element("<div>This is inside the container</div>");
//
//   5.  Close the container: $page->body->add_element(TrackMetadataContainer::close());
//       Subsequent elements added into the body will now go outside the container
//       again.
//
abstract class TrackMetadataContainer extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/tracks-container.css");
    }

    static public function open() : string
    {
        return "
        <div class='tracks-container'>
        ";
    }

    static public function close() : string
    {
        return "
        </div>
        ";
    }
}
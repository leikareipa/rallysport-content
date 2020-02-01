<?php namespace RallySportContent\HTMLPage\Fragment;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/html-page-fragment.php";

// Represents a HTML container for TrackMetadata elements in a HTMLPage object.
//
// Sample usage:
//
//   1. Create the page object: $page = new HTMLPage();
//
//   2. Add the container's CSS to the page: $page->head->css .= TrackMetadataContainer::css();
//
//   3. Insert an instance of the container onto the page: $page->body->add_element(TrackMetadataContainer::open());
//      Any subsequent elements inserted into the body will be placed inside the
//      container, until its ::close() function is used as shown in (5).
//
//   4. Insert elements inside the container: $page->body->add_element("<div>This is inside the container</div>");
//
//   5. Close the container: $page->body->add_element(TrackMetadataContainer::close());
//      Subsequent elements added into the body will now go outside the container
//      again.
//
class TrackMetadataContainer extends HTMLPageFragment
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/tracks-container.css");
    }

    static public function open()
    {
        return "
        <div class='tracks-container'>
        ";
    }

    static public function close()
    {
        return "
        </div>
        ";
    }
}
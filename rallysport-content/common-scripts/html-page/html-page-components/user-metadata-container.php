<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../../api/session.php";

// Represents a HTML container for UserMetadata elements in a HTMLPage object.
//
// Sample usage:
//
//   1.  Create the page object: $page = new HTMLPage();
//
//   2.  Import the container's fragment class into the page object: $page->use_component(UserMetadataContainer::class);
//
//   3.  Insert an instance of the container onto the page: $page->body->add_element(UserMetadataContainer::open());
//       Any subsequent elements inserted into the body will be placed inside the
//       container, until its ::close() function is used as shown in (5).
//
//   4.  Insert elements inside the container: $page->body->add_element("<div>This is inside the container</div>");
//
//   5.  Close the container: $page->body->add_element(UserMetadataContainer::close());
//       Subsequent elements added into the body will now go outside the container
//       again.
//
abstract class UserMetadataContainer extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/round-button.css").
               file_get_contents(__DIR__."/css/rsc-table.css");
    }

    static public function open()
    {
        return "
        <div class='rsc-table-container plain'>

            <div class='rsc-table-title'>".(($_GET["id"] ?? false)? "User ID search results" : "Registered users")."</div>

            <table class='rsc-table' style='width: 395px;'>

                <thead>
                    <tr>
                        <th>User ID</th>
                        <th style='text-align: center'>Tracks</th>
                    <tr>
                </thead>

                <tbody>
        ";
    }

    static public function close()
    {
        return "
                </tbody>
            </table>
        </div>
        ";
    }
}

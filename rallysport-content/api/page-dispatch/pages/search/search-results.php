<?php namespace RSC\API\BuildPage\Search;
      use RSC\DatabaseConnection;
      use RSC\HTMLPage;
      use RSC\Resource;
      use RSC\API;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../response.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/track-resource-metadata.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/resource-page-number-selector.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/resource-metadata-container.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-header.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-footer.php";
require_once __DIR__."/../../../common-scripts/html-page/html-page-components/rallysport-content-navibar.php";
require_once __DIR__."/../../../common-scripts/database-connection/track-database.php";

// Constructs a HTML page in memory and returns it as a HTMLPage object. On
// error, will exit with API\Response.
//
// The page provides search results for the given search query.
function search_results(string $queryString) : HTMLPage\HTMLPage
{
    $queryString = htmlspecialchars($queryString);

    $trackDB = new DatabaseConnection\TrackDatabase();

    $matchingTracks = $trackDB->search($queryString, [Resource\ResourceVisibility::PUBLIC]);

    // Build a HTML page that lists the track resources that match the search
    // query.
    $htmlPage = new HTMLPage\HTMLPage();
    {
        $htmlPage->head->title = "Search results";

        $htmlPage->use_component(HTMLPage\Component\RallySportContentHeader::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentFooter::class);
        $htmlPage->use_component(HTMLPage\Component\RallySportContentNavibar::class);
        $htmlPage->use_component(HTMLPage\Component\ResourceMetadataContainer::class);
        $htmlPage->use_component(HTMLPage\Component\TrackResourceMetadata::class);

        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentHeader::html());
        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentNavibar::html());

        if (empty($matchingTracks))
        {
            $htmlPage->body->add_element("<div style='margin: 30px;'>
                                              No search results for
                                              <span style='background-color: #ffffa9;
                                                           padding: 3px;'>
                                                  {$queryString}
                                              </span>
                                          </div>");
        }
        else
        {
            $htmlPage->body->add_element("<div style='margin: 30px;'>
                                              Search results for
                                              <span style='background-color: #ffffa9;
                                                           padding: 3px;'>
                                                  {$queryString}
                                              </span>
                                          </div>");

            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::open());

            foreach ($matchingTracks as $trackResource)
            {
                if (!$trackResource)
                {
                    exit(API\Response::code(404)->error_message("An error occurred while processing track data."));
                }

                $htmlPage->body->add_element($trackResource->view("metadata-html"));
            }

            $htmlPage->body->add_element(HTMLPage\Component\ResourceMetadataContainer::close());
        }

        $htmlPage->body->add_element(HTMLPage\Component\RallySportContentFooter::html());
    }

    return $htmlPage;
}

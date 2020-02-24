<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../resource/resource-view-url-params.php";


// A widget with which the user can specify the page number of a multi-page
// resource view.
abstract class ResourcePageNumberSelector extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/page-number-selector.css");
    }

    static public function html(int $resourceCount) : string
    {
        $numPages = ceil($resourceCount / Resource\ResourceViewURLParams::items_per_page());
        $currentPage = min($numPages, (Resource\ResourceViewURLParams::page_number() + 1));

        $selectorRow = self::create_selector_row($resourceCount, $currentPage, $numPages);
        $formattedSelectorRow = self::format_selector_row($selectorRow, $currentPage);

        return "
        <div class='page-number-selector ".(($numPages == 1)? "empty" : "")."'>

            ".implode("", $formattedSelectorRow)."

        </div>
        ";
    }

    // Returns an array of page numbers centered about the current page number;
    // for example: ["1", ".", "4", "5", "6", "7", "8", ".", "10"], assuming
    // the current page number is 6, the last page is 10, and we have a padding
    // value of 2 (two numbers to each side of the current page).
    //
    // The first element in the array is always page number 1; and the last
    // element is always the last page number. Ellipses (".") will be used to
    // separate the actual page numbers from these extrema if the padding
    // value doesn't allow them to flow together normally.
    //
    // Examples (with a padding value of 2):
    //
    //   Current page 1, max page 10: ["1", "2", "3", ".", "10"].
    //
    //   Current page 3, max page 10: ["1", "2", "3", "4", "5", ".", "10"].
    //
    //   Current page 4, max page 10: ["1", "2", "3", "4", "5", "6", ".", "10"].
    //
    //   Current page 5, max page 10: ["1", ".", "3", "4", "5", "6", "7", ".", "10"].
    //
    static private function create_selector_row(int $resourceCount,
                                                int $currentPageNumber,
                                                int $numPages) : array
    {
        $selectorRow = [];
        
        // How many page numbers to provide to the left and right of the current
        // page number.
        $padding = 3;

        // Insert the page numbers centered about the current page.
        {
            $selectorRow[] = $currentPageNumber;

            for ($i = 1; $i <= $padding; $i++)
            {
                $pageBack = ($currentPageNumber - $i);
                $pageForward = ($currentPageNumber + $i);

                if ($pageBack > 0) $selectorRow[] = $pageBack;
                if ($pageForward <= $numPages) $selectorRow[] = $pageForward;
            }

            sort($selectorRow);
        }

        // Insert ellipses, so that the first and last entry in the row is
        // always the first and last page number.
        {
            if ($currentPageNumber == ($padding + 2))
            {
                array_splice($selectorRow, 0, 0, "1");
            }
            else if ($currentPageNumber > ($padding + 1))
            {
                array_splice($selectorRow, 0, 0, "1");
                array_splice($selectorRow, 1, 0, ".");
            }

            if (($numPages - $currentPageNumber) == ($padding + 1))
            {
                array_splice($selectorRow, count($selectorRow), 0, $numPages);
            }
            else if (($numPages - $currentPageNumber) > $padding)
            {
                array_splice($selectorRow, count($selectorRow), 0, ".");
                array_splice($selectorRow, count($selectorRow), 0, $numPages);
            }
        }

        return $selectorRow;
    }

    // Returns as a string a HTML element that can be used in a selector row.
    // The element provides a clickable link to change the current page number.
    // Optionally, a Font Awesome icon name name be provided (e.g. "arrow-left"
    // for fa-arrow-left); if given, the link will be displayed with that icon
    // instead of the page number.
    private static function make_page_link(int $targetPageNumber,
                                           int $currentPageNumber,
                                           string $icon = "none") : string
    {
        $modifiedQueryString = $_GET;
        $modifiedQueryString["page"] = $targetPageNumber;
        $modifiedQueryString = http_build_query($modifiedQueryString);

        if ($icon !== "none")
        {
            return "
            <a href='?{$modifiedQueryString}'>
                <i style='color: lightgray;' class='fas fa-fw fa-sm fa-".$icon."'></i>
            </a>
            ";
        }
        else
        {
            return "
            <a href='?{$modifiedQueryString}'>
                <span class='".(($targetPageNumber == $currentPageNumber)? "current-page" : "")."'>{$targetPageNumber}</span>
            </a>
            ";
        }
    }

    // Replaces the plain page numbers in arrays returned from
    // ::make_page_selector_row() with HTML formatting, so they can be printed
    // out as clickable page links.
    private static function format_selector_row(array $selectorRow,
                                                int $currentPageNumber) : array
    {
        // Assumes that the last item in a selector row is always the last page.
        $numPages = $selectorRow[count($selectorRow) - 1];

        foreach ($selectorRow as &$pageNumber)
        {
            switch ($pageNumber)
            {
                case ".": $pageNumber = "&hellip;"; break;
                default: $pageNumber = self::make_page_link($pageNumber, $currentPageNumber); break;
            }
        }

        // Add navigational arrows to the sides of the selector row. They move
        // one page back or forward.
        array_splice($selectorRow, 0, 0, self::make_page_link(max(1, ($currentPageNumber - 1)), $currentPageNumber, "arrow-left"));
        array_splice($selectorRow, count($selectorRow), 0, self::make_page_link(min($numPages, ($currentPageNumber + 1)), $currentPageNumber, "arrow-right"));

        return $selectorRow;
    }
}

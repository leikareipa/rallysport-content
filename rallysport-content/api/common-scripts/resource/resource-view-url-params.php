<?php namespace RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// A convenience class for querying resource-related URL parameters
abstract class ResourceViewURLParams
{
    // HTML view: which page to display.
    public static function page_number() : int
    {
        // Page numbers are 1-indexed, but we'll use 0-indexing in-code.
        $pageNum = ((int)($_GET["page"] ?? 1) - 1);

        return max(0, $pageNum);
    }

    // HTML view: how many resource items to show per page.
    public static function items_per_page() : int
    {
        $count = ((int)($_GET["show"] ?? 6));

        return max(1, $count);
    }

    // Filter resources by exact ID.
    public static function target_id()
    {
        return ($_GET["id"] ?? NULL);
    }

    // Filter resources by creator (uploader).
    public static function creator_id()
    {
        return ($_GET["by"] ?? NULL);
    }
}

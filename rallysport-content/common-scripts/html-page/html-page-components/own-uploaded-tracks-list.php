<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../html-page-component.php";

// Represents a HTML table that displays metadata about a set of tracks uploaded
// by a user. Expects that the current logged-in user is also the uploader of the
// given tracks.
abstract class OwnUploadedTracksList extends HTMLPage\HTMLPageComponent
{
    static public function html(array $trackList)
    {
        $tableRows = [];

        foreach ($trackList as $trackMetadata)
        {
            $trackDisplayName   = ($trackMetadata["displayName"]   ?? "Unknown");
            $trackResourceID    = ($trackMetadata["resourceID"]    ?? "unknown");
            $trackDownloadCount = ($trackMetadata["downloadCount"] ?? "?");

            $tableRows[] = "
            <tr>

                <td><a href='/rallysport-content/tracks/?id={$trackResourceID}'>{$trackDisplayName}</a></td>

                <td style='text-align: center'>{$trackDownloadCount}</td>
                
                <td style='text-align: right'>
                    <a href='/rallysport-content/tracks/?form=delete&id={$trackResourceID}'>delete</a>
                </td>

            </tr>
            ";
        }

        return "
        <div class='rsc-table-title'>Tracks you've uploaded</div>

        <table class='rsc-table' style='width: 375px;'>

            <tr>

                <th style='width: 40%;'>Name</th>

                <th style='text-align: center'>Views</th>

                <th style='text-align: right'>Actions</th>
                
            </tr>

            ".implode("\n", $tableRows)."

        </table>
        ";
    }
}
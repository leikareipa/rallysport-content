<?php namespace RSC\HTMLPage\Fragment;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/html-page-fragment.php";

// Represents a HTML element in a HTMLPage object that provides metadata about
// the tracks of Rally-Sport Content.
//
// Sample usage:
//
//   1. Create the page object: $page = new HTMLPage();
//
//   2. Import the element's fragment class into the page object: $page->use_fragment(TrackMetadata::class);
//
//   3. Insert an instance of the element onto the page: $page->body->add_element(TrackMetadata::html($trackInfo));
//      The $trackInfo parameter contains the actual track metadata as fetched
//      from Rally-Sport Content's track database.
//
//   4. Optionally, you can use the TrackMetadataContainer to hold multiple
//      track metadata elements.
//
class TrackMetadata extends HTMLPageFragment
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/track-metadata.css");
    }

    static public function scripts() : array
    {
        return [
            file_get_contents(__DIR__."/js/track-metadata/request-track-deletion.js"),
        ];
    }

    static public function html(array $trackMetadata)
    {
        $trackDisplayName     = ($trackMetadata["displayName"]       ?? "unknown");
        $trackInternalName    = strtolower($trackMetadata["internalName"] ?? "unknown");
        $trackWidth           = ($trackMetadata["width"]             ?? "0");
        $trackHeight          = ($trackMetadata["height"]            ?? "0");
        $kierrosSVG           = ($trackMetadata["kierrosSVG"]        ?? "Image unavailable");
        $trackID              = ($trackMetadata["resourceID"]        ?? "unknown");
        $trackUploaderID      = ($trackMetadata["creatorID"]         ?? "unknown");
        $trackTimestamp       = ($trackMetadata["creationTimestamp"] ?? "0");
        $trackDownloadCount   = ($trackMetadata["downloadCount"]     ?? "?");

        return "
        <div class='track-metadata'>

            <div class='title'>
                {$trackDisplayName}
                <span class='tag'><i class='fas fa-fw fa-sm fa-tag'></i> {$trackID}</span>
                <span class='tag'><i class='fas fa-fw fa-sm fa-folder'></i> {$trackInternalName}</span>
            </div>
            
            <div class='form'>

                <div class='media'>
                    {$kierrosSVG}
                </div>

                <div class='fields'>

                    <div class='value-field' id='view-count' title='Viewed {$trackDownloadCount} times'>
                        <i class='fas fa-fw fa-sm fa-eye'></i>
                        <span class='value'>{$trackDownloadCount}</span>
                    </div>

                    <div class='value-field' id='dimensions' title='Dimensions: {$trackWidth} x {$trackHeight} tiles'>
                        <i class='fas fa-fw fa-sm fa-arrows-alt'></i>
                        <span class='value'>{$trackWidth} x {$trackHeight}</span>
                    </div>

                    <div class='value-field' id='upload-date' title='Last modified: ".date("j.n.Y H:i", $trackTimestamp)."'>
                        <i class='fas fa-fw fa-sm fa-user-clock'></i>
                        <span class='value'>".date("j M Y", $trackTimestamp)."</span>
                    </div>

                    <div class='value-field' id='uploader' title='Uploaded by {$trackUploaderID}'>
                        <i class='fas fa-fw fa-sm fa-user-tag'></i>
                        <span class='value'>
                            <a href='/rallysport-content/users/?id={$trackUploaderID}'>{$trackUploaderID}</a>
                        </span>
                    </div>

                    <div class='actions'>

                        <div class='value-field' id='edit-copy'>
                            <span class='value'>
                                <i class='fas fa-fw fa-tools'></i>
                                <a href='/rallysported/?track={$trackID}'>Open a copy in RallySportED</a>
                            </span>
                        </div>

                        <div class='value-field' id='download'>
                            <span class='value'>
                                <i class='fas fa-fw fa-database'></i>
                                <a download href='/rallysport-content/tracks/?id={$trackID}&zip=1'>Download as a ZIP</a>
                            </span>
                        </div>

                        <div class='value-field' id='request-deletion'>
                            <span class='value'>
                                <i class='fas fa-fw fa-database'></i>
                                <a href='#' onclick=\"request_track_deletion('/rallysport-content/tracks', '{$trackID}')\">Remove</a>
                            </span>
                        </div>

                    </div>

                </div>

            </div>

        </div>
        ";
    }
}

<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;
      use RSC\Resource;
      use RSC\DatabaseConnection;

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
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/round-button.css").
               file_get_contents(__DIR__."/css/rsc-table.css").
               file_get_contents(__DIR__."/css/own-uploaded-track-list.css");
    }

    static public function html(array /*elements = TrackResource*/ $tracks) : string
    {
        $tableRows = [];

        // We'll display the tracks by order of upload date, newest first.
        usort($tracks, function(Resource\TrackResource $a, Resource\TrackResource $b)
        {
            $timeA = $a->creation_timestamp();
            $timeB = $b->creation_timestamp();
            return (($timeA == $timeB)? 0 : ($timeA < $timeB)? 1 : -1);
        });

        if (empty($tracks))
        {
            $tableRows[] =
            "
            <tr>
            
                <td colspan='3'
                    style='color: gray; text-align: center;'>
                    No tracks uploaded yet
                </td>
                
            </tr>
            ";
        }
        else
        {
            foreach ($tracks as $track)
            {
                $trackDB = new DatabaseConnection\TrackDatabase();
                $kierrosSVG = "";
                $trackDownloadCount = $trackDB->get_track_download_count($track->id());

                if ($track->visibility() === Resource\ResourceVisibility::PUBLIC)
                {
                    $kierrosSVG = $trackDB->get_track_svg($track->id());

                    $iconRow =
                    "
                    <a href='/rallysport-content/tracks/?id={$track->id()->string()}'
                        title='Permalink'>
                        <i class='fas fa-fw fa-link'></i>
                    </a>

                    <a href='/rallysported/?track={$track->id()->string()}'
                        title='Open a copy in RallySportED'>
                        <i class='fas fa-fw fa-hammer'></i>
                    </a>

                    <a href='/rallysport-content/tracks/?form=delete&id={$track->id()->string()}'
                        title='Delete'>
                        <i class='fas fa-fw fa-times'></i>
                    </a>

                    <a href='/rallysport-content/tracks/?zip=1&id={$track->id()->string()}'
                        title='Download as a ZIP'>
                        <i class='fas fa-fw fa-file-download'></i>
                        {$track->download_count()}
                    </a>
                    ";
                }
                else
                {
                    $iconRow =
                    "
                    <span class='processing'
                          title='This track will be available after undergoing a manual review'>
                        Processing...
                    </span>
                    ";
                }

                $tableRows[] = "
                <tr>

                    <td>

                        <div class='media ".(empty($kierrosSVG)? "empty" : "")."'>
                            {$kierrosSVG}
                        </div>

                        <span title='Uploaded on ".date("j F Y", $track->creation_timestamp())."'>
                            {$track->data()->name()}
                        </span>

                    </td>

                    <td class='icon-row'>
                        {$iconRow}
                    </td>

                </tr>
                ";
            }
        }

        return "
        <div class='rsc-table-container'>
        
            <div class='rsc-table-title'>Tracks you've uploaded</div>

            <a href='/rallysport-content/tracks/?form=add'
               title='Upload a new track'>

                <div class='round-button top-right'>
                    <i class='fas fa-upload'></i>
                </div>

            </a>

            <table class='rsc-table own-tracks-list'
                   style='width: 395px;'>

                <thead>

                    <tr>
                        <th >Name</th>
                        <th class='icon-row' colspan='3'>Manage</th>
                    </tr>

                </thead>

                <tbody>

                    ".implode("\n", $tableRows)."

                </tbody>

            </table>
        </div>
        ";
    }
}

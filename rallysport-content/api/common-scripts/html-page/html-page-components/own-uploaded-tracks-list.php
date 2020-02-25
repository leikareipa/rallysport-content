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
               file_get_contents(__DIR__."/css/rsc-table.css");
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
                $trackDownloadCount = (new DatabaseConnection\TrackDatabase())->get_track_download_count($track->id());

                $tableRows[] = "
                <tr>

                    <td>
                        <a href='/rallysport-content/tracks/?id={$track->id()->string()}'>
                            {$track->data()->name()}
                        </a>
                    </td>

                    <td style='text-align: center'>{$trackDownloadCount}</td>
                    
                    <td style='text-align: right'>
                        <a href='/rallysport-content/tracks/?form=delete&id={$track->id()->string()}'>
                            delete
                        </a>
                    </td>

                </tr>
                ";
            }
        }

        return "
        <div class='rsc-table-container'>
        
            <div class='rsc-table-title'>Tracks you've uploaded</div>

            <a href='/rallysport-content/tracks/?form=add' title='Upload a new track'>
                <div class='round-button top-right'>
                    <i class='fas fa-upload'></i>
                </div>
            </a>

            <table class='rsc-table' style='width: 395px;'>

                <thead>
                    <tr>
                        <th >Name</th>
                        <th style='text-align: center'>Views</th>
                        <th style='text-align: right'>Actions</th>
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

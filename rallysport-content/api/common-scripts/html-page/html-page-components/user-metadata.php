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
require_once __DIR__."/../../database-connection/track-database.php";

// Represents a HTML element in a HTMLPage object that provides metadata about
// a registered user of Rally-Sport Content.
//
abstract class UserMetadata extends HTMLPage\HTMLPageComponent
{
    static public function html(\RSC\Resource\UserResource $user) : string
    {
        $sessionUserID       = ($_SESSION["user_resource_id"] ?? "no-session");
        $userNumPublicTracks = (new \RSC\DatabaseConnection\TrackDatabase())->tracks_count([$user->id()->string()],
                                                                                           [Resource\ResourceVisibility::PUBLIC]);

        return "
        <tr>

            <td style='font-weight: ".(($sessionUserID == $user->id()->string())? "bold" : "normal").";'>
                {$user->id()->string()}
            </td>

            <td style='text-align: center'>
                <a href='/rallysport-content/tracks/?by={$user->id()->string()}'>
                    {$userNumPublicTracks}
                </a>
            </td>

        </tr>
        ";
    }
}

<?php namespace RSC\HTMLPage\Component;
      use RSC\HTMLPage;

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
    static public function html(\RSC\Resource\UserResource $user)
    {
        $sessionUserID       = ($_SESSION["user_resource_id"] ?? "no-session");
        $userNumPublicTracks = (new \RSC\DatabaseConnection\TrackDatabase())->num_public_tracks_by_user($user->id());

        return "
        <tr>

            <td style='font-weight: ".(($sessionUserID == $user->id()->string())? "bold" : "normal").";'>
                <i class='far fa-fw fa-sm fa-user'></i>{$user->id()->string()}
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

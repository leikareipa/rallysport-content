<?php namespace RSC\HTMLPage\Component;
      use RSC\DatabaseConnection;
      use RSC\API\Session;
      use RSC\HTMLPage;
      use RSC\Resource;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../session.php";
require_once __DIR__."/../html-page-component.php";
require_once __DIR__."/../../database-connection/track-database.php";

// Represents a HTML element in a HTMLPage object that provides metadata about
// a registered user of Rally-Sport Content.
//
abstract class UserResourceMetadata extends HTMLPage\HTMLPageComponent
{
    static public function css() : string
    {
        return file_get_contents(__DIR__."/css/resource-metadata.css").
               file_get_contents(__DIR__."/css/user-resource-metadata.css");
    }

    static public function html(Resource\UserResource $user) : string
    {
        $trackDB = new DatabaseConnection\TrackDatabase();
        $numTracksByUser = $trackDB->tracks_count([$user->id()->string()], [Resource\ResourceVisibility::PUBLIC]);

        return "
        <div class='resource-metadata user'>

            <div class='card'>

                <div class='media'>
                    ".explode(Resource\ResourceID::RESOURCE_KEY_FRAGMENT_SEPARATOR, $user->id()->resource_key())[0]."
                </div>

                <div class='info-box'>

                    <div class='title'
                         title='{$user->id()->string()}'>
                        <i class='fas fa-fw fa-user'></i> {$user->id()->resource_key()}
                    </div>

                    <div class='icon-row right'>

                        <a href='/rallysport-content/users/?id={$user->id()->string()}'
                           title='Permalink'>
                            <i class='fas fa-fw fa-link'></i>
                        </a>

                        <a href='/rallysport-content/tracks/?by={$user->id()->string()}'
                           title='Tracks uploaded'>
                            <i class='fas fa-fw fa-road'></i>
                            {$numTracksByUser}
                        </a>

                    </div>

                </div>

            </div>

        </div>
        ";
    }
}

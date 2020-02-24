<?php namespace RSC\Resource;
      use RSC\DatabaseConnection;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/resource-view.php";
require_once __DIR__."/track-resource.php";
require_once __DIR__."/user-resource.php";
require_once __DIR__."/../database-connection/track-database.php";

// An abstract base for resources, like tracks and users.
abstract class Resource
{
    protected $id;                // Of class ResourceID.
    protected $creatorID;         // Of class ResourceID; user who created this resource (uploaded it to Rally-Sport Content).
    protected $data;              // Mixed.
    protected $visibility;        // Of enum class ResourceVisibility.
    protected $creationTimestamp; // Int; when the resource was created (uploaded to Rally-Sport Content).

    public function view(string $viewType)
    {
        return ResourceView::view($this, $viewType);
    }

    // Getters.
    public function id()                 { return $this->id;                }
    public function creator_id()         { return $this->creatorID;         }
    public function data()               { return $this->data;              }
    public function visibility()         { return $this->visibility;        }
    public function creation_timestamp() { return $this->creationTimestamp; }
}

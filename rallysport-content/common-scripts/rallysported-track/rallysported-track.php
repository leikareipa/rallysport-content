<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/rallysported-track-manifesto.php";
require_once __DIR__."/rallysported-track-container.php";
require_once __DIR__."/rallysported-track-display-name.php";
require_once __DIR__."/rallysported-track-internal-name.php";

// Represents the data of a track created in RallySportED.
class RallySportEDTrack
{
    public const MAX_BYTE_SIZE = (RallySportEDTrack_Manifesto::MAX_BYTE_SIZE +
                                  RallySportEDTrack_Container::MAX_BYTE_SIZE);

    private $internalName;
    private $displayName;
    private $sideLength; // Width & height (tracks are expected to be square).
    private $manifesto;
    private $container;

    public function __construct()
    {
        $this->internalName = new RallySportEDTrack_InternalName;
        $this->displayName = new RallySportEDTrack_DisplayName;
        $this->manifesto = new RallySportEDTrack_Manifesto;
        $this->container = new RallySportEDTrack_Container;
        $this->sideLength = 0;
        
        return;
    }

    public function manifesto()                           : string { return $this->manifesto->data(); }
    public function container(string $segmentName = NULL) : string { return $this->container->data($segmentName); }
    public function display_name()                        : string { return $this->displayName->string(); }
    public function internal_name()                       : string { return $this->internalName->string(); }
    public function side_length()                         : int    { return $this->sideLength; }

    // Note: Setters return true if the data were successfully set; false
    // otherwise.
    public function set_manifesto(string $newManifesto)        : bool { return $this->manifesto->set_data($newManifesto); }
    public function set_container(string $newContainer)        : bool { return $this->container->set_data($newContainer); }
    public function set_display_name(string $newDisplayName)   : bool { return $this->displayName->set_name($newDisplayName); }
    public function set_internal_name(string $newInternalName) : bool { return $this->internalName->set_name($newInternalName); }

    public function set_side_length(int $newSideLength) : bool
    {
        // Tracks can be either 64 or 128 tiles wide.
        if (self::is_valid_track_side_length($newSideLength))
        {
            $this->sideLength = $newSideLength;

            return true;
        }
        else
        {
            return false;
        }
    }

    static public function from_zip_file(string $zipFilename = NULL)
    {
        if (!$zipFilename ||
            !is_file($zipFilename) ||
            (filesize($zipFilename) > RallySportEDTrack::MAX_BYTE_SIZE))
        {
            return NULL;
        }

        $zipFile = new \ZipArchive($zipFilename);

        if (!$zipFile->open($zipFilename, \ZipArchive::CHECKCONS))
        {
            return NULL;
        }

        // Get the names of the track files inside the archive. We expect there
        // to be one directory containing exactly three files (.DTA, .$FT, and
        // HITABLE.TXT).
        $trackFilenames = [];
        {
            $i = 0;

            while (($i < 4) && ($filename = $zipFile->getNameIndex($i++)))
            {
                if ($filename === FALSE)
                {
                    return NULL;
                }

                // Ignore directories.
                if (substr($filename, -1) == "/")
                {
                    continue;
                }

                switch (strtoupper(substr($filename, -4, 4)))
                {
                    case ".TXT":  $trackFilenames["hitable"] = $filename; break;
                    case ".DTA":  $trackFilenames["container"] = $filename; break;
                    case ".\$FT": $trackFilenames["manifesto"] = $filename; break;
                    default: return NULL;
                }
            }

            if (!($trackFilenames["hitable"] ?? false) ||
                !($trackFilenames["container"] ?? false) ||
                !($trackFilenames["manifesto"] ?? false))
            {
                return NULL;
            }
        }

        // The archive's files should be inside a directory whose name defines
        // the track's internal name (e.g. "SUORUNDI/SUORUNDI.DTA" for a track
        // whose internal name is "SUORUNDI").
        {
            $internalName = (explode("/", $trackFilenames["container"])[0] ?? NULL);

            if (!RallySportEDTrack_InternalName::is_valid_internal_name($internalName) ||
                ((explode("/", $trackFilenames["hitable"])[0] ?? NULL) !== $internalName) ||
                ((explode("/", $trackFilenames["manifesto"])[0] ?? NULL) !== $internalName))
            {
                return NULL;
            }
        }

        // Extract data from the track archive.
        $trackData = [];
        {
            $trackData["container"] = $zipFile->getFromName($trackFilenames["container"], 0, \ZipArchive::FL_NOCASE);
            $trackData["manifesto"] = $zipFile->getFromName($trackFilenames["manifesto"], 0, \ZipArchive::FL_NOCASE);

            if (!$trackData["container"] ||
                !$trackData["manifesto"])
            {
                return NULL;
            }
        }

        $dataObject = new RallySportEDTrack();

        if (!$dataObject->set_internal_name($internalName) ||
            !$dataObject->set_display_name($internalName) ||
            !$dataObject->set_container($trackData["container"]) ||
            !$dataObject->set_manifesto($trackData["manifesto"]))
        {
            return NULL;
        }

        // Figure out the track's dimensions from the container data. We assume
        // that the container's data has already been verified to be validly
        // formed.
        {
            $maastoDataLen = unpack("V1", $dataObject->container(), 0)[1];

            if (!$dataObject->set_side_length(sqrt($maastoDataLen / 2)))
            {
                return NULL;
            }
        }

        return $dataObject;
    }

    // Returns true if the given track side length (width or height, as tracks
    // are expected to be square) is valid; false otherwise.
    static function is_valid_track_side_length(int $height) : bool
    {
        if (in_array($height, [64, 128]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

class RallySportEDTrackData
{
    // Maximum data lengths of elements in a track's data.
    public const MAX_MANIFESTO_BYTE_SIZE = 10240;
    public const MAX_CONTAINER_BYTE_SIZE = 307200;
    public const MAX_BYTE_SIZE = (self::MAX_MANIFESTO_BYTE_SIZE + self::MAX_CONTAINER_BYTE_SIZE);

    private $internalName;
    private $displayName;
    private $width;
    private $height;
    private $manifesto;
    private $container;

    public function __construct()
    {
        $this->internalName = "";
        $this->displayName = "";
        $this->manifesto = "";
        $this->container = "";
        $this->width = 0;
        $this->height = 0;
        
        return;
    }

    static public function from_zip_file(string $zipFilename = NULL)
    {
        if (!$zipFilename ||
            !is_file($zipFilename) ||
            (filesize($zipFilename) > RallySportEDTrackData::MAX_BYTE_SIZE))
        {
            return false;
        }

        $zipFile = new \ZipArchive($zipFilename);

        if (!$zipFile->open($zipFilename, \ZipArchive::CHECKCONS))
        {
            return false;
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
                    return false;
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
                    default: return false;
                }
            }

            if (!($trackFilenames["hitable"] ?? false) ||
                !($trackFilenames["container"] ?? false) ||
                !($trackFilenames["manifesto"] ?? false))
            {
                return false;
            }
        }

        // The archive's files should be inside a directory whose name defines
        // the track's internal name (e.g. "SUORUNDI/SUORUNDI.DTA" for a track
        // whose internal name is "SUORUNDI").
        {
            $internalName = (explode("/", $trackFilenames["container"])[0] ?? NULL);

            if (!self::is_valid_internal_track_name($internalName) ||
                ((explode("/", $trackFilenames["hitable"])[0] ?? NULL) !== $internalName) ||
                ((explode("/", $trackFilenames["manifesto"])[0] ?? NULL) !== $internalName))
            {
                return false;
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
                return false;
            }
        }

        $dataObject = new RallySportEDTrackData();

        if (!$dataObject->set_internal_name($internalName) ||
            !$dataObject->set_container_data($trackData["container"]) ||
            !$dataObject->set_manifesto_data($trackData["manifesto"]))
        {
            return false;
        }

        // Figure out the track's dimensions from the container data. We assume
        // that the container's data has already been verified to be validly
        // formed.
        {
            $maastoDataLen = unpack("V1", $dataObject->container(), 0)[1];

            if (!$dataObject->set_width(sqrt($maastoDataLen / 2)) ||
                !$dataObject->set_height(sqrt($maastoDataLen / 2)))
            {
                return false;
            }
        }

        return $dataObject;
    }

    public function manifesto() : string
    {
        return $this->manifesto;
    }

    public function container() : string
    {
        return $this->container;
    }

    public function internal_name() : string
    {
        return $this->internalName;
    }

    public function display_name() : string
    {
        return $this->displayName;
    }

    public function width() : int
    {
        return $this->width;
    }

    public function height() : int
    {
        return $this->height;
    }

    public function set_width(int $newWidth) : bool
    {
        // Tracks can be either 64 or 128 tiles wide.
        if (self::is_valid_side_length($newWidth))
        {
            $this->width = $newWidth;

            return true;
        }
        else
        {
            return false;
        }
    }

    public function set_height(int $newHeight) : bool
    {
        // Tracks can be either 64 or 128 tiles tall.
        if (self::is_valid_side_length($newHeight))
        {
            $this->height = $newHeight;

            return true;
        }
        else
        {
            return false;
        }
    }

    public function set_manifesto_data($newManifestoData) : bool
    {
        if (self::is_valid_manifesto_data($newManifestoData))
        {
            $this->manifesto = $newManifestoData;

            return true;
        }
        else
        {
            return false;
        }
    }

    public function set_container_data($newContainerData) : bool
    {
        if (self::is_valid_container_data($newContainerData))
        {
            $this->container = $newContainerData;

            return true;
        }
        else
        {
            return false;
        }
    }

    public function set_internal_name($newInternalName) : bool
    {
        $newInternalName = strtoupper($newInternalName);

        if (self::is_valid_internal_track_name($newInternalName))
        {
            $this->internalName = $newInternalName;

            return true;
        }
        else
        {
            return false;
        }
    }

    public function set_display_name($newDisplayName) : bool
    {
        if (self::is_valid_internal_track_name($newDisplayName))
        {
            $this->displayName = $newDisplayName;

            return true;
        }
        else
        {
            return false;
        }
    }

    static function is_valid_internal_track_name(string $internalName) : bool
    {
        // Internal track names are allowed to consist of 1-8 ASCII alphabet characters.
        if ((mb_strlen($internalName, "UTF-8") < 1) ||
            (mb_strlen($internalName, "UTF-8") > 8) ||
            preg_match("/[^a-zA-Z]/", $internalName))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    static function is_valid_display_track_name(string $displayTrackName) : bool
    {
        // Display names are allowed to consist of 1-15 ASCII + Finnish umlaut
        // alphabet characters.
        if ((mb_strlen($displayTrackName, "UTF-8") < 1) ||
            (mb_strlen($displayTrackName, "UTF-8") > 15) ||
            preg_match("/[^A-Za-z-.,():\/ \x{c5}\x{e5}\x{c4}\x{e4}\x{d6}\x{f6}]/u", $displayTrackName))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    // Returns true if the given track side length (width or height) is valid;
    // false otherwise.
    static function is_valid_side_length(int $sideLength) : bool
    {
        if (in_array($sideLength, [64, 128]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Scans the given container data (a binary string) on the byte level. Returns
    // true if the data appear validly formed for a container file; false otherwise.
    static function is_valid_container_data(string $containerData) : bool
    {
        $dataByteOffset = 0;
        $containerDataLen = strlen($containerData);

        // The first 4 bytes in the container should give the length of the MAASTO
        // data. The MAASTO data have one element per track tile, and each element
        // takes up 2 bytes; so the value should be track_width * track_height * 2
        // (tracks are square, so width == height).
        if (($dataByteOffset + 4) >= $containerDataLen)
        {
            return false;
        }
        $maastoDataLen = unpack("V1", $containerData, $dataByteOffset)[1];
        $trackSideLength = sqrt($maastoDataLen / 2);
        if ((($maastoDataLen % 2) != 0) ||
            !self::is_valid_side_length($trackSideLength))
        {
            return false;
        }

        // Every 2nd byte in the MAASTO data should be a value of 0, 1, or 255.
        $dataByteOffset += 4;
        for ($i = 0; $i < $maastoDataLen; $i += 2, $dataByteOffset += 2)
        {
            if ($dataByteOffset >= $containerDataLen)
            {
                return false;
            }

            $heightOffset = unpack("C1", $containerData, ($dataByteOffset + 1))[1];

            if (($heightOffset != 0) &&
                ($heightOffset != 1) &&
                ($heightOffset != 255) &&
                ($heightOffset != 15))
            {
                return false;
            }
        }

        // The next 4 bytes in the container should be the length of the VARIMAA
        // data, which should be track_width * track_height.
        if (($dataByteOffset + 4) >= $containerDataLen)
        {
            return false;
        }
        $varimaaDataLen = unpack("V1", $containerData, $dataByteOffset)[1];
        if ($varimaaDataLen != ($trackSideLength**2))
        {
            return false;
        }

        // We'll skip the VARIMAA data - the values here would all be in the range
        // 0-255.
        $dataByteOffset += (4 + $varimaaDataLen);

        // The next 4 bytes in the container should be the length of the PALAT
        // data. These data should hold about (but no more than) 256 textures,
        // 16 x 16 pixels each, where each pixel takes up one byte.
        if (($dataByteOffset + 4) >= $containerDataLen)
        {
            return false;
        }
        $palatDataLen = unpack("V1", $containerData, $dataByteOffset)[1];
        if (($palatDataLen < 65024) || ($palatDataLen > 65536))
        {
            return false;
        }

        // We'll skip the PALAT data - valid values here would be in the range
        // 0-31, but the game itself sets some of these bytes out of range, so
        // we can't expect them to be well-formed.
        $dataByteOffset += (4 + $palatDataLen);

        // The next 4 bytes in the container should be the length of the ANIMS
        // data. These data should hold about (but no more than) 256 textures,
        // 16 x 16 pixels each, where each pixel takes up one byte.
        if (($dataByteOffset + 4) >= $containerDataLen)
        {
            return false;
        }
        $animsDataLen = unpack("V1", $containerData, $dataByteOffset)[1];
        if (($animsDataLen < 65024) || ($animsDataLen > 65536))
        {
            return false;
        }

        // We'll skip the ANIMS data - valid values here would be in the range
        // 0-31, but the game itself sets some of these bytes out of range, so
        // we can't expect them to be well-formed.
        $dataByteOffset += (4 + $animsDataLen);

        // The next 4 bytes in the container should be the length of the TEXT
        // data. These data are expected to have a fixed length.
        if (($dataByteOffset + 4) >= $containerDataLen)
        {
            return false;
        }
        $textDataLen = unpack("V1", $containerData, $dataByteOffset)[1];
        if ($textDataLen != 32768)
        {
            return false;
        }

        // We'll skip the TEXT data - valid values here would be in the range
        // 0-31, but the game itself sets some of these bytes out of range, so
        // we can't expect them to be well-formed.
        $dataByteOffset += (4 + $textDataLen);

        // The next 4 bytes in the container should be the length of the KIERROS
        // data. Each element in these data is 8 bytes, and there can be at most
        // about 512 elements.
        if (($dataByteOffset + 4) >= $containerDataLen)
        {
            return false;
        }
        $kierrosDataLen = unpack("V1", $containerData, $dataByteOffset)[1];
        if (($kierrosDataLen < 8) || ($kierrosDataLen > 4096))
        {
            return false;
        }

        // The KIERROS data should end in eight bytes of 0xff.
        $dataByteOffset += (4 + $kierrosDataLen - 8);
        for ($i = 0; $i < 8; $i++, $dataByteOffset++)
        {
            if ($dataByteOffset >= $containerDataLen)
            {
                return false;
            }

            if (unpack("C1", $containerData, $dataByteOffset)[1] != 255)
            {
                return false;
            }
        }

        // We should now be at the end of the container's data.
        if ($dataByteOffset != $containerDataLen)
        {
            return false;
        }

        return true;
    }

    // Scans the given manfiesto data (a plaintext string) line by line. Returns
    // true if the data appear validly formed for a manifesto file; false otherwise.
    static function is_valid_manifesto_data(string $manifestoData) : bool
    {
        $manifestoLines = explode("\n", $manifestoData);
        if (!is_array($manifestoLines))
        {
            return false;
        }

        if (empty($manifestoLines[count($manifestoLines)-1]))
        {
            array_pop($manifestoLines);
        }

        $commandsInManifesto = [];

        foreach ($manifestoLines as $line)
        {
            if (empty($line))
            {
                return false;
            }

            // A manifesto line consists of two parts: the command, and a set of
            // parameters. E.g. in the manifesto line "4 2 6", 4 is the command,
            // and 2 and 6 are its two parameters.
            $parameters = explode(" ", $line);
            $command = array_shift($parameters);

            $commandsInManifesto[$command] = true;

            // Verify that each command has the correct number of parameters.
            switch ($command)
            {
                case 0:  if (count($parameters) != 3) return false; break;
                case 1:  if (count($parameters) != 1) return false; break;
                case 2:  if (count($parameters) != 1) return false; break;
                case 3:  if (count($parameters) != 5) return false; break;
                case 4:  if (count($parameters) != 2) return false; break;
                case 5:  if (count($parameters) != 5) return false; break;
                case 6:  if (count($parameters) != 4) return false; break;
                case 10: if (count($parameters) != 4) return false; break;
                case 99: if (count($parameters) != 0) return false; break;
                default: return false; // Unrecognized command.
            }

            // TODO: Verify that each command's parameters are within their valid ranges.
        }

        // A manifesto file needs to have at least these two commands.
        if (!isset($commandsInManifesto[0]) ||
            !isset($commandsInManifesto[99]))
        {
            return false;
        }

        return true;
    }
}

<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/rallysported-track.php";

// Represents the container data of a track created in RallySportED.
class RallySportEDTrack_Container
{
    public const MAX_BYTE_SIZE = 307200;

    private $container;

    public function __construct()
    {
        $this->container = "";

        return;
    }

    // Returns the container's data as a binary string. If no segment name (e.g.
    // "kierros") is given, the entire container is returned; otherwise, returns
    // only the given segment's data. If a segment name is specified but not
    // found, an empty string is returned.
    public function data(string $segmentName = NULL) : string
    {
        if (!$segmentName)
        {
            return $this->container;
        }

        $segmentName = strtolower($segmentName);

        // Step through the container data segment by segment until we find the
        // requested segment, then return that segment's data.
        $offset = 0;
        foreach (["maasto","varimaa","palat","anims","text","kierros"] as $segment)
        {
            $segmentLength = unpack("V1", $this->container, $offset)[1];
            $offset += 4;

            if (($offset + $segmentLength) > strlen($this->container))
            {
                break;
            }

            if ($segment == $segmentName)
            {
                return substr($this->container, $offset, $segmentLength);
            }

            $offset += $segmentLength;
        }

        // If we couldn't find the requested segment.
        return "";
    }

    public function set_data($newContainerData) : bool
    {
        if (self::is_valid_container($newContainerData))
        {
            $this->container = $newContainerData;

            return true;
        }
        else
        {
            return false;
        }
    }

    // Scans the given container data (a binary string) on the byte level. Returns
    // true if the data appear validly formed for a container file; false otherwise.
    static public function is_valid_container(string $containerData) : bool
    {
        $dataByteOffset = 0;
        $containerDataLen = strlen($containerData);

        if ($containerDataLen > self::MAX_BYTE_SIZE)
        {
            return false;
        }

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
            !RallySportEDTrack::is_valid_track_side_length($trackSideLength))
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
        if (($textDataLen < 32768) ||
            ($textDataLen > 33792))
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
}

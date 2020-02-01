<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality for validating a RallySportED container
 * file's data. You might use it e.g. to check whether a container file sent
 * from a client is malformed.
 * 
 * For more information about the container file, see the documentation in
 * RallySportED's repos, https://github.com/leikareipa/rallysported/.
 * 
 */

// Scans the given container data (a binary string) on the byte level. Returns
// true if the data appear validly formed for a container file; false otherwise.
// The 'trackSideLength' parameter gives the number of tiles per side on the
// container's track (track dimensions are assumed square).
function is_valid_container_data(string $containerData,
                                 int $trackSideLength) : bool
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
    if (($maastoDataLen != ($trackSideLength**2 * 2)) ||
        (($maastoDataLen % 2) != 0))
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

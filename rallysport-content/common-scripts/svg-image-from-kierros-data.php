<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

// Generates and returns an SVG image (as a string) in which the KIERROS data
// of the given container is indicated with a line. If fails to create such
// an image, returns an placeholder SVG which prints out an error message.
//
// Assumes tha the container is valid; i.e. that we don't need to check for
// overflow while reading, etc.
//
function svg_image_from_kierros_data(string $trackDataContainer)
{
    // The string returned if we fail to generate a valid SVG.
    $nullImage = "
    <svg width='100%' height='100%' viewBox='0 0 100 100'>
        <text font-size='7' text-anchor='middle' x='50%' y='50%'>Image unavailable</text>
    </svg>";

    $containerByteOffset = 0;

    // Seek to the beginning of the KIERROS data.
    {
        $maastoByteLength = unpack("V1", $trackDataContainer, $containerByteOffset)[1];
        $containerByteOffset += (4 + $maastoByteLength);

        $varimaaByteLength = unpack("V1", $trackDataContainer, $containerByteOffset)[1];
        $containerByteOffset += (4 + $varimaaByteLength);

        $palatByteLength = unpack("V1", $trackDataContainer, $containerByteOffset)[1];
        $containerByteOffset += (4 + $palatByteLength);

        $animsByteLength = unpack("V1", $trackDataContainer, $containerByteOffset)[1];
        $containerByteOffset += (4 + $animsByteLength);

        $textByteLength = unpack("V1", $trackDataContainer, $containerByteOffset)[1];
        $containerByteOffset += (4 + $textByteLength);
    }

    $kierrosByteLength = unpack("V1", $trackDataContainer, $containerByteOffset)[1];
    $containerByteOffset += 4;

    $trackSideLength = sqrt($varimaaByteLength);

    // Tracks can only be 64 or 128 units per side in size.
    if (($trackSideLength != 64) &&
        ($trackSideLength != 128))
    {
        return $nullImage;
    }

    // Each checkpoint is 8 bytes. We assume that the last checkpoint is just
    // 8 bytes of 0xff and can be ignored.
    $numCheckpoints = (($kierrosByteLength / 8) - 1);

    if (($numCheckpoints <= 1) ||
        ($numCheckpoints > 512))
    {
        return $nullImage;
    }

    // The string into which we'll create the image.
    $svgString = "<svg width='100%' height='100%' viewBox='0 0 ".($trackSideLength * 128)." ".($trackSideLength * 128)."' preserveAspectRatio='none'><polygon points='";

    // Add the <polyline> points. Each point receives a checkpoint's X,Y
    // coordinates.
    for ($i = 0; $i < $numCheckpoints; $i++)
    {
        $checkpointData = unpack("v4", $trackDataContainer, $containerByteOffset);
        $containerByteOffset += 8;

        $checkpointX = $checkpointData[1];
        $checkpointY = $checkpointData[2];

        $svgString .= "{$checkpointX},{$checkpointY} ";
    }

    // Close the polygon element. We expect the target HTML page to use CSS for
    // styling the SVG.
    $svgString .= "'/></svg>";

    return $svgString;
}

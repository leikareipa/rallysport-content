<?php namespace RSC;

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
function svg_image_from_kierros_data(string $trackDataContainer) : string
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

    // A value in the range (0,1] used to scale from Rally-Sport's world coordinates
    // into coordinates in the SVG. For instance, if this value is 0.5, then the
    // Rally-Sport coordinate value 453 would be stored as floor(453 * 0.5) = 226.
    // Without this reduction in scale, the SVG coordinate space would be on the
    // order of 16384 x 16384, which is larger than we need - though we still want
    // to keep it large enough, like 4096 x 4096, so that lines connecting points
    // in the coordinate system appear smooth when the SVG is displayed on the page.
    // (A smaller coordinate system also results in fewer bytes required to store
    // the image, since the individual coordinate value strings will be shorter.)
    $svgCoordinateScale = 0.125;

    // Create the SVG image.
    $svgString = "";
    {
        $svgString = "<svg width='100%' height='100%' viewBox='0 0 ".
                        (int)floor($trackSideLength * (128 * $svgCoordinateScale))." ".
                        (int)floor($trackSideLength * (128 * $svgCoordinateScale)).
                        "' preserveAspectRatio='none'>".
                     "<polygon vector-effect='non-scaling-stroke' points='";

        // Add the <polyline> points. Each point receives a checkpoint's X,Y
        // coordinates.
        for ($i = 0; $i < $numCheckpoints; $i++)
        {
            $checkpointData = unpack("v4", $trackDataContainer, $containerByteOffset);
            $containerByteOffset += 8;

            $checkpointX = (int)floor($checkpointData[1] * $svgCoordinateScale);
            $checkpointY = (int)floor($checkpointData[2] * $svgCoordinateScale);

            $svgString .= "{$checkpointX},{$checkpointY}";

            if ($i < ($numCheckpoints - 1))
            {
                $svgString .= " ";
            }
        }

        $svgString .= "'/></svg>";
    }

    return $svgString;
}

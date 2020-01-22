<?php namespace RallySportContent;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 * This script provides functionality for validating a RallySportED manifesto
 * file's data. You might use it e.g. to check whether a manifesto file sent
 * from a client is malformed.
 * 
 * For more information about the manifesto file, see the documentation in
 * RallySportED's repos, https://github.com/leikareipa/rallysported/.
 * 
 */

// Scans the given manfiesto data (a plaintext string) line by line. Returns
// true if the data appear validly formed for a manifesto file; false otherwise.
function is_valid_manifesto_data(string $manifestoData) : bool
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

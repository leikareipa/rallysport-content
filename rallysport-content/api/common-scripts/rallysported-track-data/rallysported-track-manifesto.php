<?php namespace RSC;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

 // Represents the manifesto data of a track created in RallySportED.
class RallySportEDTrackData_Manifesto
{
    public const MAX_BYTE_SIZE = 10240;

    private $manifesto;

    public function __construct()
    {
        $this->manifesto = "";

        return;
    }

    public function data() : string
    {
        return $this->manifesto;
    }

    public function set_data($newManifestoData) : bool
    {
        $sanitizedManifestoData = self::sanitized_manifesto_data($newManifestoData);
        
        if (!$sanitizedManifestoData)
        {
            return false;
        }
        else
        {
            $this->manifesto = $sanitizedManifestoData;
            return true;
        }
    }

    // Scans the given manifesto data (a plaintext string) line by line. Returns
    // a sanitized version of the string; or FALSE on error (e.g. if the input
    // data are invalid).
    static public function sanitized_manifesto_data(string $manifestoData)
    {
        if (strlen($manifestoData) > self::MAX_BYTE_SIZE)
        {
            return false;
        }
        
        // The manifesto's newlines could feasibly come in a variety of
        // newline styles, so convert all to \n format.
        $manifestoData = str_replace("\r\n", "\n", $manifestoData);
        $manifestoData = str_replace("\r", "\n", $manifestoData);

        $manifestoLines = explode("\n", $manifestoData);
        if (!is_array($manifestoLines))
        {
            return false;
        }

        // Sanitize the manifesto data.
        {
            $sanitizedManifestoLines = [];

            foreach ($manifestoLines as $line)
            {
                if (empty($line))
                {
                    continue;
                }

                // We'll accept tabs and spaces as separators, but for simplicity,
                // let's convert every allowed separator to a single space.
                $line = preg_replace('/[ \t]{1,}/', " ", $line);

                $sanitizedManifestoLines[] = $line;
            }
        }

        // Verify that the sanitized manifesto data appear valid.
        {
            $commandsInManifesto = [];

            foreach ($sanitizedManifestoLines as $line)
            {
                // A manifesto line consists of two parts: the command, and a set of
                // parameters. E.g. in the manifesto line "4 2 6\n", 4 is the command,
                // and 2 and 6 are its two parameters.
                $parameters = explode(" ", $line);
                $command = array_shift($parameters);

                $commandsInManifesto[(int)$command] = true;

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

                // We require the manifesto to be compatible with RallySportED
                // Loader v.5 or later.
                if (($command == 0) &&
                    (($parameters[2] ?? -1) < 5))
                {
                    return false;   
                }
            }

            // A manifesto file needs to have at least these two commands.
            if (!isset($commandsInManifesto[0]) ||
                !isset($commandsInManifesto[99]))
            {
                return false;
            }
        }

        // Replace the original manifesto data with its sanitized version. Note
        // that we use DOS-compatible newlines, since that's what the DOS-based
        // RallySportED Loader expects.
        $manifestoData = (implode("\r\n", $sanitizedManifestoLines) . "\r\n");

        return $manifestoData;
    }
}

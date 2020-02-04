<?php namespace RSC;

/*
 * Original source: phpMyAdmin 4.5.5.1
 *      CODE: https://github.com/phpmyadmin/phpmyadmin/blob/RELEASE_4_5_5_1/libraries/zip.lib.php
 *      LICENSE: https://github.com/phpmyadmin/phpmyadmin/blob/RELEASE_4_5_5_1/LICENSE
 * 
 * SUPERFICIALLY MODIFIED by Tarpeeksi Hyvae Soft (2020) for use in Rally-Sport
 * Content. Please diff against the original source file, linked above, to find
 * what has changed.
 * 
 * Creates a ZIP archive in memory and returns its bytes as a string.
 * 
 * Usage:
 * 
 *  1. Create a new ZipFile object:
 *     $zip = new ZipFile();
 * 
 *  2. Add a file (whose data is given as a string) into the archive:
 *     $zip->add_file($fileName, $fileData, $unixTimestamp);
 *
 *     The file name can contain a relative path: e.g. "DIR/FILE.TXT", which
 *     would store FILE.TXT inside a directory called DIR.
 * 
 *  3. Repeat step (2) for each file you want to include in the archive.
 * 
 *  4. Get the archive's byte data as a string:
 *     $zip->string();
 * 
 */

class ZipFile
{
    // Holds the zip file's compressed data.
    private $datasec;

    private $centralDirectory = array();

    public const CENTRAL_DIRECTORY_EOF = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    private $lastOffsetPos = 0;

    public function __construct()
    {
        $this->datasec = [];
        $this->centralDirectory = [];
        $this->lastOffsetPos = 0;

        return;
    }

    // Converts an Unix timestamp to a four byte DOS date and time format (date
    // in high two bytes, time in low two bytes allowing magnitude comparison).
    private function unix2DosTime($unixtime = 0) : int
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980)
        {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }

        return (($timearray['year'] - 1980) << 25)
               | ($timearray['mon'] << 21)
               | ($timearray['mday'] << 16)
               | ($timearray['hours'] << 11)
               | ($timearray['minutes'] << 5)
               | ($timearray['seconds'] >> 1);
    }

    // Adds the given file's data into the zip file.
    public function add_file($name, $data, $time = 0) : void
    {
        $name     = str_replace('\\', '/', $name);

        $hexdtime = pack('V', $this->unix2DosTime($time));

        $fr  = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";            // ver needed to extract
        $fr .= "\x00\x00";            // gen purpose bit flag
        $fr .= "\x08\x00";            // compression method
        $fr .= $hexdtime;             // last mod time and date

        // "local file header" segment
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len   = strlen($zdata);
        $fr      .= pack('V', $crc);             // crc32
        $fr      .= pack('V', $c_len);           // compressed filesize
        $fr      .= pack('V', $unc_len);         // uncompressed filesize
        $fr      .= pack('v', strlen($name));    // length of filename
        $fr      .= pack('v', 0);                // extra field length
        $fr      .= $name;

        // "file data" segment
        $fr .= $zdata;

        $this->datasec[] = $fr;
        
        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";               // version made by
        $cdrec .= "\x14\x00";               // version needed to extract
        $cdrec .= "\x00\x00";               // gen purpose bit flag
        $cdrec .= "\x08\x00";               // compression method
        $cdrec .= $hexdtime;                // last mod time & date
        $cdrec .= pack('V', $crc);          // crc32
        $cdrec .= pack('V', $c_len);        // compressed filesize
        $cdrec .= pack('V', $unc_len);      // uncompressed filesize
        $cdrec .= pack('v', strlen($name)); // length of filename
        $cdrec .= pack('v', 0);             // extra field length
        $cdrec .= pack('v', 0);             // file comment length
        $cdrec .= pack('v', 0);             // disk number start
        $cdrec .= pack('v', 0);             // internal file attributes
        $cdrec .= pack('V', 32);            // external file attributes
                                            // - 'archive' bit set

        $cdrec .= pack('V', $this->lastOffsetPos); // relative offset of local header
        $this->lastOffsetPos += strlen($fr);

        $cdrec .= $name;

        // optional extra field, file comment goes here
        // save to central directory
        $this->centralDirectory[] = $cdrec;

        return;
    }

    // Return the current contents of the zip file as a string.
    public function string() : string
    {
        $ctrldir = implode('', $this->centralDirectory);

        $header = $ctrldir .
                  self::CENTRAL_DIRECTORY_EOF .
                  pack('v', sizeof($this->centralDirectory)) .  // total #of entries "on this disk"
                  pack('v', sizeof($this->centralDirectory )) . // total #of entries overall
                  pack('V', strlen($ctrldir)) .                 // size of central dir
                  pack('V', $this->lastOffsetPos) .             // offset to start of central dir
                  "\x00\x00";                                   // .zip file comment length

        $data = implode('', $this->datasec);

        return ($data . $header);
    }
}

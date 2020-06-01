<?php namespace RSC\API\HelpTopic;

/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

require_once __DIR__."/../../../../common-scripts/user/user-password-characteristics.php";
require_once __DIR__."/../../../../common-scripts/html-page/html-page-components/help-topic.php";

// Provides instructions on uploading a track resource.
abstract class UploadATrack extends \RSC\HTMLPage\Component\HelpTopic
{
    static public function id() : string
    {
        return "upload-a-track";
    }

    static public function inner_title() : string
    {
        return "Submitting a new track";
    }

    static public function inner_html() : string
    {
        return "
        <p>Registered users can submit new tracks to Rally-Sport Content.
        The form to upload a track can be found
        <a href='/rallysport-content/tracks/?form=add'>here<i class='fas fa-fw fa-sm fa-lock'></i></a>.
        </p>

        <p>Before submitting a track, you should be aware of the following:
            <ul>
                <li>All submissions are manually reviewed by administration and
                may be rejected for any reason (none will be specified). While a
                new submission is being reviewed, you'll see a \"Processing...\"
                next to its name on your account's
                <a href='/rallysport-content/tracks/?form=add'>control panel<i class='fas fa-fw fa-sm fa-lock'></i></a>.
                The review process can take hours, days, weeks, or even longer &ndash;
                administration works on a volunteer basis, so no guarantees can be
                given, although a best effort is made.
                </li>

                <li>The track would be publically and freely available to any visitor
                of Rally-Sport Content without further stipulation  from you.
                </li>
                
                <li>Anybody who obtains the track can modify it and upload the original
                and/or modified version onto Rally-Sport Content from their own user account,
                without crediting you.
                </li>

                <li>This non-profit Rally-Sport Content service is provided in good
                faith and isn't backed by resources for legal battles. Should any
                relevant instance demand the removal from Rally-Sport Content of a track
                you've submitted, or should such a need arise otherwise, the track will
                be removed without your consent.
                </li>
            </ul>

        <p>To submit a track using the upload form (linked to, above), all you
        need to provide is the track's ZIP file as exported from
        <a href='/rallysported/'>RallySportED-js</a>.
        </p>

        <p>You should, however, first leaf through
        <a href='/rallysported/user-guide/'>the RallySportED-js user guide</a>.
        Most importantly, you should be aware that the track's name will be
        determined by the directory structure inside the ZIP archive. For instance,
        a track will be called \"Suorundi\" on Rally-Sport Content if it has
        the following ZIP archive:
        </p>

        <pre>
        SUORUNDI.ZIP (or whatever filename you prefer)
         |
         +--> SUORUNDI/
               |
               +--> SUORUNDI.DTA
               |
               +--> SUORUNDI.\$FT
               |
               +--> HITABLE.TXT
        </pre>

        </p>Here, the track's directory is called SUORUNDI, which will be stylized
        as \"Suorundi\" when displayed in Rally-Sport Content. The individual track
        data files inside the directory are required to be named after the directory,
        i.e. SUORUNDI in this case. The name of the ZIP file has no effect and can
        be whatever you want &ndash; it won't be displayed publically nor stored
        on Rally-Sport Content.
        </p>

        </p>If you wanted to submit this track under a different name, let's say
        \"Massiivi\", you'd need to rename the ZIP archive's directories and files
        like so:
        </p>

        <pre>
        MASSIIVI.ZIP (or SUORUNDI.ZIP, or whatever you prefer)
         |
         +--> MASSIIVI/
               |
               +--> MASSIIVI.DTA
               |
               +--> MASSIIVI.\$FT
               |
               +--> HITABLE.TXT
        </pre>

        Track names are limited to ASCII A-Z and can be between 1 and 8 characters long.

        <p>Once submitted, a track can't be modified in any way except by full
        deletion. You can choose to delete a track by clicking on the
        <i class='fas fa-sm fa-times'></i> icon next to the track's name on your account's
        <a href='/rallysport-content/tracks/?form=add'>control panel<i class='fas fa-fw fa-sm fa-lock'></i></a>.
        If this icon isn't available (and you see something like \"Processing...\"
        instead), the track submission hasn't yet been reviewed by administration,
        and you need to wait a bit longer.
        ";
    }
}

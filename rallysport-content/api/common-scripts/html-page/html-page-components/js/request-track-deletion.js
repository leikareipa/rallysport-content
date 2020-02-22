/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

"use strict";

// Sends a query to the Rally-Sport Content REST API (to which a base URL - e.g.
// "https://xxx.xxx.xxx/rallysport-content" - is to be given as the first parameter)
// asking for the deletion of the track identified by the given Rally-Sport Content
// track ID (e.g. "track.xxx-xxx-xxx").
//
function request_track_deletion(trackID)
{
    fetch(`/rallysport-content/tracks/?id=${trackID}`,
    {
        method: "DELETE",
    })
    .then(response=>
    {
        if (response.redirected)
        {
            window.location.href = response.url;
        }
    });

    return;
}

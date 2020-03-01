/*
 * 2020 Tarpeeksi Hyvae Soft
 * 
 * Software: Rally-Sport Content
 * 
 */

"use strict";

// Sends a query to the Rally-Sport Content REST API asking for the deletion of
// the track resource identified by the given Rally-Sport Content track ID (e.g.
// "track.xxx-xxx-xxx").
function request_track_deletion(trackID)
{
    fetch(`/rallysport-content/tracks/?id=${trackID}`,
    {
        method: "DELETE",
    })
    .then(response=>
    {
        if (!response.ok)
        {
            window.alert("Track deletion failed.");
            location.reload();
        }
        else if (response.redirected)
        {
            window.location.href = response.url;
        }
    });

    return;
}

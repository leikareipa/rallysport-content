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
// Since tracks can only be deleted by their uploaders, the function will first
// query the user to input their user ID and password, which will be submitted with
// the request body.
//
function request_track_deletion(rallySportContentURL, trackID)
{
    let userID = "";
    let password = "";

    /// TODO: A better mechanism for querying the username and password.
    if (((userID = window.prompt(`Track removal requires authentication.\n\nEnter your user ID:`, "user.xxx-xxx-xxx")) === null) ||
        ((password = window.prompt(`Track removal requires authentication.\n\nEnter your password:`, "")) === null))
    {
        window.alert("Authentication interrupted.");
        return;
    }

    if (!userID.length ||
        !password.length)
    {
        window.alert("Invalid credentials.");
        return;
    }

    fetch(`${rallySportContentURL}/tracks/?id=${trackID}`,
    {
        method: "DELETE",
        body: JSON.stringify({
            userID: userID,
            password: password,
        }),
    })
    .then(response=>
    {
        if (!response.ok)
        {
            window.alert("Track deletion failed.");
        }
        else
        {
            location.reload();
        }
    });

    return;
}

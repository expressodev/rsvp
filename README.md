# RSVP for ExpressionEngine

RSVP gives your members the ability to respond to events, with a Facebook-style interface.

* Content publishers can specify a maximum number of places available for each event
* Members can optionally select how many places to reserve (e.g. if they are bringing a friend)
* Members receive a short confirmation email when they respond to events
* Administrators can view & export the attendance list for an event
* Administrators can send email updates to all members attending an event
* A list of attending members can be displayed on your website

## Installation

To install the RSVP module for ExpressionEngine, simply upload the entire `rsvp` folder to `system/expressionengine/third_party` on your web server. You should then see RSVP appear in the modules list under Add-Ons > Modules in your web site’s control panel. Click ‘Install’ next to the module to get started.

Configuring RSVP is simple. Your events are stored in a standard ExpressionEngine channel, so if you don’t have a channel for events, go ahead and set one up first. You will probably want to add custom fields to the channel, such as the event location and time (or you can use the entry publish date to record the event date and time).

When you first install RSVP and visit the control panel page, you will be prompted to specify your events channel. Once you have set the events channel, you will see a shiny new ‘RSVP’ tab on your publish page for that channel. You can move and rearrange the fields on this tab to a just like any other custom fields.

Before members can respond to events in your channel, you must enable RSVPs for that event. When you create new events, RSVP will be enabled by default. You can change this behaviour in the RSVP preferences page.

## Documentation

### Attendance Tag

    {exp:rsvp:attendance entry_id="{entry_id}" limit="10"}

Displays the list of attendees for an event. For a minimal setup, simply add a single attendance tag, you will be given a list of attendees to your event.

#### Attendance Tag Parameters

* `entry_id=”{entry_id}”` - The event entry ID to list attendance for.
* `orderby=”date”` - Specify the display order of your attendees. Defaults to`“date` (when the RSVP was last updated). Valid values are: `date`, `member_id`, `group_id`, `username`, `screen_name`, `email`
* `sort=”desc”` - Reverse the display order of your attendees. Can be `asc` or `desc`.
* `limit=”10”` - Limit the number of attendees displayed. Highly recommended.
* `offset=”10”` - Offset the displayed results. Useful for displaying paginated lists of attendees.

#### Attendance Tag Variables

All member fields are available by prepending `attendee_`. For example:

* `attendee_screen_name`
* `attendee_username`

This is done to avoid conflicts with the member fields which are already available inside a channel entry loop.

* `{if no_attendance}` - This conditional works similarly to the `{if no_results}` tag inside a regular channel entries loop. Its contents will be displayed if there are no attendees for the specified event.

### Member Events Tag

    {exp:rsvp:member_events parse="inward"}
        {exp:channel:entries channel="events" entry_id="1|{entry_ids}"}
            <!-- event details -->
        {/exp:channel:entries}
    {/exp:rsvp:member_events}

Fetch entry IDs of events the current user is attending. This tag simply returns a pipe-separated list of entry IDs, which can be fed into the channel entries tag. Be sure to use `parse="inward"` on the `member_events` tag so that the `{entry_ids}` variable is parsed before your channel entries loop. It also pays to prepend a known invalid `entry_id` to the result, so that if `{entry_ids}` evaluates to an empty string, the channel entries tag does not simply return all events.

#### Member Events Tag Parameters

* `member_id=”CURRENT_USER”` - The member ID for which to return event entry IDs for. Defaults to the current user.
* `parse=”inward”` - This is a standard ExpressionEngine parameter which causes this tag to be parsed before the inner tags.

#### Member Events Tag Variables

* `{entry_ids}` - A pipe-separated list of entry IDs, corresponding to events which the member has responded to.

### RSVP Enabled Tag

    {exp:rsvp:if_rsvp_enabled entry_id="{entry_id}"}
        ... (other RSVP tags) ...
    {/exp:rsvp:if_rsvp_enabled}

A conditional tag to display content only if RSVP has been enabled for the entry. If you always enable RSVPs for your events, then you don’t need to use this tag. Otherwise, you may want to enclose everything related to RSVP inside a giant if statement using this tag.

#### RSVP Enabled Tag Parameters

* `entry_id=”{entry_id}”` - The entry ID of the event.

#### RSVP Enabled Tag Variables

None. This tag will either display all or none of its contents.

### RSVP Form Tag

    {exp:rsvp:rsvp_form}

Displays a form which allows users to respond to events. You will get a basic form, with all the RSVP options available, including members requesting multiple seats, and a space for members to write notes. If you want to customise this form, you can use a pair of rsvp_form tags, with anything between them.

#### RSVP Form Tag Parameters

* `entry_id=”{entry_id}”` - The entry ID of the event.
* `return=”path/to/return”` - Specify a path to redirect the user to after their RSVP is submitted. Defaults to the current page.

#### RSVP Form Tag Variables

* `{rsvp_seats}` - The number of seats the current user has already reserved.
* `{total_seats}` - The total number of seats available for this event.
* `{total_seats_reserved}` - The total number of seats reserved so far for this event.
* `{total_seats_remaining}` - The total number of available seats remaining for this event.
* `{total_members_responded}` - The total number of members who have responded so far to this event.

#### RSVP Form Tag Example

    {exp:rsvp:rsvp_form entry_id="{entry_id}"}
        <div class="rsvp_form">
            {if logged_in}
            {if rsvp_seats > 0}
                <p>You have already responded to this event.</p>
                <p><strong>Edit your response:</strong></p>
            {if:elseif total_seats > 0 AND total_seats_remaining == 0}
                <p>Sorry, this event has sold out!</p>
            {if:else}
                <p><strong>Respond to this event:</strong></p>
                {if total_seats > 0}
                    {if total_seats_remaining == 1}
                          <p>Hurry, there is only 1 seat remaining!</p>
                    {if:elseif total_seats_remaining <= 10}
                        <p>Hurry, there are only {total_seats_remaining} seats remaining!</p>
                    {/if}
                {/if}
            {/if}
            {if total_seats == 0 OR rsvp_seats > 0 OR total_seats_remaining > 0}
                <label for="rsvp_seats">Seats Required</label>
                <select name="rsvp_seats">
                    <option value="1" {if rsvp_seats <= 1} selected="selected"{/if}>1</option>
                    {if total_seats == 0 OR total_seats_remaining >= 2}<option value="2" {if rsvp_seats == 2} selected="selected"{/if}>2</option>{/if}
                    {if total_seats == 0 OR total_seats_remaining >= 3}<option value="3" {if rsvp_seats == 3} selected="selected"{/if}>3</option>{/if}
                    {if total_seats == 0 OR total_seats_remaining >= 4}<option value="4" {if rsvp_seats == 4} selected="selected"{/if}>4</option>{/if}
                    {if total_seats == 0 OR total_seats_remaining >= 5}<option value="5" {if rsvp_seats == 5} selected="selected"{/if}>5</option>{/if}
                </select><br />
                <label for="rsvp_notes">Notes (e.g. dietary requirements, names of additional attendees)</label><br />
                <textarea name="rsvp_notes" rows="4" cols="30">{rsvp_notes}</textarea><br />
                <input name="rsvp_public" type="checkbox" value="y" {if rsvp_public == "y"}checked="checked"{/if} />
                <label for="rsvp_public">Make my attendance status public</label><br />
                {if rsvp_seats > 0}<input type="submit" name="rsvp_cancel" value="Cancel my RSVP" />{/if}
                    <input type="submit" name="rsvp_submit" value="{if rsvp_seats > 0}Update RSVP{if:else}Send RSVP{/if}" />
                {/if}
            {if:else}
                <p>Please log in or register to respond to this event.</p>
            {/if}
        </div>
    {/exp:rsvp:rsvp_form}

## Changelog

### RSVP 1.2.3
*Released May 18, 2013*

* Fixed `channel_model` not loaded automatically in ExpressionEngine 2.6

### RSVP 1.2.2
*Released January 26, 2013*

* Added support for ExpressionEngine 2.5.5
* First open source release

### RSVP 1.2.1
*Released December 19, 2011*

* Added orderby and sort parameters to the rsvp:attendance tag
* Added BCC field for email notifications
* Visual fixes for ExpressionEngine 2.3
* Added support for NSM Addon Updater

### RSVP 1.2.0
*Released July 1, 2011*

* Updated for EE 2.2
* Added `return=””` parameter to rsvp_form tag
* Added member_events tag

### RSVP 1.1.1
*Released February 28, 2011*

* Renamed lang file to follow new naming conventions

### RSVP 1.1.0
*Released January 16, 2011*

* Added MSM support

### RSVP 1.0.2
*Released December 20, 2010*

* Fixed error calling CI_Model constructor in EE 2.1.2

### RSVP 1.0.1
*Released November 17, 2010*

* Added {exp:rsvp:if_rsvp_enabled} tag
* Added ‘Edit Entry’ button to the RSVP control panel page.
* Added ‘offset’ parameter to {exp:rsvp:attendance} tag
* Added {attendee_count} and {attendee_total_results} variables to {exp:rsvp:attendance} tag
* Automatically add <p> and <br /> tags to HTML-formatted emails

### RSVP 1.0.0
*Released November 3, 2010*

* Initial release

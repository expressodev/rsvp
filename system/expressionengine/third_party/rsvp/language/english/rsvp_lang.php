<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(
	// module details
	'rsvp_module_name' => 'RSVP',
	'rsvp_module_description' => 'RSVP is a complete event management module',

	// publish page
	'rsvp' => 'RSVP',
	'rsvp_enabled' => 'RSVP Status',
	'rsvp_enabled_y' => 'Enable RSVPs',
	'rsvp_enabled_n' => 'Disable RSVPs',
	'rsvp_total_seats' => 'RSVP Seats Available',
	'rsvp_total_seats_instructions' => 'Specify the total number of seats available, or leave empty for unlimited seats.',

	// control panel
	'rsvp_events' => 'Events',
	'rsvp_event_details' => 'Event Details',
	'rsvp_edit_entry' => 'Edit Entry',
	'rsvp_attendance_export' => 'Export Attendance List',
	'rsvp_email_attendees' => 'Email Attendees',
	'rsvp_event_return' => 'Return to Event',
	'send' => 'Send',

	'rsvp_error' => 'RSVP Error: ',
	'error_invalid_template' => 'Missing required entry_id parameter in your template, e.g. exp:rsvp:rsvp_form entry_id="{entry_id}"',
	'error_sold_out' => 'User tried to respond to a sold-out event!',
	'error_insufficient_seats' => 'User tried to reserve more seats than are available!',

	'entry_id_invalid' => 'Invalid entry_id specified!',
	'entry_id_not_event' => 'There is no event associated with entry %d!',
	'email_no_attendees' => 'You cannot email attendees because there is currently no-one attending this event!',
	'channel_id_not_set' => 'To enable RSVP, please select the channel you currently store events in.',

	'settings_updated' => 'Your settings have been saved.',

	'event_id' => '#',
	'event_entry_id' => 'Entry ID',
	'event_title' => 'Title',
	'event_date' => 'Entry Date',
	'event_seats_available' => 'Total Seats Available',
	'event_seats_reserved' => 'Seats Reserved',
	'event_seats_reserved_format' => '%1$d by %2$d members (%3$s remaining)',
	'event_communicate' => 'Communicate',
	'event_view_all' => 'view all attendees...',
	'event_message_sent' => 'Thank you. Your message has been sent.',
	'event_member' => '1 member',
	'event_members' => '%s members',

	'event_attendee' => 'Attendee',
	'event_email' => 'Email',
	'event_seats' => 'Seats',
	'event_response_date' => 'Response Date',
	'event_notes' => 'Notes',

	// default module tag text
	'rsvp_already_responded' => 'You have already responded to this event.',
	'rsvp_edit_response' => 'Edit your response:',
	'rsvp_sold_out' => 'Sorry, this event has sold out!',
	'rsvp_respond' => 'Respond to this event:',
	'rsvp_hurry_one_seat' => 'Hurry, there is only 1 seat remaining!',
	'rsvp_hurry_seats' => 'Hurry, there are only {total_seats_remaining} seats remaining!',
	'rsvp_seats_required' => 'Seats Required',
	'rsvp_notes_eg' => 'Notes (e.g. dietary requirements, names of additional attendees)',
	'rsvp_attendance_public' => 'Make my attendance status public',
	'rsvp_cancel' => 'Cancel my RSVP',
	'rsvp_update' => 'Update RSVP',
	'rsvp_send' => 'Send RSVP',
	'rsvp_please_login' => 'Please log in or register to respond to this event.',
	'rsvp_no_attendance' => 'No one is attending this event yet. You could be the first!',

	// settings
	'rsvp_channel_id' => 'Events Channel',
	'rsvp_channel_id_subtext' => 'This is the channel your site uses to store events.',
	'rsvp_enabled_default' => 'Default RSVP status',
	'rsvp_from_email' => 'Event Reminder Email Address',
	'rsvp_from_email_subtext' => 'The email address event reminder emails will be sent from. If left blank will use site default.',
	'rsvp_from_name' => 'Event Reminder Email Name',
	'rsvp_from_name_subtext' => 'The name event reminder emails will be sent as. If left blank will use site default.',
	'rsvp_email_bcc' => 'Event Reminder BCC Address',
	'rsvp_email_bcc_subtext' => 'All notification emails will be copied to this (comma-separated) address list.',
	'rsvp_attendance_export_fields' => 'Attendance Export Fields',
	'rsvp_attendance_export_fields_subtext' => 'Enter a comma-separated list of fields to export in SQL format, e.g. <br /><span style="font-style: italic;">members.member_id,email,seats_reserved,public,notes AS special_requirements,my_custom_field</span>',
);

/* End of file lang.rsvp.php */
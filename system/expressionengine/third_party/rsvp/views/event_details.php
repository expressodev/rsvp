<?php
	// event details
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(lang('rsvp_event_details'), '');

	$this->table->add_row(lang('event_entry_id'), $event['entry_id']);
	$this->table->add_row(lang('event_title'), $event['title']);
	$this->table->add_row(lang('event_date'), $this->localize->human_time($event['entry_date']));
	$this->table->add_row(lang('event_seats_available'), $event['total_seats'] == 0 ? 'unlimited' : $event['total_seats']);
	$this->table->add_row(lang('event_seats_reserved'),
			sprintf(lang('event_seats_reserved_format'),
				$event['total_seats_reserved'],
				$event['total_members_responded'],
				$event['total_seats'] == 0 ? 'unlimited' : $event['total_seats_remaining'],
				$event['total_seats']));

	echo $this->table->generate();
?>

<div style="padding: 5px 0 15px 0;">
	<a title="<?= lang('rsvp_edit_entry'); ?>" class="submit" href="<?= $edit_entry_link; ?>"><?= lang('rsvp_edit_entry'); ?></a>
	<a title="<?= lang('rsvp_attendance_export'); ?>" class="submit" href="<?= $attendance_export_link; ?>"><?= lang('rsvp_attendance_export'); ?></a>
	<a title="<?= lang('rsvp_email_attendees'); ?>" class="submit" href="<?= $email_link; ?>"><?= lang('rsvp_email_attendees'); ?></a>
</div>

<?php
	// attendance list
	$this->table->clear();
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(lang('event_attendee'), lang('event_email'), lang('event_seats'), lang('event_response_date'), lang('event_notes'));

	foreach ($attendance as $user)
	{
		$this->table->add_row(
			'<a href="'.$user['member_link'].'">'.$user['screen_name'].'</a>',
			'<a href="mailto:'.$user['email'].'">'.$user['email'].'</a>',
			$user['seats_reserved'],
			$this->localize->set_human_time($user['updated']),
			$user['notes']
		);
	}

	echo $this->table->generate();
?>

<?= $pagination ?>

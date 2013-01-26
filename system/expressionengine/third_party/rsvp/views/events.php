<?php
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		lang('event_id'),
		lang('event_title'),
		lang('event_seats_reserved'),
		lang('event_communicate')
	);

	foreach($events as $event)
	{
		$this->table->add_row(
			$event['entry_id'],
			'<a href="'.$event['details_link'].'">'.$event['title'].'</a>',
			sprintf(lang('event_seats_reserved_format'),
				$event['total_seats_reserved'],
				$event['total_members_responded'],
				$event['total_seats'] == 0 ? 'unlimited' : $event['total_seats_remaining'],
				$event['total_seats']),
			'<a href="'.$event['email_link'].'">'.lang('rsvp_email_attendees').'</a>'
		);
	}

	echo $this->table->generate();
?>

<?= $pagination ?>
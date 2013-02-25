<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of RSVP for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Rsvp_mcp {

	private $EE;

	function __construct()
	{
		$this->EE =& get_instance();

		if (!$this->EE->cp->allowed_group('can_access_members'))
		{
			show_error($this->lang->line('unauthorized_access'));
		}

		$this->EE->lang->loadfile('design');

		$this->EE->load->library(array('javascript', 'rsvp_config', 'table'));
		$this->EE->load->helper('form');
		$this->EE->load->model('rsvp_model');

		define('RSVP_CP', 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=rsvp');
		$this->EE->cp->set_breadcrumb(BASE.AMP.RSVP_CP, lang('rsvp_module_name'));

		$this->EE->cp->set_right_nav(array(
			'rsvp_events' => BASE.AMP.RSVP_CP.AMP.'method=events',
			'preferences' => BASE.AMP.RSVP_CP.AMP.'method=settings',
			'documentation' => $this->EE->cp->masked_url('http://github.com/expressodev/rsvp'),
		));
	}

	/*
	 *	Helper functions
	 */

	function get_pagination_config($base_url, $total_rows, $perpage = 50)
	{
		return array(
			'base_url' => $base_url,
			'total_rows' => $total_rows,
			'per_page' => $perpage,
			'page_query_string' => TRUE,
			'query_string_segment' => 'rownum',
			'full_tag_open' => '<p id="paginationLinks">',
			'full_tag_close' => '</p>',
			'prev_link' => '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />',
			'next_link' => '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />',
			'first_link' => '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />',
			'last_link' => '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />'
		);
	}

	function get_event_data()
	{
		// fetch data for the curent event
		$entry_id = (int)$this->EE->input->get('entry_id');
		if ($entry_id === 0)
		{
			$this->EE->session->set_flashdata('message_failure', lang('entry_id_invalid'));
			$this->EE->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=events');
		}

		$event_data = $this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data))
		{
			$this->EE->session->set_flashdata('message_failure', sprintf(lang('entry_id_not_event'), $entry_id));
			$this->EE->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=events');
		}

		return $event_data;
	}

	/*
	 *	Control Panel pages
	 */

	function index()
	{
		$this->EE->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=events');
	}

	function events()
	{
		$this->EE->cp->set_variable('cp_page_title', lang('rsvp_events'));

		// get pagination
		$rownum = (int)$this->EE->input->get('rownum');
		$perpage = (int)$this->EE->input->get('perpage');
		if ($perpage == 0)
		{
			$perpage = (int)$this->EE->input->cookie('perpage');
			if ($perpage == 0) { $perpage = 50; }
		}

		// check events channel has been set
		$channel_info = $this->EE->channel_model->get_channel_info($this->EE->rsvp_config->item('rsvp_channel_id'));
		if ($channel_info->num_rows() == 0)
		{
			$this->EE->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=settings');
		}

		// load data
		$data['events'] = $this->EE->rsvp_model->get_rsvp_events($perpage, $rownum)->result_array();
		$events_count = $this->EE->rsvp_model->count_all_rsvp_events();

		foreach ($data['events'] as $key => $row)
		{
			$data['events'][$key]['details_link'] = BASE.AMP.RSVP_CP.AMP.'method=event_details'.AMP.'entry_id='.$row['entry_id'];
			$data['events'][$key]['email_link'] = BASE.AMP.RSVP_CP.AMP.'method=email_attendees'.AMP.'entry_id='.$row['entry_id'];
		}

		// configure pagination
		$this->EE->load->library('pagination');
		$page_config = $this->get_pagination_config(BASE.AMP.RSVP_CP.AMP.'method=events'.AMP.'perpage='.$perpage, $events_count, $perpage);
		$this->EE->pagination->initialize($page_config);
		$data['pagination'] = $this->EE->pagination->create_links();

		// return view
		return $this->EE->load->view('events', $data, TRUE);
	}

	function settings()
	{
		$this->EE->cp->set_variable('cp_page_title', lang('preferences'));
		$data = array('post_url' => RSVP_CP.AMP.'method=settings');

		// check for submitted form
		if (isset($_POST['settings']))
		{
			$settings = $this->EE->input->post('settings', TRUE);

			// strip disallowed SQL keywords and invalid chars from export fields list
			if (isset($settings['rsvp_attendance_export_fields']))
			{
				$settings['rsvp_attendance_export_fields'] = preg_replace('/[^\w\-\.\, \"]+|(\b(from|join|select|union)\b)|--/i', '',
					$settings['rsvp_attendance_export_fields']);
			}

			if (isset($settings['rsvp_channel_id']))
			{
				$settings['rsvp_channel_id'] = (int)$settings['rsvp_channel_id'];
			}

			foreach ($settings as $key => $value)
			{
				$this->EE->rsvp_config->set_item($key, $value);
			}

			$this->EE->rsvp_config->save();
			$this->EE->session->set_flashdata('message_success', lang('settings_updated'));
			$this->EE->functions->redirect(BASE.AMP.RSVP_CP);
		}

		// load existing settings data
		$channels = array('s', array(0 => ''), 0);
		$channels_query = $this->EE->channel_model->get_channels()->result_array();
		foreach ($channels_query as $channel)
		{
			$channels[1][$channel['channel_id']] = $channel['channel_title'];
		}

		$this->EE->rsvp_config->set_item_default('rsvp_channel_id', $channels);

		$data['settings'] = $this->EE->rsvp_config->all_items();
		foreach ($data['settings'] as $key => $value)
		{
			$data['settings'][$key] = array(
				'value' => $value,
				'input' => $this->EE->rsvp_config->item_input($key, "settings[{$key}]")
			);
		}

		// display settings form
		return $this->EE->load->view('settings', $data, TRUE);
	}

	function event_details()
	{
		// load details for the specified event
		$data['event'] = $this->get_event_data();

		// get pagination
		$rownum = (int)$this->EE->input->get('rownum');
		$perpage = (int)$this->EE->input->get('perpage');
		if ($perpage == 0)
		{
			$perpage = (int)$this->EE->input->cookie('perpage');
			if ($perpage == 0) { $perpage = 50; }
		}

		$data['attendance'] = $this->EE->rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $data['event']['entry_id'],
			'limit' => $perpage,
			'offset' => $rownum,
		))->result_array();
		foreach ($data['attendance'] as $key => $row)
		{
			$data['attendance'][$key]['member_link'] = BASE.AMP.'C=myaccount'.AMP.'id='.$row['member_id'];
		}

		$data['attendance_export_link'] = BASE.AMP.RSVP_CP.AMP.'method=attendance_export'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['email_link'] = BASE.AMP.RSVP_CP.AMP.'method=email_attendees'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['edit_entry_link'] = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$data['event']['channel_id'].AMP.'entry_id='.$data['event']['entry_id'];

		// configure pagination
		$this->EE->load->library('pagination');
		$current_url = BASE.AMP.RSVP_CP.AMP.'method=event_details'.AMP.'entry_id='.$data['event']['entry_id'].AMP.'perpage='.$perpage;
		$page_config = $this->get_pagination_config($current_url, $data['event']['total_members_responded'], $perpage);
		$this->EE->pagination->initialize($page_config);
		$data['pagination'] = $this->EE->pagination->create_links();

		// load view
		$this->EE->cp->set_breadcrumb(BASE.AMP.RSVP_CP.AMP.'method=events', lang('rsvp_events'));
		$this->EE->cp->set_variable('cp_page_title', $data['event']['title']);
		return $this->EE->load->view('event_details', $data, TRUE);
	}

	function attendance_export()
	{
		// check the specified event is valid
		$event = $this->get_event_data();

		// convert custom field names in export_fields to database column names
		$export_fields = ' '.$this->EE->rsvp_config->item('rsvp_attendance_export_fields').' ';
		$member_fields = $this->EE->member_model->get_all_member_fields()->result_array();
		foreach ($member_fields as $field)
		{
			$pattern = '/([ ,])('.$field['m_field_name'].')([ ,])/i';
			$replacement = '$1m_field_id_'.$field['m_field_id'].' as $2$3';
			$export_fields = preg_replace($pattern, $replacement, $export_fields);
		}
		// clean up any double 'as' clauses caused by custom field names
		$export_fields = preg_replace('/ as [^,]* as /i', ' as ', $export_fields);

		$attendance = $this->EE->rsvp_model->get_rsvp_attendance(array(
			'select' => $export_fields,
			'entry_id' => $event['entry_id'],
		));

		$this->EE->load->dbutil();
		$this->EE->load->helper('download');
		force_download($event['url_title'].'-attendance-'.date('Ymd').'.csv', $this->EE->dbutil->csv_from_result($attendance));
	}

	function email_attendees()
	{
		$this->EE->lang->loadfile('communicate');

		// load details for the specified event
		$data['event'] = $this->get_event_data();
		$data['current_uri'] = RSVP_CP.AMP.'method=email_attendees'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['event_details_link'] = BASE.AMP.RSVP_CP.AMP.'method=event_details'.AMP.'entry_id='.$data['event']['entry_id'];

		// check for a success message
		if ($this->EE->session->flashdata('message_success'))
		{
			return $this->EE->load->view('email_attendees_thanks', $data, TRUE);
		}

		// check there are actually members attending the event
		if ($data['event']['total_members_responded'] == 0)
		{
			$this->EE->session->set_flashdata('message_failure', lang('email_no_attendees'));
			$this->EE->functions->redirect($data['event_details_link']);
		}

		// get attendance information
		$data['attendance'] = $this->EE->rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $data['event']['entry_id']))->result_array();
		foreach ($data['attendance'] as $key => $row)
		{
			$data['attendance'][$key]['member_link'] = BASE.AMP.'C=myaccount'.AMP.'id='.$row['member_id'];
		}

		// configure default email
		$data['mailtype_options'] = array('text' => lang('plain_text'), 'html' => lang('html'));
		$data['word_wrap_options'] = array('y' => lang('on'), 'n' => lang('off'));

		$email = array(
			'from'		 	=> $this->EE->rsvp_config->item('rsvp_from_email'),
			'name'			=> $this->EE->rsvp_config->item('rsvp_from_name'),
			'cc'			=> '',
			'bcc'			=> '',
			'subject' 		=> $data['event']['title'],
			'message'		=> '',
			'mailtype'		=> $this->EE->config->item('mail_format'),
			'wordwrap'		=> $this->EE->config->item('word_wrap')
		);

		if (empty($email['from']))
		{
			$email['from'] = $this->EE->config->item('webmaster_email');
			$email['name'] = $this->EE->config->item('webmaster_name');
		}

		// set page title and breadcrumb
		$this->EE->cp->set_breadcrumb(BASE.AMP.RSVP_CP.AMP.'method=events', lang('rsvp_events'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.RSVP_CP.AMP.'method=event_details'.AMP.'entry_id='.$data['event']['entry_id'], $data['event']['title']);
		$this->EE->cp->set_variable('cp_page_title', lang('rsvp_email_attendees'));

		// check for submitted form
		if ($this->EE->input->post('submit') !== FALSE)
		{
			// get submitted values
			foreach ($email as $key => $value)
			{
				$post_value = $this->EE->input->post($key, TRUE);
				if ($post_value !== FALSE)
				{
					$email[$key] = $post_value;
				}
			}

			// validate form
			$this->EE->load->library('form_validation');
			$this->EE->form_validation->set_rules('subject', 'lang:subject', 'required');
			$this->EE->form_validation->set_rules('message', 'lang:message', 'required');
			$this->EE->form_validation->set_rules('from', 'lang:from', 'required|valid_email');
			$this->EE->form_validation->set_rules('cc', 'lang:cc', 'valid_emails');
			$this->EE->form_validation->set_rules('bcc', 'lang:bcc', 'valid_emails');
			$this->EE->form_validation->set_error_delimiters('<br /><strong class="notice">', '</strong><br />');

			if ($this->EE->form_validation->run() === TRUE)
			{
				// configure email
				$this->EE->load->library('email');
				$this->EE->email->wordwrap  = ($email['wordwrap'] == 'y') ? TRUE : FALSE;

				if ($email['mailtype'] == 'html')
				{
					$this->EE->load->library('typography');
					$this->EE->typography->initialize();
					$email['message'] = $this->EE->typography->auto_typography($email['message']);
					$this->EE->email->mailtype = 'html';
				}
				else
				{
					$this->EE->email->mailtype = 'text';
				}

				// check for users who don't want to receive admin email
				if ($this->EE->input->post('accept_admin_email') == 'y')
				{
					foreach ($data['attendance'] as $key => $attendee)
					{
						if ($attendee['accept_admin_email'] != 'y')
						{
							unset($data['attendance'][$key]);
						}
					}
				}

				if (!empty($email['cc']) OR !empty($email['bcc']))
				{
					// send a separate email to cc/bcc
					$this->EE->email->EE_initialize();
					$this->EE->email->from($email['from'], $email['name']);
					$this->EE->email->to('');
					$this->EE->email->cc($email['cc']);
					$this->EE->email->bcc($email['bcc']);
					$this->EE->email->subject($email['subject']);
					$this->EE->email->message($email['message']);

					if (!$this->EE->email->send())
					{
						show_error(lang('error_sending_email').BR.BR.implode(BR, $this->EE->email->_debug_msg));
					}
				}

				foreach ($data['attendance'] as $attendee)
				{
					// email each attendee individually
					$this->EE->email->EE_initialize();
					$this->EE->email->from($email['from'], $email['name']);
					$this->EE->email->to($attendee['email']);
					$this->EE->email->subject($email['subject']);
					$this->EE->email->message($email['message']);

					if (!$this->EE->email->send())
					{
						show_error(lang('error_sending_email').BR.BR.implode(BR, $this->EE->email->_debug_msg));
					}
				}

				// return success message
				$this->EE->session->set_flashdata('message_success', lang('event_message_sent'));
				$this->EE->functions->redirect(BASE.AMP.$data['current_uri']);
			}
		}

		// load view
		$data = array_merge($data, $email);
		$data['attendance'] = array_slice($data['attendance'], 0, 10);
		return $this->EE->load->view('email_attendees', $data, TRUE);
	}
}

/* End of file mcp.rsvp.php */

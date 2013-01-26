<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of RSVP for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Rsvp {

	private $EE;

	function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('rsvp');
		$this->EE->load->library('rsvp_config');
		$this->EE->load->model('rsvp_model');
	}

	/**
	 * DEPRECATED: Returns 0 or 1 depending on whether RSVP is enabled for a specific entry.
	 *             Use the {exp:rsvp:if_rsvp_enabled} tag instead, for easier parsing.
	 */
	function rsvp_enabled()
	{
		// returning TRUE or FALSE throws parser errors for some reason
		$entry_id = (int)$this->EE->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		return $this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->num_rows() > 0 ? 1 : 0;
	}

	/**
	 * Allows you to easily and cleanly hide an RSVP form if the event is not RSVP enabled
	 */
	function if_rsvp_enabled()
	{
		$entry_id = (int)$this->EE->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		if ($this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->num_rows() > 0)
		{
			return $this->EE->TMPL->tagdata;
		}
		else
		{
			return '';
		}
	}

	function rsvp_form()
	{
		$this->EE->load->helper('form');

		// display any error messages from a submitted form
		$rsvp_error = $this->EE->session->flashdata('rsvp_error');
		if (!empty($rsvp_error))
		{
			return '<p>'.lang('rsvp_error').$rsvp_error.'</p>';
		}

		$entry_id = (int)$this->EE->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		$event_data = $this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data)) { return; }

		$entry_id = $event_data['entry_id'];

		// is the user logged in?
		$member_id = (int)$this->EE->session->userdata['member_id'];

		// get any existing RSVP
		$event_rsvp = $this->EE->rsvp_model->get_rsvp_response($entry_id, $member_id)->row_array();

		// this array will store variables used in the tagdata
		$tag_vars = array();

		// fill the tagdata variables with event info
		foreach (array('total_seats', 'total_seats_reserved', 'total_members_responded', 'total_seats_remaining') as $var)
		{
			$tag_vars[0][$var] = $event_data[$var];
		}

		// check for existing response
		if (empty($event_rsvp))
		{
			$tag_vars[0]['rsvp_seats'] = 0;
			$tag_vars[0]['rsvp_notes'] = '';
			$tag_vars[0]['rsvp_public'] = 'y';
		}
		else
		{
			$tag_vars[0]['rsvp_seats'] = $event_rsvp['seats_reserved'];
			$tag_vars[0]['rsvp_notes'] = $event_rsvp['notes'];
			$tag_vars[0]['rsvp_public'] = $event_rsvp['public'];

			// available seats should include any the member has already reserved
			$tag_vars[0]['total_seats_remaining'] += $event_rsvp['seats_reserved'];
		}

		$hidden_fields = array(
			'entry_id' => $entry_id,
			'return_url' => $this->EE->TMPL->fetch_param('return'),
		);

		if ($hidden_fields['return_url'] === FALSE)
		{
			$hidden_fields['return_url'] = $this->EE->uri->uri_string;
		}

		// start our form output
		$out = $this->EE->functions->form_declaration(array(
			'action' => $this->EE->functions->fetch_site_index().QUERY_MARKER.
							'ACT='.$this->EE->functions->fetch_action_id('Rsvp', 'update_rsvp_response'),
			'hidden_fields' => $hidden_fields
		));

		// default tagdata if nothing is specified
		if (trim($this->EE->TMPL->tagdata) === '')
		{
			$this->EE->TMPL->tagdata = '
				<div class="rsvp_form">
					{if logged_in}
						{if rsvp_seats > 0}
							<p>'.lang('rsvp_already_responded').'</p>
							<p><strong>'.lang('rsvp_edit_response').'</strong></p>
						{if:elseif total_seats > 0 AND total_seats_remaining == 0}
							<p>'.lang('rsvp_sold_out').'</p>
						{if:else}
							<p><strong>'.lang('rsvp_respond').'</strong></p>
							{if total_seats > 0}
								{if total_seats_remaining == 1}
									<p>'.lang('rsvp_hurry_one_seat').'</p>
								{if:elseif total_seats_remaining <= 10}
									<p>'.lang('rsvp_hurry_seats').'</p>
								{/if}
							{/if}
						{/if}
						{if total_seats == 0 OR rsvp_seats > 0 OR total_seats_remaining > 0}
							<label for="rsvp_seats">'.lang('rsvp_seats_required').'</label>
							<select name="rsvp_seats">
								<option value="1" {if rsvp_seats <= 1} selected="selected"{/if}>1</option>
								{if total_seats == 0 OR total_seats_remaining >= 2}<option value="2" {if rsvp_seats == 2} selected="selected"{/if}>2</option>{/if}
								{if total_seats == 0 OR total_seats_remaining >= 3}<option value="3" {if rsvp_seats == 3} selected="selected"{/if}>3</option>{/if}
								{if total_seats == 0 OR total_seats_remaining >= 4}<option value="4" {if rsvp_seats == 4} selected="selected"{/if}>4</option>{/if}
								{if total_seats == 0 OR total_seats_remaining >= 5}<option value="5" {if rsvp_seats == 5} selected="selected"{/if}>5</option>{/if}
							</select><br />
							<label for="rsvp_notes">'.lang('rsvp_notes_eg').'</label><br />
							<textarea name="rsvp_notes" rows="4" cols="30">{rsvp_notes}</textarea><br />
							<input name="rsvp_public" type="checkbox" value="y" {if rsvp_public == "y"}checked="checked"{/if} />
							<label for="rsvp_public">'.lang('rsvp_attendance_public').'</label><br />
							{if rsvp_seats > 0}<input type="submit" name="rsvp_cancel" value="'.lang('rsvp_cancel').'" />{/if}
							<input type="submit" name="rsvp_submit" value="{if rsvp_seats > 0}'.lang('rsvp_update').'{if:else}'.lang('rsvp_send').'{/if}" />
						{/if}
					{if:else}
						<p>'.lang('rsvp_please_login').'</p>
					{/if}
				</div>
			';
		}

		// parse tagdata variables
		$out .= $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $tag_vars);

		// end form output and return
		$out .= '</form>';
		return $out;
	}

	function attendance()
	{
		$entry_id = (int)$this->EE->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		$event_data = $this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data)) { return; }

		$rsvp_attendance = $this->EE->rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $entry_id,
			'public' => TRUE,
			'limit' => (int)$this->EE->TMPL->fetch_param('limit'),
			'offset' => (int)$this->EE->TMPL->fetch_param('offset'),
			'order_by' => $this->EE->TMPL->fetch_param('orderby'),
			'sort' => $this->EE->TMPL->fetch_param('sort'),
		));

		$tag_vars = array();
		foreach ($rsvp_attendance->result_array() as $key => $response)
		{
			foreach ($response as $field => $value)
			{
				$tag_vars[$key]['attendee_'.$field] = $value;
			}

			$tag_vars[$key]['attendee_count'] = $key + 1;
			$tag_vars[$key]['attendee_total_results'] = $rsvp_attendance->num_rows();
		}

		// default tagdata if nothing is specified
		if (trim($this->EE->TMPL->tagdata) === '')
		{
			$this->EE->TMPL->tagdata = '
				{if no_attendance}
					'.lang('rsvp_no_attendance').'
				{/if}
				{attendee_screen_name}<br />
			';
		}

		// check for an empty result set
		if (empty($tag_vars))
		{
			// based on no_results code in ./system/expressionengine/libraries/Template.php
			if (strpos($this->EE->TMPL->tagdata, 'if no_attendance') !== FALSE && preg_match("/".LD."if no_attendance".RD."(.*?)".LD.'\/'."if".RD."/s", $this->EE->TMPL->tagdata, $match))
			{
				if (stristr($match[1], LD.'if'))
				{
					$match[0] = $this->EE->functions->full_tag($match[0], $this->EE->TMPL->tagdata, LD.'if', LD.'\/'."if".RD);
				}

				// return the no_attendance template
				return substr($match[0], strlen(LD."if no_attendance".RD), -strlen(LD.'/'."if".RD));
			}
			else
			{
				// nothing to return
				return;
			}
		}
		else
		{
			// parse the template as normal
			return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $tag_vars);
		}
	}

	public function member_events()
	{
		$member_id = $this->EE->TMPL->fetch_param('member_id');
		if ($member_id === FALSE OR $member_id == 'CURRENT_USER')
		{
			$member_id = $this->EE->session->userdata['member_id'];
		}

		if (empty($member_id))
		{
			$entry_ids = '';
		}
		else
		{
			$entry_ids = $this->EE->rsvp_model->get_member_events($member_id)->result_array();
			foreach ($entry_ids as $key => $row)
			{
				$entry_ids[$key] = $row['entry_id'];
			}
			$entry_ids = implode('|', $entry_ids);
		}

		return str_replace('{entry_ids}', $entry_ids, $this->EE->TMPL->tagdata);
	}

	function update_rsvp_response()
	{
		$entry_id = (int)$this->EE->input->post('entry_id');
		$member_id = (int)$this->EE->session->userdata['member_id'];
		$event = $this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();

		$return_url = $this->EE->functions->create_url($this->EE->input->post('return_url', TRUE));

		if ($entry_id === 0 OR $member_id === 0 OR empty($event))
		{
			$this->EE->functions->redirect($return_url);
		}

		// get any existing RSVP response
		$response = $this->EE->rsvp_model->get_rsvp_response($entry_id, $member_id)->row_array();

		// create a new response
		$data = array(
			'entry_id' => $entry_id,
			'member_id' => $member_id
		);

		// validate input data
		$rsvp_seats = (int)$this->EE->input->post('rsvp_seats');
		if ($this->EE->input->post('rsvp_cancel') !== FALSE)
		{
			$data['seats_reserved'] = 0;
		}
		elseif ($rsvp_seats > 0)
		{
			$data['seats_reserved'] = $rsvp_seats;
		}
		else
		{
			$data['seats_reserved'] = 1;
		}

		$data['notes'] = substr(trim($this->EE->input->post('rsvp_notes', TRUE)), 0, 255);
		$data['public'] = $this->EE->input->post('rsvp_public') === 'y' ? 'y' : 'n';

		// available seats should include any the member has already reserved
		$total_seats_available = $event['total_seats_remaining'];
		if (isset($response['seats_reserved']))
		{
			$total_seats_available += $response['seats_reserved'];
		}

		// check the form XID matches
		if ($this->EE->security->secure_forms_check($this->EE->input->post('XID')) === FALSE)
		{
			$this->EE->functions->redirect($return_url);
		}
		// check the event is not sold out
		elseif ($data['seats_reserved'] > 0 AND $event['total_seats'] > 0 AND $total_seats_available < 1)
		{
			$this->EE->session->set_flashdata('rsvp_error', lang('error_sold_out'));
			$this->EE->functions->redirect($return_url);
		}
		// check the number of seats available
		elseif ($event['total_seats'] > 0 AND $data['seats_reserved'] > $total_seats_available)
		{
			$this->EE->session->set_flashdata('rsvp_error', lang('error_insufficient_seats'));
			$this->EE->functions->redirect($return_url);
		}
		else
		{
			// submit RSVP, email confirmation, and refresh page
			$this->EE->rsvp_model->update_rsvp_response($data);
			$this->_send_rsvp_confirmation($event, $data);
			$this->EE->functions->redirect($return_url);
		}
	}

	function _send_rsvp_confirmation($event, $response)
	{
		$this->EE->load->library(array('email', 'template'));
		$this->EE->load->helper('text');

		$this->EE->email->wordwrap = true;

		// parse confirmation email template
		if ($response['seats_reserved'] === 0)
		{
			$template = $this->EE->functions->fetch_email_template('cm_rsvp_cancellation');
		}
		else
		{
			$template = $this->EE->functions->fetch_email_template('cm_rsvp_confirmation');
		}

		if (empty($template['title']) OR empty($template['data'])) { return; }

		$vars = array(
			'site_name'	=> stripslashes($this->EE->config->item('site_name')),
			'site_url'	=> $this->EE->config->item('site_url'),
			'name'		=> $this->EE->session->userdata('screen_name'),
		);
		$vars = array(array_merge($event, $response, $vars));
		$email_title = $this->EE->template->parse_variables($template['title'], $vars);
		$email_body = $this->EE->template->parse_variables($template['data'], $vars);

		// sender address defaults to site webmaster email
		if ($this->EE->rsvp_config->item('rsvp_from_email') == '')
		{
			$this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
		}
		else
		{
			$this->EE->email->from($this->EE->rsvp_config->item('rsvp_from_email'), $this->EE->rsvp_config->item('rsvp_from_name'));
		}

		// do we have a BCC address?
		if ($this->EE->rsvp_config->item('rsvp_email_bcc'))
		{
			$this->EE->email->bcc($this->EE->rsvp_config->item('rsvp_email_bcc'));
		}

		// send message
		$this->EE->email->to($this->EE->session->userdata['email']);
		$this->EE->email->subject(entities_to_ascii($email_title));
		$this->EE->email->message(entities_to_ascii($email_body));
		$this->EE->email->send();
	}
}

/* End of file mod.rsvp.php */
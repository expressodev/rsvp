<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of RSVP for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Rsvp_tab
{
	function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('rsvp');
		$this->EE->load->library('rsvp_config');
		$this->EE->load->model('rsvp_model');
	}

	function publish_tabs($channel_id, $entry_id = '')
	{
		// only show RSVP fields for specific channels
		if ($channel_id != $this->EE->rsvp_config->item('rsvp_channel_id'))
		{
			return array();
		}

		// default values
		$rsvp_enabled = $this->EE->rsvp_config->item('rsvp_enabled_default');
		$rsvp_total_seats = '';

		// check for existing values
		if ($entry_id)
		{
			$event = $this->EE->rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
			if (empty($event))
			{
				$rsvp_enabled = 'n';
			}
			else
			{
				$rsvp_enabled = 'y';
				$rsvp_total_seats = $event['total_seats'] == 0 ? '' : $event['total_seats'];
			}
		}

		// configure tabs
		$tabs = array(
			array('field_id'			=> 'rsvp_enabled',
				'field_label'			=> lang('rsvp_enabled'),
				'field_required'		=> 'n',
				'field_type'			=> 'select',
				'field_data'			=> $rsvp_enabled,
				'field_list_items'		=> array('y' => lang('rsvp_enabled_y'), 'n' => lang('rsvp_enabled_n')),
				'field_instructions'	=> '',
				'field_pre_populate'	=> 'n',
				'field_pre_field_id'	=> '',
				'field_pre_channel_id'	=> '',
				'field_text_direction'	=> 'ltr'),

			array('field_id'			=> 'rsvp_total_seats',
				'field_label'			=> lang('rsvp_total_seats'),
				'field_type'			=> 'text',
				'field_data'			=>	$rsvp_total_seats,
				'field_required'		=> 'n',
				'field_instructions'	=> lang('rsvp_total_seats_instructions'),
				'field_text_direction'	=> 'ltr',
				'field_maxl'			=> '10')
		);

		return $tabs;
	}

	function validate_publish($params)
	{
		return FALSE;
	}

	function publish_data_db($params)
	{
	    if (isset($params['mod_data']['rsvp_enabled']))
	    {
		    if ($params['mod_data']['rsvp_enabled'] == 'y')
    		{
    			// enable event
    			$this->EE->rsvp_model->update_rsvp_event(array(
    				'entry_id' => $params['entry_id'],
    				'total_seats' => (int)$params['mod_data']['rsvp_total_seats']
    			));
    		}
    		else
    		{
    			// disable event
    			$this->EE->rsvp_model->remove_rsvp_event($params['entry_id']);
    		}
    	}
	}

	function publish_data_delete_db($params)
	{
		// remove event and all responses
		$this->EE->rsvp_model->remove_rsvp_event($params['entry_ids'], TRUE);
	}
}

/* End of file tab.rsvp.php */
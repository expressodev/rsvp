<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of RSVP for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include(PATH_THIRD.'rsvp/config.php');

class Rsvp_upd
{
	public $version = RSVP_VERSION;

	function __construct()
	{
		$this->EE =& get_instance();
	}

	function install()
	{
		$this->EE->load->dbforge();
		$this->EE->load->library('layout');

		// register module
		$this->EE->db->insert('modules', array(
			'module_name' => 'Rsvp',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'y'));

		// register form actions
		$this->EE->db->insert('actions', array(
			'class'		=> 'Rsvp',
			'method'	=> 'update_rsvp_response'));

		// add exp_modules settings column if it doesn't exist
		if (!$this->EE->db->field_exists('settings', 'modules'))
		{
			$this->EE->dbforge->add_column('modules', array(
				'settings'		=> array('type' => 'text',
										'null' => TRUE)
			));
		}

		// add rsvp events table
		$this->EE->dbforge->add_field(array(
			'entry_id'			=> array('type' => 'int',
										'constraint' => '10',
										'unsigned' => TRUE),
			'total_seats'		=> array('type' => 'int',
										'constraint' => '10',
										'null' => FALSE)
		));

		$this->EE->dbforge->add_key('entry_id', TRUE);
		$this->EE->dbforge->create_table('cm_rsvp_events');

		// add rsvp responses table
		$this->EE->dbforge->add_field(array(
			'entry_id'		=> array('type' => 'int',
									'constraint' => '10',
									'unsigned' => TRUE),
			'member_id' 	=> array('type' => 'int',
									'constraint' => '10',
									'unsigned' => TRUE),
			'seats_reserved' => array('type' => 'int',
									'constraint' => '10'),
			'public'		=> array('type' => 'char',
									'constraint' => '1'),
			'notes'			=> array('type' => 'varchar',
									'constraint' => '255'),
			'updated'		=> array('type' => 'int',
									'constraint' => '10')
		));

		$this->EE->dbforge->add_key('entry_id', TRUE);
		$this->EE->dbforge->add_key('member_id', TRUE);
		$this->EE->dbforge->create_table('cm_rsvp_responses');

		// register tabs
		$this->EE->layout->add_layout_tabs($this->tabs(), 'rsvp');

		// add custom email templates
		$this->EE->db->insert('specialty_templates', array(
			'template_name'	=> 'cm_rsvp_confirmation',
			'data_title'	=> '{title}',
			'template_data'	=> <<<EOF
Hi {name},

Thank you for registering your attendance to this event.

Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}
Seats Reserved: {seats_reserved}
Notes: {notes}

We look forward to seeing you there!
EOF
		));

		$this->EE->db->insert('specialty_templates', array(
			'template_name'	=> 'cm_rsvp_cancellation',
			'data_title'	=> '{title}',
			'template_data'	=> <<<EOF
Hi {name},

We have removed you from the attendance list for this event.

Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}

See you next time!
EOF
		));

		return TRUE;
	}

	function tabs()
	{
		$tabs['rsvp'] = array(
			'rsvp_enabled'			=> array('visible' => 'true',
											'collapse' => 'false',
											'htmlbuttons' => 'false',
											'width' => '100%'),
			'rsvp_total_seats'		=> array('visible' => 'true',
											'collapse' => 'false',
											'htmlbuttons' => 'false',
											'width' => '100%')
		);

		return $tabs;
	}

	function update($current = '')
	{
		if (empty($current)) return FALSE;

		if ($current < '1.1.0')
		{
			// update settings array to include site id
			$this->EE->db->where('module_name', 'Rsvp');
			$row = $this->EE->db->get('modules')->row_array();

			if ( ! empty($row['settings']))
			{
				$settings = array(1 => unserialize($row['settings']));
			}

			$this->EE->db->where('module_name', 'Rsvp');
			$this->EE->db->update('modules', array('settings' => serialize($settings)));
		}

		if ($current < $this->version) return TRUE;
		else return FALSE;
	}

	function uninstall()
	{
		$this->EE->load->dbforge();
		$this->EE->load->library('layout');

		$this->EE->db->where('module_name', 'Rsvp');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Rsvp');
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table('cm_rsvp_events');
		$this->EE->dbforge->drop_table('cm_rsvp_responses');

		$this->EE->db->where('template_name', 'cm_rsvp_confirmation');
		$this->EE->db->or_where('template_name', 'cm_rsvp_cancellation');
		$this->EE->db->delete('specialty_templates');

		$this->EE->layout->delete_layout_tabs($this->tabs(), 'rsvp');

		return TRUE;
	}
}

/* End of file upd.rsvp.php */
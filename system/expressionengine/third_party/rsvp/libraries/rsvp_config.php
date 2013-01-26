<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of RSVP for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Rsvp_config {

	private $EE;
	private $_config_items = array();

	private $_class_name = 'Rsvp';
	private $_config_defaults = array(
		'rsvp_channel_id' => '',
		'rsvp_enabled_default' => array('s', array('y' => 'rsvp_enabled_y', 'n' => 'rsvp_enabled_n'), 'y'),
		'rsvp_from_email' => '',
		'rsvp_from_name' => '',
		'rsvp_email_bcc' => '',
		'rsvp_attendance_export_fields' => 'members.member_id,screen_name,email,seats_reserved,public,notes'
	);

	public function __construct()
	{
		$this->EE =& get_instance();
		$this->load();
	}

	public function item($key)
	{
		$item_default = $this->item_default($key);
		if ($item_default === FALSE) return FALSE;

		$site_id = $this->EE->config->item('site_id');

		if (isset($this->_config_items[$site_id][$key]))
		{
			return $this->_config_items[$site_id][$key];
		}
		else
		{
			return $item_default;
		}
	}

	public function item_default($key)
	{
		if ( ! isset($this->_config_defaults[$key])) return FALSE;

		$value = $this->_config_defaults[$key];
		if (is_array($value))
		{
			if ($value[0] == 't') return isset($value[1]) ? $value[1] : '';
			else return isset($value[2]) ? $value[2] : '';
		}
		else
		{
			return $value;
		}
	}

	public function item_input($key, $field_name)
	{
		if ( ! isset($this->_config_defaults[$key])) return FALSE;

		$value = $this->item($key);
		$default = $this->_config_defaults[$key];

		if (is_array($default))
		{
			// for select/radio inputs, run options through lang()
			if (is_array($default[1]))
			{
				foreach ($default[1] as $option_id => $option_value)
				{
					$default[1][$option_id] = lang($option_value);
				}
			}

			switch ($default[0])
			{
				case 't':
					return form_textarea($field_name, set_value($field_name, $value));
					break;
				case 's':
					return form_dropdown($field_name, $default[1], set_value($field_name, $value));
					break;
			}
		}
		else
		{
			return form_input($field_name, set_value($field_name, $value));
		}
	}

	public function all_items()
	{
		$items = array();
		foreach ($this->_config_defaults as $key => $value)
		{
			$items[$key] = $this->item($key);
		}

		return $items;
	}

	public function set_item($key, $value)
	{
		$site_id = $this->EE->config->item('site_id');
		$item_default = $this->item_default($key);

		if ($item_default === FALSE OR $value === FALSE OR $item_default === $value)
		{
			unset($this->_config_items[$site_id][$key]);
		}
		else
		{
			$this->_config_items[$site_id][$key] = $value;
		}
	}

	public function set_item_default($key, $default)
	{
		$this->_config_defaults[$key] = $default;
	}

	public function load()
	{
		// load any custom settings for the current site from the database
		$this->EE->db->where('module_name', $this->_class_name);
		$row = $this->EE->db->get('modules')->row_array();

		if (empty($row['settings']))
		{
			$this->_config_items = array();
		}
		else
		{
			$this->_config_items = unserialize($row['settings']);
		}

		$site_id = $this->EE->config->item('site_id');

		if (isset($this->_config_items[$site_id]) AND is_array($this->_config_items[$site_id]))
		{
			foreach ($this->_config_items[$site_id] as $key => $value)
			{
				if ( ! isset($this->_config_defaults[$key]))
				{
					unset($this->_config_items[$site_id][$key]);
				}
			}
		}
	}

	public function save()
	{
		$site_id = $this->EE->config->item('site_id');

		if (empty($this->_config_items[$site_id]))
		{
			unset($this->_config_items[$site_id]);
		}

		$this->EE->db->where('module_name', $this->_class_name);
		$this->EE->db->update('modules', array('settings' => serialize($this->_config_items)));
	}
}

/* End of file ./libraries/cm_config.php */
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of RSVP for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Rsvp_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	function count_all_rsvp_events()
	{
		$this->db->from('cm_rsvp_events');
		$this->db->join('channel_titles', 'channel_titles.entry_id = cm_rsvp_events.entry_id');
		$this->db->where('channel_id', (int)$this->rsvp_config->item('rsvp_channel_id'));
		$this->db->where('site_id', (int)$this->config->item('site_id'));
		return $this->db->count_all_results();
	}

	function get_rsvp_events($limit = 0, $offset = 0)
	{
		$sql = 'select events.*, titles.*,
				coalesce(sum(responses.seats_reserved),0) as total_seats_reserved,
				events.total_seats - coalesce(sum(responses.seats_reserved),0) as total_seats_remaining,
				count(responses.member_id) as total_members_responded
			from '.$this->db->protect_identifiers('cm_rsvp_events', TRUE).' as events
			join '.$this->db->protect_identifiers('channel_titles', TRUE).' as titles on titles.entry_id = events.entry_id
			left join '.$this->db->protect_identifiers('cm_rsvp_responses', TRUE).' as responses on responses.entry_id = events.entry_id
			where titles.channel_id = '.(int)$this->rsvp_config->item('rsvp_channel_id').'
				and titles.site_id = '.(int)$this->config->item('site_id').'
			group by events.entry_id
			order by titles.entry_date desc';

		if ($limit > 0)
		{
			$sql .= ' limit '.(int)$offset.', '.(int)$limit;
		}

		return $this->db->query($sql);
	}

	function get_rsvp_event_by_id($entry_id)
	{
		return $this->db->query('
			select events.*, titles.*, data.*,
				coalesce(sum(responses.seats_reserved),0) as total_seats_reserved,
				events.total_seats - coalesce(sum(responses.seats_reserved),0) as total_seats_remaining,
				count(responses.member_id) as total_members_responded
			from '.$this->db->protect_identifiers('cm_rsvp_events', TRUE).' as events
			join '.$this->db->protect_identifiers('channel_titles', TRUE).' as titles on titles.entry_id = events.entry_id
			join '.$this->db->protect_identifiers('channel_data', TRUE).' as data on data.entry_id = events.entry_id
			left join '.$this->db->protect_identifiers('cm_rsvp_responses', TRUE).' as responses on responses.entry_id = events.entry_id
			where events.entry_id = ?
				and titles.channel_id = '.(int)$this->rsvp_config->item('rsvp_channel_id').'
				and titles.site_id = '.(int)$this->config->item('site_id').'
			group by events.entry_id', $entry_id);
	}

	function update_rsvp_event($data)
	{
		$this->db->where('entry_id', $data['entry_id']);
		$query = $this->db->get('cm_rsvp_events');

		if ($query->num_rows() > 0)
		{
			// update existing entry
			$this->db->where('entry_id', $data['entry_id']);
			$this->db->update('cm_rsvp_events', $data);
		}
		else
		{
			// insert new entry
			$this->db->insert('cm_rsvp_events', $data);
		}
	}

	function remove_rsvp_event($entry_ids, $remove_responses = FALSE)
	{
		$this->db->where_in('entry_id', $entry_ids);
		$this->db->delete('cm_rsvp_events');

		if ($remove_responses === TRUE)
		{
			$this->db->where_in('entry_id', $entry_ids);
			$this->db->delete('cm_rsvp_responses');
		}
	}

	function get_rsvp_response($entry_id, $member_id)
	{
		$this->db->where('entry_id', $entry_id);
		$this->db->where('member_id', $member_id);
		return $this->db->get('cm_rsvp_responses');
	}

	function update_rsvp_response($data)
	{
		// entry_id and member_id are required
		if (!isset($data['entry_id']) OR !isset($data['member_id'])) return;

		// remove any existing response first
		$this->remove_rsvp_response($data['entry_id'], $data['member_id']);

		// normalise data
		if (!isset($data['seats_reserved'])) { $data['seats_reserved'] = 1; }
		if (empty($data['notes'])) { unset($data['notes']); }
		if (isset($data['public']) AND ($data['public'] === FALSE OR strtolower($data['public']) == 'n'))
		{
			$data['public'] = 'n';
		}
		else
		{
			$data['public'] = 'y';
		}

		$data['updated'] = $this->localize->now;

		// check we are not making an empty reservation
		if ($data['seats_reserved'] < 1) return;

		$this->db->insert('cm_rsvp_responses', $data);
	}

	function remove_rsvp_response($entry_id, $member_id)
	{
		$this->db->where('entry_id', $entry_id);
		$this->db->where('member_id', $member_id);
		$this->db->delete('cm_rsvp_responses');
	}

	function get_rsvp_attendance($options)
	{
		// prevent array errors
		foreach (array('select', 'entry_id', 'public', 'limit', 'offset', 'order_by', 'sort') as $field)
		{
			if ( ! isset($options[$field])) $options[$field] = FALSE;
		}

		$this->db->select(empty($options['select']) ? '*' : $options['select'])
			->where('entry_id', $options['entry_id']);

		if ($options['public']) $this->db->where('cm_rsvp_responses.public', 'y');

		$this->db->from('cm_rsvp_responses')
			->join('members', 'members.member_id = cm_rsvp_responses.member_id')
			->join('member_data', 'member_data.member_id = cm_rsvp_responses.member_id');

		// add limit and offset
		if ($options['limit'])
		{
			$options['limit'] = (int)$options['limit'];
			$options['offset'] = (int)$options['offset'];
			$this->db->limit($options['limit'], $options['offset']);
		}

		// default order_by
		if (in_array($options['order_by'], array('member_id', 'group_id', 'username', 'screen_name', 'email')))
		{
			$options['order_by'] = 'members.'.$options['order_by'];
		}
		else
		{
			// in docs this is referred to as 'date', in reality it is a catch all
			$options['order_by'] = 'cm_rsvp_responses.updated';
		}

		// default sort
		if (empty($options['sort']) AND $options['order_by'] == 'cm_rsvp_responses.updated')
		{
			$options['sort'] = 'desc';
		}
		$options['sort'] = $options['sort'] == 'desc' ? 'desc' : 'asc';

		return $this->db->order_by($options['order_by'], $options['sort'])->get();
	}

	function get_member_events($member_id)
	{
		$this->db->select('entry_id');
		$this->db->where('member_id', (int)$member_id);
		return $this->db->get('cm_rsvp_responses');
	}
}

/* End of file rsvp_model.php */
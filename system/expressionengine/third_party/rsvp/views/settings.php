<? if (empty($settings['rsvp_channel_id']['value'])): ?>
	<p style="margin-bottom: 15px"><strong class="notice"><?= lang('channel_id_not_set') ?></strong></p>
<? endif ?>

<?= form_open($post_url); ?>

<?php
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(array('data' => lang('preference'), 'style' => 'width:50%;'), lang('setting'));

	foreach ($settings as $setting_id => $setting)
	{
		$label = '<strong>'.form_label(lang($setting_id), $setting_id).'</strong>';
		if (lang($setting_id.'_subtext') != $setting_id.'_subtext')
		{
			$label .= '<div class="subtext">'.lang($setting_id.'_subtext').'</div>';
		}

		if ($setting_id == 'rsvp_channel_id' AND empty($setting['value']))
		{
			$label = str_ireplace('<strong>', '<strong class="notice">', $label);
		}

		$this->table->add_row($label, $setting['input']);
	}

	echo $this->table->generate();
?>

<?= form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>

<?= form_close(); ?>
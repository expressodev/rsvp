<? /* This template is largely based on ./themes/cp_themes/default/tools/communicate.php */ ?>

<?= form_open($current_uri) ?>

	<div id="communicate_info">
		<p>
			<?= lang('your_name', 'name') ?>
			<?= form_input(array('id'=>'name','name'=>'name','class'=>'fullfield','value'=>set_value('name', $name))) ?>
		</p>
		<p>
			<strong class="notice">*</strong> <?=lang('your_email', 'your_email')?>
			<?= form_input(array('id'=>'from','name'=>'from','class'=>'fullfield','value'=>set_value('from', $from))) ?>
			<?= form_error('from') ?>
		</p>
		<p>
			<?= lang('recipient', 'recipient') ?>&nbsp;
			<? if ($event['total_members_responded'] == 1): ?>
				<?= lang('event_member') ?>
			<? else: ?>
				<?= sprintf(lang('event_members'), $event['total_members_responded']) ?>
			<? endif ?>
			<ul>
				<? foreach ($attendance as $attendee): ?>
					<li><a href="<?= $attendee['member_link'] ?>"><?= $attendee['screen_name'] ?></a></li>
				<? endforeach ?>
			<? if ($event['total_members_responded'] > 10): ?>
				<li><a href="<?= $event_details_link ?>"><?= lang('event_view_all') ?></a></li>
			<? endif ?>
			</ul>
		</p>
		<p>
			<?= lang('cc', 'cc') ?><br />
			<?= lang('separate_emails_with_comma') ?>
			<?= form_input(array('id'=>'cc','name'=>'cc','class'=>'fullfield','value'=>set_value('cc', $cc))) ?>
			<?= form_error('cc') ?>
		</p>
		<p>
			<?= lang('bcc', 'bcc')?>
			<?= form_input(array('id'=>'bcc','name'=>'bcc','class'=>'fullfield','value'=>set_value('bcc', $bcc))) ?>
			<?= form_error('bcc') ?>
		</p>
	</div>

	<div id="communicate_compose">
		<p>
			<strong class="notice">*</strong> <?=lang('subject', 'subject') ?>
			<?= form_input(array('id'=>'subject','name'=>'subject','class'=>'fullfield','value'=>set_value('subject', $subject))) ?>
			<?= form_error('subject') ?>
		</p>
		<p style="margin-bottom:15px">
			<strong class="notice">*</strong> <?= lang('message', 'message') ?><br />
			<?= form_error('message') ?>
			<?= form_textarea(array('id'=>'message','name'=>'message','rows'=>20,'cols'=>85,'class'=>'fullfield','value'=>set_value('message', $message))) ?>
		</p>
<?php
		$this->table->set_template($cp_pad_table_template);

		$this->table->add_row(array(
				array('data' => lang('mail_format', 'mailtype'), 'style' => 'width:30%;'),
				form_dropdown('mailtype', $mailtype_options, $mailtype, 'id="mailtype"')
			)
		);

		$this->table->add_row(array(
				lang('wordwrap', 'wordwrap'),
				form_dropdown('wordwrap', $word_wrap_options, $wordwrap, 'id="wordwrap"')
			)
		);

		echo $this->table->generate();
?>
		<p style="margin-top:15px;">
<?php
			echo form_checkbox(array(
				'name'        => 'accept_admin_email',
				'id'          => 'accept_admin_email',
				'value'       =>  'y',
				'checked'		=> set_checkbox('accept_admin_email', 'y')
			));
?>
			<?= lang('honor_email_pref', 'accept_admin_email') ?>
		</p>
		<p><strong class="notice">*</strong> <?= lang('required_fields') ?></p>
		<p><?= form_submit(array('name' => 'submit', 'value' => lang('send'), 'class' => 'submit')) ?></p>
	</div>

<?= form_close() ?>
<div class="clear_right"></div>
<li>
	<span class="text-muted"><i class="fa-fw fa fa-chevron-right small"></i></span>
	<?php echo $this->Html->link(Configure::read('project.alt_name_for_lend_singular_caps') . ' (' . $lend_late_payment_count. ')', array('controller'=>'lends','action'=>'index','filter_id' => ConstMoreAction::PendingPayment), array('class' => 'h5 rgt-move'));?> 
</li>
<div class="container gray-bg marg-top-30">
	<?php echo $this->Form->create('ProjectRepayment', array('action' => 'add/' . $project['Project']['id'] , 'class' => 'form-horizontal normal clearfix')); ?>
			<div class="media">
				<div class="payment-img pull-left float-none"> 
					<?php echo $this->Html->link($this->Html->showImage('Project', $project['Attachment'], array('dimension' => 'medium_thumb', 'alt' => sprintf(__l('[Image: %s]'), $this->Html->cText($project['Project']['name'], false)), 'title' => $this->Html->cText($project['Project']['name'], false), 'class' => 'js-tooltip'),array('aspect_ratio'=>1)), array('controller' => 'projects', 'action' => 'view',  $project['Project']['slug'], 'admin' => false), array('escape' => false)); ?> 
				</div>
				<div class="media-body">
					<h3 class="list-group-item-heading"><?php echo $this->Html->link($this->Html->filterSuspiciousWords($this->Html->cText($project['Project']['name'], false), $project['Project']['detected_suspicious_words']), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug']), array('escape' => false));?></h3>
					<p> 
						<?php echo __l('A') . ' '; ?>
						<?php echo sprintf(__l('%s in '), Configure::read('project.alt_name_for_project_singular_small')) . ' '; ?>
						<?php
							if (!empty($project['City']['name'])) {
								echo $this->Html->cText($project['City']['name'], false) . ', ';
							}
							if (!empty($project['Country']['name'])) {
								echo $this->Html->cText($project['Country']['name'], false);
							}
						?>
						<?php echo __l(' by '); ?><?php echo $this->Html->link($this->Html->cText($project['User']['username']), array('controller' => 'users', 'action' => 'view', $project['User']['username']), array('escape' => false));?>
					</p>
				</div>
			</div>
		<div class="panel panel-info well-sm navbar-btn">
			<?php
				$next_repayment_amount = $project['Lend']['next_repayment_amount'];
				echo $this->Form->input('project_id',array('type'=>'hidden', 'value' => $project['Project']['id']));
			?>
			<dl class="row marg-top-20">
				<dt class="col-sm-3"><?php echo __l('Principal Amount') . ' ('. Configure::read('site.currency') .')'; ?></dt>
				<dd class="col-sm-3"><?php echo $this_month_principal; ?></dd>
			</dl>
			<dl class="row">
				<dt class="col-sm-3"><?php echo __l('Interest Amount') . ' ('. Configure::read('site.currency') .')'; ?></dt>
				<dd class="col-sm-3"><?php echo $this_month_interest; ?></dd>
			</dl>
			<?php if($project['Lend']['next_repayment_date'] < date('Y-m-d') && isset($project['ProjectRepayment']['late_fee'])) {
				$next_repayment_amount = $project['Lend']['next_repayment_amount'] + $project['ProjectRepayment']['late_fee'];
			?>
			<dl class="row">
				<dt class="col-sm-3"><?php echo __l('Late Fee') . ' ('. Configure::read('site.currency') .')'; ?></dt>
				<dd class="col-sm-3"><?php echo $this->Html->cCurrency($project['ProjectRepayment']['late_fee']); ?></dd>
			</dl>
			<?php } ?>
			<dl class="row">
				<dt class="col-sm-3"><?php echo __l('Total Amount') . ' ('. Configure::read('site.currency') .')'; ?></dt>
				<dd class="col-sm-3"><?php echo $this->Html->cCurrency($next_repayment_amount); ?></dd>
			</dl>
		</div>
		<div>
			<fieldset class="grid_left">
				<legend><?php echo __l('Select Payment Type'); ?></legend>
				<?php echo $this->element('payment-get_gateways', array('model'=>'ProjectRepayment', 'type'=>'is_enable_for_lend', 'is_enable_wallet'=>1, 'project_type'=> 'ProjectRepayment', 'cache' => array('config' => 'sec')));?>
			</fieldset>
		</div>
	<?php echo $this->Form->end(); ?>
</div>
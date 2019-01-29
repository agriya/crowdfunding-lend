<?php /* SVN: $Id: $ */ ?>
<div class="repaymentSchedules admin-form">
<?php echo $this->Form->create('RepaymentSchedule', array('class' => 'form-horizontal'));?>
  <fieldset>
		<ul class="breadcrumb">
			<li><?php echo $this->Html->link(__l('Repayment Schedules'), array('action' => 'index'),array('title' => __l('Repayment Schedules')));?><span class="divider">&raquo</span></li>
			<li class="active"><?php echo sprintf(__l('Add %s'), __l('Repayment Schedule'));?></li>
		</ul>
		<ul class="nav nav-tabs">
			<li>
			<?php echo $this->Html->link('<i class="fa fa-th-list fa-fw"></i>'.__l('List'), array('action' => 'index'),array('title' =>  __l('List'),'data-target'=>'#list_form', 'escape' => false));?>
			</li>
			<li class="active"><a href="#add_form"><i class="fa fa-plus-circle fa-fw"></i><?php echo __l('Add'); ?></a></li>
		</ul>
		<div class="gray-bg admin-checkbox">
			<?php
				echo $this->Form->input('name',array('label'=>__l('Name')));
				echo $this->Form->input('day',array('label'=>__l('Day')));
				echo $this->Form->input('is_particular_day_of_month', array('label' =>__l('Particular Day of Month?'),'type'=>'checkbox'));
				echo $this->Form->input('is_approved', array('label' =>__l('Active?'),'type'=>'checkbox'));
			?>
			<div class="form-actions">
				<?php echo $this->Form->submit(__l('Add'),array('class'=>'btn btn-info'));?>
			</div>
		</div>
	</fieldset>    
<?php echo $this->Form->end();?>
</div>
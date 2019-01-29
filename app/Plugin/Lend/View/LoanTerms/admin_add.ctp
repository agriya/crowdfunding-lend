<?php /* SVN: $Id: $ */ ?>
<div class="loanTerms admin-form">
<?php echo $this->Form->create('LoanTerm', array('class' => 'form-horizontal'));?>
	<fieldset>
		<ul class="breadcrumb">
			<li><?php echo $this->Html->link(__l('Loan Terms'), array('action' => 'index'),array('title' => __l('Loan Terms')));?><span class="divider">&raquo</span></li>
			<li class="active"><?php echo sprintf(__l('Add %s'), __l('Loan Term'));?></li>
		</ul>
		<ul class="nav nav-tabs">
			<li>
			<?php echo $this->Html->link('<i class="fa fa-th-list fa-fw"></i>'.__l('List'), array('action' => 'index'),array('title' =>  __l('List'),'data-target'=>'#list_form', 'escape' => false));?>
			</li>
			<li class="active"><a href="#add_form"><i class="fa fa-plus-circle fa-fw"></i><?php echo __l('Add'); ?></a></li>
		</ul>
		<div class="admin-checkbox gray-bg">
			<?php
				echo $this->Form->input('name',array('label'=>__l('Name')));
				echo $this->Form->input('months', array('label' => __l('Number of Months')));
				echo $this->Form->input('is_approved', array('label' =>__l('Active?'),'type'=>'checkbox'));
			?>		
			<div class="form-actions">
				<?php echo $this->Form->submit(__l('Add'),array('class'=>'btn btn-info'));?>
			</div>
		</div>
	</fieldset>
	<?php echo $this->Form->end();?>
</div>
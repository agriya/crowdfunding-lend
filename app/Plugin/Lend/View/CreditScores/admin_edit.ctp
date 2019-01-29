<?php /* SVN: $Id: $ */ ?>
<div class="creditScores admin-form">
<?php echo $this->Form->create('CreditScore', array('class' => 'form-horizontal'));?>
	<fieldset>
		<ul class="breadcrumb">
			<li><?php echo $this->Html->link(__l('Credit Scores'), array('action' => 'index'),array('class'=>'text-danger','title' => __l('Credit Scores')));?><span class="divider">&raquo</span></li>
			<li class="active"><?php echo sprintf(__l('Edit %s'), __l('Credit Score'));?></li>
		</ul>
		<ul class="nav nav-tabs">
			<li>
				<?php echo $this->Html->link('<i class="fa fa-th-list fa-fw"></i>'.__l('List'), array('action' => 'index'),array('title' =>  __l('List'),'data-target'=>'#list_form', 'escape' => false));?>
			</li>
			<li class="active"><a href="#add_form"><i class="fa fa-pencil-square-o fa-fw"></i><?php echo __l('Edit'); ?></a></li>
		</ul>
		<div class="gray-bg admin-checkbox clearfix">
			<div class="col-md-8 col-sm-7 navbar-btn">
				<?php
					echo $this->Form->input('id');
					echo $this->Form->input('name',array('label'=>__l('Name')));
					echo $this->Form->input('interest_rate',array('label'=>__l('Interest Rate')));
					echo $this->Form->input('suggested_interest_rate',array('label'=>__l('Suggested Interest Rate')),array('info' =>__l('Please refer the lastest target interest rate summary before enter your site suggested interest rate')));
					echo $this->Form->input('is_approved', array('label' =>__l('Active?'),'type'=>'checkbox'));
				?>
				<div class="form-actions">
					<?php echo $this->Form->submit(__l('Update'),array('class'=>'btn btn-info'));?>
				</div>
			</div>
			<div class="navbar-right col-sm-4 navbar-btn"><div class="mob-top-space"><?php echo $this->element('Lend.credit_scores_summary');?></div></div>			
		</div>		
	</fieldset>
	
<?php echo $this->Form->end();?>
</div>
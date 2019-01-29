<div class="add-project">
<div class="bg-success clearfix text-center start-project-baner">
	<h2 class="list-group-item-heading"><span class="or-hor text-b"><?php echo __l('Start Project');?></span></h2>
	<p><?php echo sprintf(__l('Discover new %s campaigns or start your own campaign to raise funds.'), Configure::read('site.name')); ?></p>
</div> 
<ul class="nav nav-tabs project-tab text-center">
	<?php 
	if(isPluginEnabled('Pledge')) {
	?>
		<li>
			<?php echo $this->Html->link($this->Html->image('start-pledge.png') . '<strong>'. __l(Configure::read('project.alt_name_for_pledge_singular_small')) .'</strong>', array('controller' =>'projects', 'action' => 'add', 'project_type'=>'pledge'), array('class' => 'pledge-heading text-uppercase', 'escape' => false)); ?>
		</li>
	<?php
	}
	?>
	<?php 
	if(isPluginEnabled('Donate')) {
	?>
		<li>
			<?php echo $this->Html->link($this->Html->image('start-donate.png') . '<strong>'. __l(Configure::read('project.alt_name_for_donate_singular_small')) .'</strong>', array('controller' =>'projects', 'action' => 'add', 'project_type'=>'donate'), array('class' => 'donate-heading text-uppercase', 'escape' => false)); ?>
		</li>
	<?php
	}
	?>
	<?php 
	if(isPluginEnabled('Equity')) {
	?>
		<li>
			<?php echo $this->Html->link($this->Html->image('start-equity.png') . '<strong>'. __l(Configure::read('project.alt_name_for_equity_singular_small')) .'</strong>', array('controller' =>'projects', 'action' => 'add', 'project_type'=>'equity'), array('class' => 'equity-heading text-uppercase', 'escape' => false)); ?>
		</li>
	<?php
	}
	?>
	<?php 
	if(isPluginEnabled('Lend')) {
	?>
		<li class="active">
			<?php echo $this->Html->link($this->Html->image('start-lend.png') . '<strong>'. __l(Configure::read('project.alt_name_for_lend_singular_small')) .'</strong>', array('controller' =>'projects', 'action' => 'add', 'project_type'=>'lend'), array('class' => 'lend-heading text-uppercase', 'escape' => false)); ?>
		</li>
	<?php
	}
	?>
</ul>
	<div class="no-border" >
		<h4 class="text-center h2 marg-top-30 roboto-bold"><?php echo __l('Check Rate'); ?></h4>
		<p class="text-center marg-btom-30 roboto-light h3"><?php echo __l('Before starting project, check your eligble interest rate based on your credit score and category.'); ?></p>
	</div>
<div class="container">
<div class="projects js-responses lend admin-form start-equity" id="ProjectAddForm">
    <?php
      echo $this->Form->create('Project', array('url' => array('controller' => 'lends', 'action'=> 'check_rate'), 'class' => 'form-horizontal js-project-form clearfix','enctype' => 'multipart/form-data'));
    ?>	
	<div class="thumbnail well-sm lend-form">
		<div>
		<?php 
			if(empty($rate_calculation_result))
			{
			echo $this->Form->input('needed_amount', array('type' => 'text','placeholder'=>'Enter the amount','label' => __l('Needed Amount').' ('. Configure::read('site.currency').')'));
			echo $this->Form->input('Lend.lend_project_category_id',array('options' => $lendCategories, 'label' => __l('Purpose'), 'empty' => __l('Please Select')));
			echo $this->Form->input('Lend.credit_score_id',array('empty' => __l('Please Select')));
			}
			if(!empty($rate_calculation_result))
			{
		?>
		</div>
		<dl class="row">
			<dt class="col-md-2">					
				<?php echo __l('Needed Amount'); ?>
			</dt>
			<dd class="col-md-2">
				<?php echo $this->request->data['Project']['needed_amount']; ?>				
			</dd>
		</dl>
		<dl class="row">
			<dt class="col-md-2">					
				<?php echo __l('Terms'); ?>
			</dt>
			<dd class="col-md-2">
				<?php echo Configure::read('lend.default_terms') . __l('months'); ?>				
			</dd>
		</dl>
		<dl class="row">
			<dt class="col-md-2">					
				<?php echo __l('Interest Rate'); ?>
			</dt>
			<dd>
				<?php echo $rate; ?>			
			</dd>
		</dl>
		<dl class="row">
			<dt class="col-md-2">					
				<?php echo __l('Monthly Repayment'); ?>
			</dt>
			<dd class="col-md-2">
				<?php echo $this->Html->cInt($rate_calculation_result['per_month'], false); ?>				
			</dd>
		</dl>
		<?php
				echo $this->Form->input('needed_amount', array('type' => 'hidden','label' => __l('Needed Amount').' ('. Configure::read('site.currency').')'));
				echo $this->Form->input('Lend.lend_project_category_id',array('type' => 'hidden', 'value' => $this->request->data['Lend']['lend_project_category_id']));
				echo $this->Form->input('Lend.credit_score_id',array('type' => 'hidden', 'value' => $this->request->data['Lend']['credit_score_id']));
				echo $this->Form->input('Lend.per_month',array('type' => 'hidden', 'value' => $rate_calculation_result['per_month']));
				echo $this->Form->input('Lend.total_amount_to_pay',array('type' => 'hidden', 'value' => $rate_calculation_result['total_amount']));
				echo $this->Form->input('Lend.total_interest_to_pay',array('type' => 'hidden', 'value' => $rate_calculation_result['total_interest']));
				echo $this->Form->input('Lend.target_interest_rate',array('type' => 'hidden', 'value' => $rate, 'class'=>'js-remove-error'));
			}
		?>
	</div>	
              
    <div class="clearfix">
      <div class="form-actions input">
        <div class="submit">
			<?php 
				if(!empty($rate_calculation_result))
				{
					echo $this->Form->submit(__l('Get Loan'), array('class' => 'btn btn-info', 'name' => 'data[Project][CheckRate]', 'div' => false));
				} else{
					echo $this->Form->submit(__l('Check Rate'), array('class' => 'btn btn-warning', 'name' => 'data[Project][CheckRate]', 'div' => false));
				}
				?>
      </div>
    </div>
    <?php echo $this->Form->end();?>
  </div>
</div>
</div>
</div>
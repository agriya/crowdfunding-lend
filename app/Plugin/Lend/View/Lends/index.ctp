<div class="lends index js-response lend">
  <div class="page-header">
    <h2><?php echo $this->Html->cText($heading, false) ?></h2>
  </div>
  <?php 
  echo $this->Form->create('Lend', array('action' => 'index', 'class' => 'form-horizontal'));  
  ?>
  <section class="clearfix img-thumbnail">
  <div class="row">
    <label for="LendProjectCategoryId"><?php echo __l('Lend Amount'); ?> <?php echo '('.Configure::read('site.currency').')';?></label>
	<div class="col-md-10">
  <?php
  echo $this->Form->input('lend_amount', array('label' => false, 'class' => 'col-md-2'));
  ?>
	</div>
  </div>  
  <div class="row">
    <label for="LendProjectCategoryId"><?php echo __l('Purpose'); ?></label>
	<div class="col-md-10">
  <?php
  echo $this->Form->input('lend_project_category_id', array('label' => false, 'multiple' => 'checkbox', 'class' => 'checkbox col-md-3'));
  ?>
	</div>
  </div>
  <div class="row">
    <label for="CreditScoreId"><?php echo __l('Credit Scores'); ?></label>
	<div class="col-md-10">
  <?php
  echo $this->Form->input('credit_score_id', array('label' => false, 'multiple' => 'checkbox', 'class' => 'checkbox col-md-1'));
  ?>
	</div>
  </div>
  <div class="row">
    <label for="LoanTermId"><?php echo __l('Loan Term'); ?></label>
	<div class="col-md-10">
  <?php
  echo $this->Form->input('loan_term_id', array('label' => false, 'multiple' => 'checkbox', 'class' => 'checkbox col-md-2'));
  ?>
    </div>
  </div>
  <div class="submit lend">
  <?php
  echo $this->Form->submit(__l('Search'), array('name' => 'data[Lend][search]', 'class' => "btn-primary", 'div' => false));
  ?>
  </div>
  </section>
  <?php if(isset($lends)) { ?>
  <h3><?php echo __l('Open for Lending Projects'); ?></h3>
  <section class="well">
	<div>
	<table class="table table-striped table-bordered table-condensed table-hover">
      <thead>
        <tr>
          <th class="select text-center"><?php echo __l('Select'); ?></th>
          <th class="text-center"><?php echo __l('Lend Amount');?></th>
          <th class="text-center"><?php echo __l('Credit Score') . ' / ' . __l('Rate');?></th>
          <th class="text-center"><?php echo __l('Offered') . ' / ' . __l('Difference in Rate');?></th>
          <th class="text-center"><?php echo __l('Term');?></th>
          <th class="text-center"><?php echo __l('Amount');?></th>
          <th class="text-center"><?php echo __l('Title') . ' / ' . __l('Purpose');?></th>
          <th class="text-center"><?php echo __l('Collected') . ' / ' . __l('Needed'); ?></th>
          <th class="text-center"><?php echo __l('Days Left');?></th>
        </tr>
	  </thead>
      <tbody>
	  <?php
		if (!empty($lends)):
		  foreach ($lends as $lend):
	  ?>
			<tr>
              <td class="select text-center">
                <?php
				echo $this->Form->input('Project.'.$lend['Project']['id'].'.id',array('type' => 'checkbox','div' => false, 'id' => "checkbox_".$lend['Project']['id'],'label' => false , 'class' => 'js-checkbox-list'));?>
		      </td>
			  <td class="text-center">
                <?php echo $this->Form->input('Project.'.$lend['Project']['id'].'.amount',array('id' => "amount_".$lend['Project']['id'], 'label' => false, 'class' => 'col-md-2')); ?>
		      </td>
			  <td class="text-center"><?php echo $this->Html->cText($lend['CreditScore']['name']) . ' / ' . $this->Html->cFloat($lend['CreditScore']['interest_rate']); ?></td>
			  <td class="text-center"><?php echo $this->Html->cFloat($lend['Lend']['target_interest_rate']) . ' / ' . $this->Html->cFloat(($lend['CreditScore']['suggested_interest_rate'] - $lend['Lend']['target_interest_rate'])); ?></td>
			  <td class="text-center"><?php echo $this->Html->cInt($lend['LoanTerm']['months']); ?></td>
			  <td class="text-center"><?php echo $this->Html->cCurrency($lend['Project']['needed_amount']); ?></td>
			  <td class="text-center"><?php echo $this->Html->link($this->Html->cText($lend['Project']['name']),array('controller' => 'projects', 'action' => 'view',  $lend['Project']['slug'], 'admin' => false), array('escape' => false)) . ' / ' . $this->Html->cText($lend['LendProjectCategory']['name']); ?></td>
			  <td class="text-center lend">
				<?php $collected_percentage = ($lend['Project']['collected_percentage']) ? $lend['Project']['collected_percentage'] : 0; ?>
				<div class="progress">
				  <div style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" class="progress-bar"></div>
				</div>
				<p class="text-center"><?php echo $this->Html->cCurrency($lend['Project']['collected_amount']); ?> / <?php echo $this->Html->cCurrency($lend['Project']['needed_amount']); ?></p>
			  <td class="text-center">
				<?php 
					if(!empty($lend['Project']['project_end_date'])):
						$time_strap= strtotime($lend['Project']['project_end_date']) -strtotime( date('Y-m-d'));
						$days = floor($time_strap /(60*60*24));
						if ($days > 0) {
						  $lend[0]['enddate'] = $days;
						} else {
						  $lend[0]['enddate'] =0;
						}
					  endif;
					$end_time = intval(strtotime($lend['Project']['project_end_date']) - time());
					if(!empty($lend[0]['enddate']) && round($lend[0]['enddate']) > 0){
						echo $this->Html->cInt($lend[0]['enddate']) . " ";
					}else{ 
				?>
						<span class="js-countdown"> <span title="<?php echo $end_time;?> " class="js-time hide"><?php echo $end_time;?> </span></span>
				<?php  
					}
					echo (round($lend[0]['enddate']) >0) ? __l('days to go') : __l('hours to go');
				?>
			  </td>
			</tr>
	  <?php
		  endforeach;
		else:
	  ?>
		 <tr>
			<td colspan="9" class="text-center"><i class="fa fa-exclamation-triangle fa-fw"></i> <?php echo sprintf(__l('No %s available'), __l('projects'));?></td>
		</tr>
	  <?php
		endif;
	  ?>
      </tbody>
	</table>
	</div>
	<?php if (!empty($lends)): ?>
	 <h3>Lend Terms</h3>
	<?php echo $this->requestAction(array('controller' => 'nodes', 'action' => 'view', 'type' => 'page', 'slug' => 'lend-terms'), array('return'));  ?>
	<div class="lendform">
	 <?php echo $this->Form->input('Lend.is_agree_terms_conditions',array('class'=>'js-term js-no-pjax', 'div' => false, 'type'=>'checkbox','label'=>__l('I have read and understand ').$this->Html->cText(Configure::read('site.name'), false).'\'s '. strtolower(Configure::read('project.alt_name_for_lend_singular_caps')) .__l(' terms.'))); ?>
	</div>
	<div class="clearfix">
      <div class="col-md-6">
        <fieldset>
       	  <legend><?php echo __l('Lending Name'); ?></legend>
          <div class="group-block personal-radio">
            <div class="input text">
		  	  <?php echo $this->Form->input('Lend.lend_name', array('label' =>'Lending Name'));?>
			</div>
		  </div>
        </fieldset>
		<fieldset>
			<legend><?php echo sprintf(__l('Personalize your  %s'),Configure::read('project.alt_name_for_lend_singular_caps')); ?></legend>
			<div class="group-block personal-radio"> <?php echo $this->Form->input('is_anonymous',array('type' =>'radio','options'=>$radio_options,'default'=>ConstAnonymous::None,'legend'=>false));?> </div>
		</fieldset>
      </div>
      <div class="offset1 col-md-4">
        <fieldset class="grid_left">
          <legend><?php echo __l('Select Payment Type'); ?></legend>
          <div class="group-block">
			<?php
			echo $this->element('payment-get_gateways', array('model' => 'Lend', 'type'=>'is_enable_for_lend', 'is_enable_wallet'=>1, 'project_type' => 'Lend', 'cache' => array('config' => 'sec')));?>
		  </div>
		</fieldset>
	  </div>
	</div>
	<?php endif; ?>
  </section>
  <?php } ?>
  <?php echo $this->Form->end(); ?>
</div>
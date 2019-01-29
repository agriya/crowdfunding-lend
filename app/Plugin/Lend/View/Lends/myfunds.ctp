<?php /* SVN: $Id: index.ctp 2879 2010-08-27 11:08:48Z sakthivel_135at10 $ */ ?>
<?php if (!$this->request->params['isAjax']) { ?>
<div class="js-response space hor-mspace lend">
  <?php } ?>
  <div class="clearfix space" id="js-lend-scroll" itemtype="http://schema.org/Product" itemscope>
  <div class="lend-status" itemprop="Name">
    <span class="ver-space"><?php echo $this->Html->image('lend-hand.png', array('width' => 50, 'height' => 50)); ?></span><span class="h3"><?php echo __l(Configure::read('project.alt_name_for_lend_singular_caps')); ?></span>
  </div>
  <!--<h3 class="lendc text-warning navbar-btn"><?php echo sprintf(__l('My').' %s', Configure::read('project.alt_name_for_lend_plural_caps')); ?></h3>-->
</div>
 <div class="clearfix">

  <?php
      $link2 = __l('Refunded');
      $link3 = __l('Lended');
	  $link4 = __l('Canceled');
  ?>
  <ul class="filter-list-block list-inline">
    <li> <?php echo $this->Html->link('<span class="badge badge-danger"><span><strong>'.$this->Html->cInt($fund_count,false).'</strong></span></span><span class="show">' .__l('All'). '</span>', array('controller'=>'lends','action'=>'myfunds', 'status' => 'all'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
    <li> <?php echo $this->Html->link('<span class="badge badge-info"><span><strong>'.$this->Html->cInt($refunded_count,false).'</strong></span></span><span class="show">' .$link2. '</span>', array('controller'=>'lends','action'=>'myfunds','status'=>'refunded'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
    <li> <?php echo $this->Html->link('<span class="badge badge-success"><span><strong>'.$this->Html->cInt($paid_count,false).'</strong></span></span><span class="show">' .$link3. '</span>', array('controller'=>'lends','action'=>'myfunds','status'=>'paid'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
	 <li> <?php echo $this->Html->link('<span class="badge badge-warning"><span><strong>'.$this->Html->cInt($cancelled_count,false).'</strong></span></span><span class="show">' .$link4. '</span>', array('controller'=>'lends','action'=>'myfunds','status'=>'cancelled'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
  </ul>
  </div>
  <?php echo $this->element('paging_counter');?>
  <div class="table-responsive">
  <table class="table table-striped table-bordered table-condensed table-hover panel" id="js-invest-scroll">
  <tr>
    <th class="text-center"><?php echo __l('Actions');?></th>
    <th class="js-filter text-left"><?php echo $this->Paginator->sort('project_id', __l(Configure::read('project.alt_name_for_project_singular_caps')), array('url' => array('controller' => 'lends', 'action' => 'myfunds', 'type' => 'mydonations'), 'class' => 'js-no-pjax'));?></th>
	<th class="text-center"><?php echo __l(Configure::read('project.alt_name_for_lend_project_owner_singular_caps'));?></th>
	<th class="text-center"><?php echo __l('Credit Score');?></th>
	<th class="text-center"><?php echo __l('Loan Term');?></th>
	<th class="text-center"><?php echo __l('Rate');?></th>
	<th class="js-filter text-left"><?php echo $this->Paginator->sort('amount', sprintf(__l('Amount %s '),Configure::read('project.alt_name_for_lend_past_tense_caps')) . ' (' . Configure::read('site.currency') . ')', array('url' => array('controller' => 'lends', 'action' => 'myfunds', 'type' => 'mydonations'), 'class' => 'js-no-pjax'));?></th>
	
    <th class="text-center"><?php echo __l('Capital Returned'). ' (' . Configure::read('site.currency') . ')';?></th>
	<th class="text-center"><?php echo __l('Interest Returned'). ' (' . Configure::read('site.currency') . ')';?></th>
	<th class="text-center"><?php echo __l('Percentage Repaid');?></th>
	<th class="text-center"><?php echo __l('Repayment Date');?></th>
	<th class="text-center"><?php echo __l('Monthly Repayment'). ' (' . Configure::read('site.currency') . ')';?></th>
	<th class="text-center"><?php echo __l('Total Arrears');?></th>
    <th class="text-center"><?php echo __l('Status');?></th>
  
  </tr>
  <?php
    if (!empty($projectFunds)):
      $i = 0;
      foreach ($projectFunds as $projectFund):
        $class = null;
        if ($i++ % 2 == 0) {
          $class = ' class="altrow"';
        }
    ?>
  <tr <?php echo $class;?>>
    <td class="col-md-1 text-center"><div class="dropdown"> <a href="#" title="Actions" data-toggle="dropdown" class="fa fa-cog fa-fw dropdown-toggle js-no-pjax"><span class="hide">Action</span></a>
      <ul class="list-unstyled dropdown-menu text-left clearfix">
      <?php
          if ($projectFund['ProjectFund']['user_id'] == $this->Auth->user('id') && (Configure::read('Project.is_allow_fund_cancel_by_funder')) && (strtotime('+'.Configure::read('Project.minimum_days_before_fund_cancel').' days') < strtotime($projectFund['Project']['project_end_date'].'23:59:59'))  && $projectFund['Project']['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending): ?>
     <?php  if ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Authorized)  :
              $link = sprintf(__l('Cancel %s'), Configure::read('project.alt_name_for_lend_singular_caps'));
              $type = 'cancel'; ?>
      <li> <?php echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.$link, array('controller'=> 'project_funds', 'action' => 'edit_fund', 'project_fund' => $projectFund['ProjectFund']['id'], 'type' => $type, 'return_page' => 'mydonations'), array('escape' => false,'class' => 'cancel js-confirm','title'=>$link,'escape'=>false)); ?> </li>
      <?php  endif;
          endif; ?>
      <li> <?php echo $this->Html->link('<i class="fa fa-user fa-fw"></i>'.sprintf(__l('Contact %s'), Configure::read('project.alt_name_for_lend_project_owner_singular_small')), array('controller' => 'projects', 'action' => 'view', $projectFund['Project']['slug'] . '#comments'), array('class' => 'js-no-pjax cboxelement msg', 'escape' => false,'title' => sprintf(__l('Contact %s'), Configure::read('project.alt_name_for_borrow_noun_singular_small')))); ?> </li>
      </ul>
    </div></td>
    <td class="text-left"><?php
          if($is_wallet_enabled) {
            $project_status = $projectFund['Project']['Lend']['LendProjectStatus']['name'];
          } else {
            $project_status = str_replace("Refunded","Voided",$projectFund['Project']['Lend']['LendProjectStatus']['name']);
          }
        ?>
    <i title="<?php echo $this->Html->cText($project_status, false);?>" class="fa fa-square fa-fw project-status-<?php echo $this->Html->cInt($projectFund['Project']['Lend']['lend_project_status_id'], false);?>"></i> <?php echo $this->Html->link($this->Html->cText($projectFund['Project']['name']), array('controller'=> 'projects','action' => 'view', $projectFund['Project']['slug']), array('class' => 'cboxelement', 'escape' => false,'title'=> $this->Html->cText($projectFund['Project']['name'],false)));?> </td>
	<td class="text-center"><?php echo $this->Html->cText($projectFund['Project']['User']['username'], false);?></td>
	<td class="text-center"><?php echo $this->Html->cText($projectFund['Project']['Lend']['CreditScore']['name'], false);?></td>
	<td class="text-center"><?php echo $this->Html->cText($projectFund['Project']['Lend']['LoanTerm']['name'], false);?></td>
    <td class="text-right"><?php echo $this->Html->cFloat($projectFund['Project']['Lend']['target_interest_rate'], false).'%';?></td>
	<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount']);?></td>
	<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['Project']['Lend']['repayment_amount']);?></td>
	<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['Project']['Lend']['repayment_interest_amount']);?></td>
	<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['Project']['Lend']['repayment_percentage']).'%';?></td>
    <td class="text-center"><?php echo (($projectFund['Project']['Lend']['next_repayment_date'] !=  '0000-00-00'))? $this->Html->cDateTimeHighlight($projectFund['Project']['Lend']['next_repayment_date']) : "-";?></td>
    <td class="text-right"><?php echo $this->Html->cCurrency($projectFund['Project']['Lend']['next_repayment_amount']);?></td>
	 <td class="text-right"><?php echo $this->Html->cInt($projectFund['Project']['Lend']['total_arrear_count']);?></td>
	 <td class="text-right">
		 <?php $status = ""; 
			 if($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Authorized){
				$status = "Authorized";
			 }else if(($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaidToOwner) && ($projectFund['ProjectFund']['is_collection'] == 1)){
				$status = "Collection";
			 }else if($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaidToOwner){
				$status = "Withdrawn";
			 }else if($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Closed){
				$status = "Closed";
			 }else if($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::DefaultFund){
				$status = "Default";
			 }else if($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Expired){
				$status = "Expired";
			 }else if($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Canceled){
				$status = "Canceled";
			 }
			 
			 ?>
		 <?php echo $this->Html->cText($status);?>
	 </td>
  </tr>
  <?php
      endforeach;
    else:
  ?>
  <tr>
    <td colspan="14">
	  <div class="text-center no-items">
		<p><?php echo sprintf(__l('No %s available'), Configure::read('project.alt_name_for_lend_plural_caps'));?></p>
	  </div>
	</td>
  </tr>
  <?php
    endif;
  ?>
  </table>
  </div>
  <?php if (!empty($projectFunds)) { ?>
  <div class="clearfix">
  <div class=" pull-right paging js-pagination js-no-pjax {'scroll':'js-lend-scroll'}"> <?php echo $this->element('paging_links'); ?> </div>
  </div>
  <?php } ?>
  <?php if (!$this->request->params['isAjax']) { ?>
</div>
<?php } ?>
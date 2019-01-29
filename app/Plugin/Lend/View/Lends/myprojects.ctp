<?php /* SVN: $Id: index.ctp 2901 2010-09-02 11:49:34Z sakthivel_135at10 $ */ ?>
<?php if (!$this->request->params['isAjax']) { ?>
  <div class="js-response lend space hor-mspace">
<?php } ?>
<div class="clearfix space" id="js-lend-scroll" itemtype="http://schema.org/Product" itemscope>
  <div class=" lend-status text-b" itemprop="Name"> <span class="ver-space"><?php echo $this->Html->image('lend-hand.png', array('width' => 50, 'height' => 50)); ?></span><span class="no-mar h3"><?php echo Configure::read('project.alt_name_for_lend_singular_caps'); ?></span> </div>  
</div>
  <div class="clearfix hor-space">
    <ul class="filter-list-block list-inline">
		  <li class="text-center"><?php echo $this->Html->link('<span class="badge badge-info"><span><strong>'.$this->Html->cInt($count,false).'</strong></span></span><span class="show">' .__l('All'). '</span>', array('controller'=>'lends','action'=>'myprojects', 'status' => 'all'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
		  <?php if(Configure::read('Project.is_project_owner_select_funding_method')): ?>
		  <li><?php echo $this->Html->link('<span class="badge badge-success"><span><strong>'.$this->Html->cInt($total_flexible_projects,false).'</strong></span></span><span class="show">' .__l('Flexible'). '</span>', array('controller'=>'lends','action'=>'myprojects', 'status' => 'flexible'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
		  <li><?php echo $this->Html->link('<span class="badge badge-blue"><span><strong>'.$this->Html->cInt($total_fixed_projects,false).'</strong></span></span><span class="show">' .__l('Fixed'). '</span>', array('controller'=>'lends','action'=>'myprojects', 'status' => 'fixed'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
		  <?php endif; ?>
		  <?php	
		  $approvedCountInfo = count($formFieldSteps) > 1 ?' / '.__l('Admin Approved').' ('.$this->Html->cInt($approvedCount,false).')':'';
		  $countInfo = !empty($formFieldSteps)?'<i class="fa fa-info-circle fa-fw sfont js-tooltip" title="'.__l('Admin Rejected').' ('.$this->Html->cInt($rejectedCount,false).')'.$approvedCountInfo.'"></i>':'';
		  ?>
          <li><?php echo $this->Html->link('<span class="badge badge-warning"><span><strong>'.$this->Html->cInt($projectStatuses[ConstLendProjectStatus::Pending],false).$countInfo.'</strong></span></span><span class="show">' .__l('Pending'). '</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'pending'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <?php if(isPluginEnabled('Idea')): ?>
        <li><?php echo $this->Html->link('<span class="badge badge-green"><span><strong>' . $this->Html->cInt($projectStatuses[ConstLendProjectStatus::OpenForIdea], false) . '</strong></span></span><span class="show">' .__l('Open for Voting'). '</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'idea'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <?php endif; ?>
      <li><?php echo $this->Html->link('<span class="badge badge-primary"><span><strong>'.$this->Html->cInt($projectStatuses[ConstLendProjectStatus::OpenForLending],false).'</strong></span></span><span class="show">' .__l('Open for Lending'). '</span>', array('controller'=>'lends','action'=>'myprojects'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-default"><span><strong>'.$this->Html->cInt($projectStatuses[ConstLendProjectStatus::ProjectAmountRepayment],false).'</strong></span></span><span class="show">' .__l('Project Amount Repayment'). '</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'goal'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-darkgreen"><span><strong>'.$this->Html->cInt($projectStatuses[ConstLendProjectStatus::ProjectClosed],false).'</strong></span></span><span class="show">' .sprintf(__l('Funding Closed and Paid to %s'),Configure::read('project.alt_name_for_lend_project_owner_singular_caps')). '</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'closed'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <?php
        if ($is_wallet_enabled) {
          $current_title = 'Refunded';
        } else {
          $current_title = 'Voided';
        }
      ?>
      <li><?php echo $this->Html->link('<span class="badge badge-danger"><span><strong>'.$this->Html->cInt($projectStatuses[ConstLendProjectStatus::ProjectCanceled],false).'</strong></span></span><span class="show">' .__l($current_title. " due to Canceled").'</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'cancelled'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-lightblue"><span><strong>'.$this->Html->cInt($projectStatuses[ConstLendProjectStatus::ProjectExpired],false).'</strong></span></span><span class="show">' .__l($current_title. " due to Expired").'</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'expired'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-black"><span><strong>'.$this->Html->cInt($system_drafted,false).'</strong></span></span><span class="show">' .__l('Drafted'). '</span>', array('controller'=>'lends','action'=>'myprojects','status'=>'draft'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
    </ul>
  </div>
  <?php  echo $this->element('paging_counter'); ?>
  <div class="table-responsive">
  <table class="table table-striped table-bordered table-condensed table-hover panel">
    <tr>
      <?php if (empty($this->request->params['named']['status']) || !in_array($this->request->params['named']['status'], array('cancelled', 'expired', 'closed'))) { ?>
        <th class="text-center"><?php echo __l('Actions');?></th>
      <?php } ?>
      <th class="text-left"><div class="js-filter"><?php echo $this->Paginator->sort('Project.name', __l('Name') ,array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php if(empty($this->request->params['named']['status'])  || !in_array($this->request->params['named']['status'], array('goal'))): ?>
	  <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.collected_amount', __l('Collected Amount') ,array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax')).' ('.Configure::read('site.currency').')';?></div> / <div class="js-filter js-no-pjax"><?php echo $this->Paginator->sort('Project.needed_amount', __l('Needed'),array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
	  <?php endif; ?>
      <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort( 'Project.project_fund_count', Configure::read('project.alt_name_for_lender_plural_caps') ,array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort( 'LoanTerm.months', __l('Terms') ,array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php
        if (!empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')):
          $colspan = 3;
        else:
          $colspan = 2;
        endif;
      ?>
      <?php if(empty($this->request->params['named']['status'])  || !in_array($this->request->params['named']['status'], array('goal'))): ?>
	  <th class="text-center"><div><?php echo __l('Lending Date'); ?>
        <div class="js-filter"><?php echo $this->Paginator->sort('Project.project_start_date', __l('Start') , array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div> / <div class="js-filter js-no-pjax"><?php echo $this->Paginator->sort('Project.project_end_date', __l('End') ,array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></div>
      </th>
	  <?php endif; ?>
	  <?php if (Configure::read('Project.is_project_owner_select_funding_method')) : ?>
		<th  class="text-center">
        <div><span class="clearfix"><?php echo sprintf(__l('Fixed %s'),Configure::read('project.alt_name_for_lend_present_continuous_caps')); ?></span><i class="fa fa-info-circle fa-fw js-tooltip" data-placement="top" title="<?php echo sprintf(__l('Fixed %s:  %s fund will be captured only if it reached the needed amount.When %s has been reached the ending date, then funds can start to be released.'), Configure::read('project.alt_name_for_lend_present_continuous_caps'), Configure::read('project.alt_name_for_project_singular_caps'), Configure::read('project.alt_name_for_project_singular_small')); echo "\n";echo sprintf(__l('Flexible %s:  %s fund will be captured even if it does not reached the needed amount.'), Configure::read('project.alt_name_for_lend_present_continuous_caps'),Configure::read('project.alt_name_for_project_singular_caps')); ?>"></i></div>
        </th>
		<?php endif; ?>
	  <?php if( !empty($this->request->params['named']['status']) && $this->request->params['named']['status'] == 'goal'): ?>
        <th><div class="js-filter"><?php echo $this->Paginator->sort('Lend.project_fund_goal_reached_date', __l('Goal Reached Date') , array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
		<th><?php echo __l('Repayment Amount') . ' ('.Configure::read('site.currency').')'; ?></th>
		<th><?php echo __l('Repayment Date'); ?></th>
      <?php endif; ?>
	  <?php if(isPluginEnabled('ProjectUpdates')):?>
      <th class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.blog_count', __l('Updates') , array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
	  <?php endif; ?>
      <?php if(isPluginEnabled('ProjectFollowers')): ?>
        <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.project_follower_count', __l('Followers') , array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php endif; ?>
      <th class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.message_count', __l('Comments') , array('url'=>array('controller'=>'lends','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
    </tr>
    <?php
      if (!empty($projects)):
        $i = 0;
        foreach ($projects as $project):
          if(!empty($project['Project']['project_end_date'])):
            $time_strap= strtotime($project['Project']['project_end_date']) -strtotime( date('Y-m-d'));
            $days = floor($time_strap /(60*60*24));
            if ($days > 0) {
              $project[0]['enddate'] =$days;
            } else {
              $project[0]['enddate'] =0;
            }
          endif;
    ?>
    <tr>
      <?php if (empty($this->request->params['named']['status']) || !in_array($this->request->params['named']['status'], array('cancelled', 'expired', 'closed'))) { ?>
        <td class="col-md-1 text-center">
          <?php if (($project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::ProjectClosed) || (!in_array($project['Lend']['lend_project_status_id'], array(ConstLendProjectStatus::ProjectCanceled, ConstLendProjectStatus::ProjectExpired, ConstLendProjectStatus::ProjectClosed)))): ?>
            <div class="dropdown">
              <a href="#" title="Actions" data-toggle="dropdown" class="fa fa-cog fa-lg fa-fw dropdown-toggle js-no-pjax"><span class="hide">Action</span></a>
              <ul class="list-unstyled dropdown-menu text-left clearfix">
                <?php if($project['Project']['is_draft']||$project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::Pending || $project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForIdea || ($project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending && Configure::read('Project.is_allow_project_owner_to_edit_project_in_open_status'))): ?>
                  <li><?php echo $this->Html->link('<i class="fa fa-pencil-square-o fa-fw"></i>'.__l('Edit'), array('controller' => 'projects', 'action' => 'edit', $project['Project']['id'], 0), array('class' => 'edit js-edit', 'title' => __l('Edit'),'escape'=>false)); ?></li>
                <?php endif; ?>
                <?php if (Configure::read('Project.is_allow_owner_project_cancel') and $project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending) : ?>
                  <li><?php echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.__l('Cancel'), array('controller' => 'projects', 'action' => 'cancel', $project['Project']['id']), array('class' => 'edit js-confirm cancel', 'title' => __l('Cancel'), 'escape'=>false)); ?></li>
                <?php endif; ?>
                <?php if ($project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::ProjectAmountRepayment) { ?>
					<li><?php  echo $this->Html->link('<i class="fa fa-share fa-fw"></i>'.__l('Pay Repayment'), array('controller'=>'project_repayments', 'action'=>'add', $project['Project']['id']), array( 'title' => __l('Repayment'), 'class' => 'js-no-pjax', 'escape'=>false)); ?></li>
				<?php } ?>
                <?php if (in_array($project['Lend']['lend_project_status_id'], array(ConstLendProjectStatus::OpenForIdea, ConstLendProjectStatus::OpenForLending)) && isPluginEnabled('SocialMarketing')) { ?>
                  <li><?php  echo $this->Html->link('<i class="fa fa-share fa-fw"></i>'.__l('Share'), array('controller'=>'social_marketings','action'=>'publish', $project['Project']['id'],'type'=>'facebook', 'publish_action' => 'add'), array( 'title' => __l('Share'),'escape'=>false)); ?></li>
                <?php } ?>
				<?php if($project['Project']['is_draft'] || $project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::Pending){ ?>
		  <li><?php echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.__l('Delete'), Router::url(array('controller'=>'projects','action' => 'delete', $project['Project']['id']),true).'?redirect_to='.$this->request->url,array('class' => 'js-confirm', 'escape'=>false,'title' => __l('Delete')));?></li>
		  <?php } ?>
              </ul>
            </div>
          <?php endif; ?>
        </td>
      <?php } ?>
      <td class="text-left">
        <?php
          if ($is_wallet_enabled) {
            $project_status = $project['LendProjectStatus']['name'];
          } else {
            $project_status = str_replace("Refunded","Voided",$project['LendProjectStatus']['name']);
          }
        ?>
        <i title="<?php echo $this->Html->cText($project['LendProjectStatus']['name'], false);?>" class="fa fa-square fa-lg fa-fw project-status-<?php echo $this->Html->cInt($project['Lend']['lend_project_status_id'], false);?>"></i>
		<?php if(!empty($formFieldSteps) && in_array($project['Project']['id'], $rejectedProjectIds)):?>
			<i class="fa fa-info-circle fa-fw sfont js-tooltip" title="<?php echo __l('Admin Rejected'); ?>"></i>
		<?php endif; ?>
		<?php if(!empty($formFieldSteps) && count($formFieldSteps) > 1 && in_array($project['Project']['id'], $approvedProjectIds)):?>
			<i class="fa fa-info-circle fa-fw sfont js-tooltip greenc" title="<?php echo __l('Admin Approved'); ?>"></i>
		<?php endif; ?>
		<?php echo $this->Html->link($this->Html->cText($project['Project']['name'],false) , array('controller'=>'projects' , 'action'=>'view' , $project['Project']['slug'] , 'admin'=>false) , array('class' => 'cboxelement', 'escape' => false,'title'=> $this->Html->cText($project['Project']['name'],false))); ?>

		<?php if ($project['Project']['payment_method_id'] == ConstPaymentMethod::KiA && Configure::read('Project.is_project_owner_select_funding_method')):
			echo '<div class="clearfix"><span class="label label-info pro-status-11">'.__l('Flexible').'</span></div>';
		  endif;
		?>

      </td>
      <?php if(empty($this->request->params['named']['status'])  || !in_array($this->request->params['named']['status'], array('goal'))): ?>
	  <td class="text-right">
        <?php $collected_percentage = ($project['Project']['collected_percentage']) ? $project['Project']['collected_percentage'] : 0; ?>
        <div class="progress">
          <div style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" class="progress-bar"></div>
        </div>
        <p class="text-center"><?php echo $this->Html->cCurrency($project['Project']['collected_amount']); ?> / <?php echo $this->Html->cCurrency($project['Project']['needed_amount']); ?></p>
      </td>
	  <?php endif; ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_fund_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#backers', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_fund_count'], false))); ?></td>
      <td class="text-center"><?php echo $this->Html->cInt($project['Lend']['total_no_of_repayment'], false); ?></td>
       <?php if(empty($this->request->params['named']['status'])  || !in_array($this->request->params['named']['status'], array('goal'))): ?>
	  <td class="text-center">
        <?php
          if (empty($project['Project']['project_start_date']) || $project['Project']['project_start_date'] == '0000-00-00')   {
            echo '-';
          } else {
        ?>
        <div class="clearfix">
          <div class="progress-block clearfix">
            <?php
              $project_progress_precentage = 0;
              if(strtotime($project['Project']['project_start_date']) < strtotime(date('Y-m-d H:i:s'))) {
                if($project['Project']['project_end_date'] !==   NULL) {
                  $days_till_now = (strtotime(date("Y-m-d")) - strtotime(date($project['Project']['project_start_date']))) / (60 * 60 * 24);
                  $total_days = (strtotime(date($project['Project']['project_end_date'])) - strtotime(date($project['Project']['project_start_date']))) / (60 * 60 * 24);
                  if($total_days) {
                    $project_progress_precentage = round((($days_till_now/$total_days) * 100));
                  } else {
                    $project_progress_precentage = round((($days_till_now) * 100));
                  }
                  if($project_progress_precentage > 100) {
                    $project_progress_precentage = 100;
                  }
                } else {
                  $project_progress_precentage = 100;
                }
              }
            ?>
            <?php if ($project['Project']['project_end_date']): ?>
              <div class="progress progress-bar-warning">
                <div style="width:<?php echo ($project_progress_precentage > 100) ? '100%' : $project_progress_precentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($project_progress_precentage, false).'%'; ?>" class="progress-bar"></div>
              </div>
            <?php endif; ?>
            <p class="progress-value clearfix no-mar"><span><?php echo $this->Html->cDateTimeHighlight($project['Project']['project_start_date']);?></span>&nbsp;/&nbsp;<span><?php echo (!is_null($project['Project']['project_end_date']))? $this->Html->cDateTimeHighlight($project['Project']['project_end_date']): ' - ';?></span></p>
          </div>
        </div>
        <?php } ?>
      </td>
	  <?php endif; ?>
	  <?php if (Configure::read('Project.is_project_owner_select_funding_method')) : ?>
	  <td class="text-center"><?php if($project['Project']['payment_method_id']==ConstPaymentMethod::AoN){ echo 'Yes'; } else { echo 'No'; }?></td>
	  <?php endif; ?>
      <?php if(Configure::read('Project.is_project_comment_enabled') && !Configure::read('Project.is_fb_project_comment_enabled')):  ?>
        <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_comment_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#comments', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_comment_count'], false))); ?></td>
      <?php endif; ?>
	  <?php if( !empty($this->request->params['named']['status']) && $this->request->params['named']['status'] == 'goal'): ?>
        <td class="text-center"><?php echo ($project['Lend']['project_fund_goal_reached_date'])?$this->Html->cDate($project['Lend']['project_fund_goal_reached_date']):' ';?></td>
		<td class="text-center">
		<?php
			if($project['Lend']['next_repayment_date'] < date('Y-m-d') && isset($project['Project']['ProjectRepayment']['late_fee'])) {
				$next_repayment_amount = $project['Lend']['next_repayment_amount'] + $project['Project']['ProjectRepayment']['late_fee'];
			} else {
				$next_repayment_amount = $project['Lend']['next_repayment_amount'];
			}
			echo $this->Html->cCurrency($next_repayment_amount);
			if($project['Lend']['next_repayment_date'] < date('Y-m-d') && isset($project['Project']['ProjectRepayment']['late_fee'])) {
		?>
			<i class="fa fa-question-circle fa-fw js-tooltip" data-original-title="<?php echo sprintf(__l('Late Fee: %s'), $project['Project']['ProjectRepayment']['late_fee']); ?>"></i>
		<?php } ?>
		</td>
		<td class="text-center"><?php echo ($project['Lend']['next_repayment_date'] && $project['Lend']['next_repayment_date'] != '0000-00-00')?$this->Html->cDate($project['Lend']['next_repayment_date']):' ';?></td>
      <?php endif; ?>
	  <?php if(isPluginEnabled('ProjectUpdates')):?>
      <td class="text-center">
        <?php
          if (!empty($project['Project']['feed_url'])) {
            echo $this->Html->link($this->Html->cInt($project['Project']['project_feed_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#updates', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title'=> $this->Html->cInt($project['Project']['project_feed_count'], false)));
          } else {
            echo $this->Html->link($this->Html->cInt($project['Project']['blog_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#updates', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['blog_count'], false)));
          }
        ?>
      </td>
	  <?php endif; ?>
      <?php if(isPluginEnabled('ProjectFollowers')): ?>
        <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_follower_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#followers', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_follower_count'], false)));?></td>
      <?php endif; ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt(count($project['Project']['Message']), false),array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], 'admin' => false, '#comments'), array('escape' => false, 'title' => $this->Html->cInt(count($project['Project']['Message']), false)));?></td>

    </tr>
    <?php
        endforeach;
      else:
    ?>
    <tr>
      <td colspan="22">
      <div class="text-center no-items">
		<p><?php echo sprintf(__l('No %s available'), Configure::read('project.alt_name_for_lend_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps'));?></p>
	  </div>
	  </td>
    </tr>
    <?php
      endif;
    ?>
  </table>
  </div>
  <?php if (!empty($projects)) { ?>
     <div class="clearfix">
      <div class="pull-right paging js-pagination js-no-pjax"> <?php echo $this->element('paging_links'); ?> </div>
    </div>
  <?php } ?>
<?php if (!$this->request->params['isAjax']) { ?>
  </div>
<?php } ?>
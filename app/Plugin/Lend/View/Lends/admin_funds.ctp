<div class="main-admn-usr-lst js-response">
<?php if(empty($this->request->params['named']['view_type'])) : ?>
	<div class="bg-primary row">		
		<ul class="list-inline sec-1 navbar-btn">
			<?php
			$link2 = __l('Refunded');
			$link3 = __l('Lent');
			$link4 = __l('Canceled');
			?>
			<?php
			$project_percentage = '';
			$project_stat = '';
			$all = $fund_count;
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center ste-usr">'.$this->Html->cInt($refunded_count,false).'</span><span>' .$link2. '</span>', array('controller'=>'lends','action'=>'funds','status'=>'refunded'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?> 
				</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_percentage .= round((empty($refunded_count)) ? 0 : ( ($refunded_count / $fund_count) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block act-usr text-center">'.$this->Html->cInt($paid_count,false).'</span><span>' .$link3. '</span>', array('controller'=>'lends','action'=>'funds','status'=>'paid'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?>
				</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_percentage .= round((empty($paid_count)) ? 0 : ( ($paid_count / $fund_count) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center ina-usr">'.$this->Html->cInt($cancelled_count,false).'</span><span>' .$link4. '</span>', array('controller'=>'lends','action'=>'funds','status'=>'cancelled'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?>
				</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_percentage .= round((empty($cancelled_count)) ? 0 : ( ($cancelled_count / $fund_count) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center opn-i-usr">'.$this->Html->cInt($all,false).'</span><span>' .__l('All'). '</span>', array('controller'=>'lends','action'=>'funds'), array('class' => 'text-center', 'escape' => false));?>
				</div>
			</li>
			<li class="pull-right">
				<?php echo $this->Html->image('http://chart.googleapis.com/chart?cht=p&amp;chd=t:'.$project_percentage.'&amp;chs=120x120&amp;chco=30bcef|468847|f89406&amp;chf=bg,s,FF000000'); ?>
			</li>
		</ul>		
	</div>
	<div class="clearfix lends">		
		<div class="navbar-btn">
			<h3>
				<i class="fa fa-th-list fa-fw"></i> <?php echo __l('List');?>
			</h3>
				<?php
				$placeholder = __l('Search');
				if (!empty($this->request->params['named']['q'])) {
				$placeholder = $this->request->params['named']['q'];
				}
				?>
			<ul class="list-unstyled clearfix">
				<li class="pull-right"> 
					<div class="form-group srch-adon">
						<?php echo $this->Form->create('Lend' ,array('url' => array('controller' => 'lends','action' => 'funds')), array('type' => 'get', 'class' => 'form-search')); ?>
						<span class="form-control-feedback " id="basic-addon1"><i class="fa fa-search text-default"></i></span>
						<?php echo $this->Form->input('q', array('label' => false,' placeholder' => __l('Search'), 'class' => 'form-control')); ?>
						<div class="hide">
						<?php echo $this->Form->submit(__l('Search'));?>
						</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</li>
			</ul>
		</div>		
		<?php endif; ?>		
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="text-center table-action-width"><?php echo __l('Action');?></th>
						<?php if(empty($this->request->params['named']['view_type'])) : ?>
						<th class="text-left"><div><?php echo $this->Paginator->sort('Project.name', __l(Configure::read('project.alt_name_for_project_singular_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<?php endif;?>
						<th class="text-left"><div><?php echo $this->Paginator->sort('User.username', __l(Configure::read('project.alt_name_for_lender_singular_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<th class="text-center"><div><?php echo __l('Paid Amount') . ' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('amount', sprintf(__l('Amount to %s'), Configure::read('project.alt_name_for_lend_project_owner_singular_small')), array('class' => 'js-no-pjax js-filter')).' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('site_fee', __l('Site Commission'), array('class' => 'js-no-pjax js-filter')).' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('created', sprintf(__l('%s On'), Configure::read('project.alt_name_for_lend_past_tense_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<th><div><?php echo $this->Paginator->sort('Status', __l('Status'), array('class' => 'js-no-pjax js-filter'));?></div></th>
					</tr>
				</thead>
				<tbody class="h6">
					<?php
					if (!empty($projectFunds)):
					$lend_amount = $site_fee_amount = $paid_amount = 0;
					foreach ($projectFunds as $projectFund):
					$lend_amount += $projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee'];
					$site_fee_amount += $projectFund['ProjectFund']['site_fee'];
					$paid_amount += $projectFund['ProjectFund']['amount'];
					?>
					<?php if(!empty($projectFund['Project']['Lend'])){ ?>
					<tr>
					<td class="text-center">					<?php if ($projectFund['Project']['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending && ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Authorized) ): ?>
						<div class="dropdown">
							<a href="#" title="Actions" data-toggle="dropdown" data-hover="dropdown" class="dropdown-toggle js-no-pjax"><i class="fa fa-cog"></i><span class="hide">Action</span></a>
							<ul class="dropdown-menu">
								<li>
								<?php  echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.sprintf(__l('Cancel %s'),Configure::read('project.alt_name_for_lend_singular_caps')), array('controller' => 'project_funds', 'action' => 'edit_fund', 'project_fund' => $projectFund['ProjectFund']['id'], 'type' => 'cancel', 'return_page' => 'admin', 'admin' => false), array('class' => 'js-confirm','escape'=>false, 'title' => sprintf(__l('Cancel %s'),Configure::read('project.alt_name_for_lend_singular_caps')))); ?>
								</li>
								<?php echo $this->Layout->adminRowActions($projectFund['ProjectFund']['id']);  ?>
							</ul>
						</div>
					<?php endif; ?>
					</td>
					<?php if(empty($this->request->params['named']['view_type'])) : ?>
					<td class="text-left">
					<div class="clearfix htruncate">
					<?php
					if($is_wallet_enabled)
					{
					$project_status = $projectFund['Project']['Lend']['LendProjectStatus']['name'];
					}
					else
					{
					$project_status = str_replace("Refunded","Voided",$projectFund['Project']['Lend']['LendProjectStatus']['name']);
					}
					?>
					<i title="<?php echo $this->Html->cText($project_status, false);?>" class="fa fa-square project-status-<?php echo $this->Html->cInt($projectFund['Project']['Lend']['lend_project_status_id'], false);?>"></i>
					<?php echo $this->Html->link($this->Html->cText($projectFund['Project']['name']), array('controller'=> 'projects', 'action'=>'view', $projectFund['Project']['slug'],'admin' => false), array('escape' => false,'title'=>$this->Html->cText($projectFund['Project']['name'],false)));?>
					</div>
					</td>
					<?php endif; ?>
					<td class="text-left">
						<div class="media">
							<div class="pull-left">
								<?php echo $this->Html->getUserAvatar($projectFund['User'], 'micro_thumb',true, '', 'admin');?>
							</div>
							<div class="media-body">
								<p>
									<?php echo $this->Html->getUserLink($projectFund['User']); ?>
								</p>
							</div>
						</div>
					</td>
					<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount']);?></td>

					<td class="text-center">
					<?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee']); ?>

					</td>
					<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['site_fee']);?></td>

					<td class="text-center"><?php echo $this->Html->cDateTimeHighlight($projectFund['ProjectFund']['created']);?></td>

					<td>
					<?php
					$refund = __l('Lent');
					if($projectFund['Project']['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending) {
					$refund = Configure::read('project.alt_name_for_lend_past_tense_caps');
					} else {
					$refund = __l('Lent');
					}
					if ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaymentFailed) {
					$refund = __l('Failed');
					$class = ' class="hide js-faild"';
					} elseif ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaymentFailed) {
					$refund = __l('Canceled');
					$class = ' class="hide js-faild"';
					} elseif ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Canceled) {
					$refund = $refund;
					}
					echo $refund;
					?>
					</td>

					</tr>
					<?php } ?>
					<?php
					endforeach;
					?>
					<?php
					else:
					?>
					<tr>
					<td colspan="9" class="text-center text-danger"><i class="fa fa-exclamation-triangle fa-fw"></i> <?php echo sprintf(__l('No %s Lends available'), Configure::read('project.alt_name_for_project_singular_caps'));?></td>
					</tr>
					<?php
					endif;
					?>
				</tbody>
			</table>
		</div>
	</div>		
	<div class="page-sec navbar-btn">
		<?php
		if (!empty($projectFunds)) : ?>
		<div class="row">
			<div class="col-xs-12 col-sm-6 pull-right">
				<?php  echo $this->element('paging_links'); ?>
			</div>
		</div>
		<?php endif;?>
	</div>
</div>

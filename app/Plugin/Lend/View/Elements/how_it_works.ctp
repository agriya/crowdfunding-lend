<?php
  if (isPluginEnabled('Idea')) {
    $is_ideaEnabled = 1;
  }
  $payment_methods_lend = '';
  $lend_refund_text = '';
  if (isPluginEnabled('Wallet')) {
	$lend_refund_text = '(amount will be refunded)';
  }
  if(isPluginEnabled('Wallet')) {
		if(!empty($payment_methods_lend)){
			$payment_methods_lend.= '/';
		}
		$payment_methods_lend.= 'Wallet';
  }
?>
	<div class="lend clearfix">
      <div class="page-header clearfix no-pad mspace">
			<?php
				if(empty($this->request->params['plugin']) && $this->request->params['controller'] == 'nodes') {
			?>
					<h4 class="text-b no-mar"><?php echo __l(Configure::read('project.alt_name_for_lend_singular_caps'));?></h4>
			<?php
				} else if($this->request->params['plugin'] == 'projects' && $this->request->params['controller'] == 'projects') {
			?>
					<h3 class="h2 roboto-bold text-center"><?php echo __l('How It Works');?>
						<sup><?php echo $this->Html->image('quesion-circle.png', array('alt' => __l('[Image: Quesion Circle]'))); ?> </sup>
					</h3>
					<p class="h3 text-center marg-btom-30"><?php echo __l('People choose to lend. Amount is captured by end date/goal reached of the project. Borrowers offer interest.');?></p>
			<?php
				}
			?>
      </div>
      <div class="col-sm-6 top-mspace">
        <div class="project_guideline">
          <ul class="project-guideline-block list-unstyled primaryNav project-owner">
            <li class="home"><span class="btn btn-warning"><?php echo __l(Configure::read('project.alt_name_for_lend_project_owner_singular_caps')); ?> </span>
              <ul class="list-unstyled">
                <?php
                if(!empty($is_ideaEnabled)) {
                ?>
                  <li><span><?php echo sprintf(__l('Adds an %s'), 'Idea'); ?> </span></li>
                  <li>
                    <span>
                      <?php
                        echo sprintf(__l('Admin moves the %s for lending'),Configure::read('project.alt_name_for_project_singular_small'));
                      ?>
                    </span>
					<?php if(Configure::read('Project.is_allow_owner_project_cancel')): ?>
					<ul class="list-unstyled  first">
                      <li class ="offset"><span><?php echo sprintf(__l('Have option to cancel %s in %s Posted'), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_project_plural_caps')); ?> </span></li>
                    </ul>
					<ul class="list-unstyled guide-expire second">
					<li class ="offset"><span><?php echo sprintf(__l('Expired (If %s is fixed funding and %s didn\'t reach goal.) '), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
                    </ul>
					<?php else: ?>
						<ul class="list-unstyled  first">
						  <li class ="offset"><span><?php echo sprintf(__l('Expired (If %s is fixed lending and %s didn\'t reach goal.) '), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
						</ul>
					<?php endif; ?>
				  </li>
                <?php } else { ?>
                  <li>
					<span><?php echo sprintf(__l('Adds a %s'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span>
					<?php if(Configure::read('Project.is_allow_owner_project_cancel')): ?>
					<ul class="list-unstyled  first">
                      <li class ="offset"><span><?php echo sprintf(__l('Have option to cancel %s in %s posted'), Configure::read('project.alt_name_for_project_plural_caps'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
                    </ul>
					<ul class="list-unstyled  guide-expire2 second">
					<li class ="offset"><span><?php echo sprintf(__l('Expired (If %s is fixed lending and %s didn\'t reach goal.) '), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
                    </ul>
					<?php else: ?>
						<ul class="list-unstyled  first">
						  <li class ="offset"><span><?php echo sprintf(__l('Expired (If %s is fixed lending and %s didn\'t reach goal.) '), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_project_singular_small')); ?> </span></li>
						</ul>
					<?php endif; ?>
				  </li>
                <?php } ?>
                  <li class="branch last-list">
                    <span>
                      <?php
                        echo sprintf(__l('%s lends a %s'),Configure::read('project.alt_name_for_lender_singular_caps'), Configure::read('project.alt_name_for_project_singular_small'));
                        if(!empty($payment_methods_lend)) {
                          echo ' through '. $payment_methods_lend;
                        }
                      ?>
                    </span>
                  </li>
                  <li><span><?php echo sprintf(__l('After %s reaches the goal'), Configure::read('project.alt_name_for_project_singular_small')) . ' <span class="show">' . sprintf(__l('site transfer amount to %s after deduct the site commission'), Configure::read('project.alt_name_for_lend_project_owner_singular_small')) . '</span>'; ?></span>
                  </li>


				  <li>
				  <span><?php echo sprintf(__l('%s Moved to Project Amount Repayment state.'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span>
				  <ul class="list-unstyled first">
					  <li class ="offset">
							<span>
							  <?php
								echo __l('If repayment schedule date exceeded, have to pay with late payment fee.');
							  ?>
							</span>
					  </li>
				  </ul>
				  </li>
                  <li class="branch last-list"><span><?php echo sprintf(__l('Repay the amount to %s, by given repayment schedule.'),Configure::read('project.alt_name_for_lender_singular_caps')); ?>  </span>
				  </li>

				  <li><span><?php echo sprintf(__l('%s Closed'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span></li>



              </ul>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-sm-6 top-mspace">
        <div class="project_guideline">
          <ul class="project-guideline-block list-unstyled primaryNav project-owner">
            <li class="home"><span class="btn btn-warning"><?php echo __l(Configure::read('project.alt_name_for_lender_singular_caps')); ?> </span>
              <ul class="list-unstyled">
                <?php
                  if(!empty($is_ideaEnabled)) {
                ?>
                    <li>
                      <span>
                        <?php
                          echo sprintf(__l('Votes an %s'), __l('Idea'));
                        ?>
                      </span>
                    </li>
                    <li>
                      <span>
                        <?php
                          echo sprintf(__l('Admin moves the %s for lending'), Configure::read('project.alt_name_for_project_singular_small'));
                        ?>
                      </span>
					  <ul class="list-unstyled  first">
						  <li class ="offset"><span><?php echo sprintf(__l('Expired %s'), $lend_refund_text); ?> </span></li>
						</ul>
                    </li>
					<li class="branch last-list">
                <?php } else { ?>
					<li>
                  <?php } ?>
                    <span>
                      <?php
                        echo sprintf(__l('Lends a %s'), Configure::read('project.alt_name_for_project_singular_small'));
                        if(!empty($payment_methods_lend)) {
                          echo ' through '. $payment_methods_lend;
                        }
                      ?>
                    </span>
                  </li>
                  <li><span><?php echo sprintf(__l('After %s reaches the goal'), Configure::read('project.alt_name_for_project_singular_small')) . '<span class="show">' . sprintf(__l('site will transfer amount to %s'), Configure::read('project.alt_name_for_lend_project_owner_singular_small')) . '</span>'; ?> </span></li>
				  <li>
                  <span><?php echo sprintf(__l('%s Moved to Project Amount Repayment state.'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span>
				  <ul class="list-unstyled first">
					  <li class ="offset">
							<span>
							  <?php
								echo sprintf(__l('If repayment schedule date exceeded, %s will pay with late payment fee.'), Configure::read('project.alt_name_for_lend_project_owner_singular_caps'));
							  ?>
							</span>
					  </li>
				  </ul>
				  </li>
                  <li class="branch last-list"><span><?php echo sprintf(__l('%s repay the amount, by given repayment schedule.'),Configure::read('project.alt_name_for_lend_project_owner_singular_caps')); ?>  </span>
				  </li>

				  <li><span><?php echo sprintf(__l('%s Closed'), Configure::read('project.alt_name_for_project_singular_caps')); ?> </span></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>
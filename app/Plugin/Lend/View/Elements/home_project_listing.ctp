
	<div class="clearfix" itemtype="http://schema.org/Product" itemscope>
		<div itemprop="Name">
			<?php echo $this->Html->link($this->Html->image('lend.png'), array('controller' => 'projects', 'action' => 'discover', 'project_type'=> 'lend' , 'admin' => false), array('class' => 'zoom-plus js-no-pjax','title' => __l(Configure::read('project.alt_name_for_lend_singular_caps')), 'escape' => false));?>
			<h3 class="h4 zoom-plus">
				<?php echo $this->Html->link(__l(Configure::read('project.alt_name_for_lend_singular_caps')), array('controller' => 'projects', 'action' => 'discover', 'project_type'=> 'lend' , 'admin' => false), array('class'=> 'text-uppercase clr-org txt js-no-pjax','title' => __l(Configure::read('project.alt_name_for_lend_singular_caps'))));?>
			</h3>
		</div>
		<p class="h4" itemprop="description"><?php echo sprintf(__l("In %s %s, %s amount is captured by end date/goal reached of the %s and %s earns interest."), Configure::read('project.alt_name_for_lend_singular_small'), Configure::read('project.alt_name_for_project_plural_small'), Configure::read('project.alt_name_for_lend_past_tense_small'), Configure::read('project.alt_name_for_project_singular_small'), Configure::read('project.alt_name_for_lender_singular_small')); ?> </p>
	</div>


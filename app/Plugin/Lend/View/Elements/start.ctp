<div class="col-xs-12 col-sm-6 col-md-3 start-projects">
	<div class="bg-light-gray">
		<div class="img-contain"><?php echo $this->Html->image('lend.png'); ?></div>
		<?php echo $this->Html->link(__l(Configure::read('project.alt_name_for_lend_singular_caps')). " " . __l(Configure::read('project.alt_name_for_project_singular_caps')), array('controller' => 'projects', 'action' => 'add', 'project_type'=>'lend', 'admin' => false), array('title' => __l(Configure::read('project.alt_name_for_lend_singular_caps')). " " . __l(Configure::read('project.alt_name_for_project_singular_caps')),'class' => 'js-tooltip h3 text-warning', 'escape' => false));?>
		<p class="navbar-btn"><?php echo __l('People choose to lend. Amount is captured by end date/goal reached of the project. Borrowers offer interest.'); ?></p>
	</div>
</div>
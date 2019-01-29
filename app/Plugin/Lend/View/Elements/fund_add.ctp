<?php
if(!empty($response_data['lend'])){
	$lend = $response_data['lend'];
	$class = '';
	if (strlen($project['Project']['name']) > 40) {
		$class .= ' title-double-line';
	}
}
$strAdditionalInfo = '';
?>
<div>
	<section data-offset-top="10" data-spy=""
		class=" row <?php echo $class; ?>">
		<div>
			<div class="payment-img col-sm-1">
				<?php echo $this->Html->link($this->Html->showImage('Project', $project['Attachment'], array('dimension' => 'medium_thumb', 'alt' => sprintf(__l('[Image: %s]'), $this->Html->cText($project['Project']['name'], false)), 'title' => $this->Html->cText($project['Project']['name'], false), 'class' => 'js-tooltip'),array('aspect_ratio'=>1)), array('controller' => 'projects', 'action' => 'view',  $project['Project']['slug'], 'admin' => false), array('escape' => false)); ?>
			</div>
			<div class="col-md-7">
				<h3 class="no-mar">
					<?php echo $this->Html->link($this->Html->filterSuspiciousWords($this->Html->cText($project['Project']['name'], false), $project['Project']['detected_suspicious_words']), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug']), array('escape' => false));?>
				</h3>
				<p>
					<?php echo __l('A') . ' '; ?>
					<?php
					$response = Cms::dispatchEvent('View.Project.displaycategory', $this, array(
							'data' => $project
					));
					if (!empty($response->data['content'])) {
						echo $response->data['content'];
					}
					?>
					<?php echo sprintf(__l('%s in '), Configure::read('project.alt_name_for_project_singular_small')) . ' '; ?>
					<?php
					if (!empty($project['City']['name'])) {
						echo $this->Html->cText($project['City']['name'], false) . ', ';
					}
					if (!empty($project['Country']['name'])) {
						echo $this->Html->cText($project['Country']['name'], false);
					}
					?>
					<?php echo __l(' by '); ?>
					<?php echo $this->Html->link($this->Html->cText($project['User']['username']), array('controller' => 'users', 'action' => 'view', $project['User']['username']), array('escape' => false));?>

				</p>
			</div>
		</div>
	</section>
</div>
<div class="projectFunds form clearfix row">
	<div class="clearfix">
		<div class="col-md-12">
			<div class="clearfix">
				<fieldset>
					<legend>
						<?php echo sprintf(__l('%s Amount'),Configure::read('project.alt_name_for_lend_singular_caps')); ?>
					</legend>
					<div>
						<?php
						echo $this->Form->input('latitude',array('type' => 'hidden', 'id'=>'latitude'));
						echo $this->Form->input('longitude',array('type' => 'hidden', 'id'=>'longitude'));
						echo $this->Form->input('project_id',array('type'=>'hidden'));
						echo $this->Form->input('amount',array('label' => sprintf(__l('%s amount'),Configure::read('project.alt_name_for_lend_singular_caps')) .' ('.Configure::read('site.currency').')'));
						echo $this->requestAction(array('controller' => 'nodes', 'action' => 'view', 'type' => 'page', 'slug' => 'lend-terms'), array('return'));
						?>
						<div class="lendform checkbox">
							<?php echo $this->Form->input('Lend.is_agree_terms_conditions',array('class'=>'js-term js-no-pjax', 'div'=>'false','type'=>'checkbox','label'=>__l('I have read and understand ').$this->Html->cText(Configure::read('site.name'), false). '\'s ' . strtolower(Configure::read('project.alt_name_for_lend_singular_caps')) . ' ' . __l('terms.'))); ?>
						</div>
					</div>
				</fieldset>
				<div class="col-md-6">
					<fieldset>
						<legend>
							<?php echo sprintf(__l('Personalize your  %s'),Configure::read('project.alt_name_for_lend_singular_caps')); ?>
						</legend>
						<div class="group-block personal-radio">
							<?php echo $this->Form->input('is_anonymous',array('type' =>'radio','default'=>ConstAnonymous::None,'options'=>$radio_options,'legend'=>false));?>
						</div>
					</fieldset>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="clearfix">
				<?php echo $this->element('lend-faq', array('cache' => array('config' => 'sec')),array('plugin'=>'Lend')); ?>
			</div>
		</div>
		<div class="col-xs-12">
			<legend>
				<?php echo __l('Select Payment Type'); ?>
			</legend>
			<?php  echo $this->element('payment-get_gateways', array('model'=>'ProjectFund','type'=>'is_enable_for_lend','is_enable_wallet'=>1, 'project_type'=>$project['ProjectType']['name'], 'cache' => array('config' => 'sec')));?>
		</div>
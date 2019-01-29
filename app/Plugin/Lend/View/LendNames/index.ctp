<?php /* SVN: $Id: index.ctp 2879 2010-08-27 11:08:48Z sakthivel_135at10 $ */ ?>
<?php if (!$this->request->params['isAjax']) { ?>

<div class="js-response">
  <?php } ?>
  <div class="clearfix" itemtype="http://schema.org/Product" itemscope>
  <div class="pull-left circ space3 row offset1 lend-status img-circle text-center" itemprop="Name"> <span class="show text-center"><?php echo $this->Html->image('lend-hand.png', array('width' => 50, 'height' => 50)); ?></span><?php echo Configure::read('project.alt_name_for_lend_singular_caps'); ?> </div>
  <h4 class="lendc"><?php echo sprintf(__l('My %s'), Configure::read('project.alt_name_for_lend_plural_caps')); ?></h4>
  </div>
  <?php echo $this->element('Lend.lend_summary');?>

  <?php echo $this->element('paging_counter');?>
  <table class="table table-striped table-bordered table-condensed table-hover">
  <tr>
    <th class="js-filter text-left"><?php echo $this->Paginator->sort('name', __l('Name'), array('url' => array('controller' => 'lend_names', 'action' => 'index'), 'class' => 'js-no-pjax'));?></th>
    <th class="js-filter text-right"><?php echo $this->Paginator->sort('project_fund_count', __l('No. of Loans'), array('url' => array('controller' => 'lend_names', 'action' => 'index'), 'class' => 'js-no-pjax'));?></th>
    <th class="js-filter text-right"><?php echo $this->Paginator->sort('average_rate', __l('Average Rate'), array('url' => array('controller' => 'lend_names', 'action' => 'index'), 'class' => 'js-no-pjax'));?></th>
    <th class="js-filter text-right"><?php echo $this->Paginator->sort('amount', __l('Amount'). ' (' 

. Configure::read('site.currency') . ')', array('url' => array('controller' => 'lend_names', 'action' => 'index'), 'class' => 'js-no-pjax'));?></th>
    <th class="js-filter text-right"><?php echo $this->Paginator->sort('total_repayment_amount', __l('Total Capital Returned'). ' (' 

. Configure::read('site.currency') . ')', array('url' => array('controller' => 'lend_names', 'action' => 'index'), 'class' => 'js-no-pjax'));?></th>
    <th class="js-filter text-right"><?php echo $this->Paginator->sort('total_repayment_interest_amount', __l('Total Interest Returned'). ' (' 

. Configure::read('site.currency') . ')', array('url' => array('controller' => 'lend_names', 'action' => 'index'), 'class' => 'js-no-pjax'));?></th>
    <th class="js-filter text-right"><?php echo $this->Paginator->sort('created', sprintf(__l('%s On'),Configure::read('project.alt_name_for_lend_past_tense_caps')) , array('url'=>array('controller'=>'lend_names','action'=>'index'), 'class' => 'js-no-pjax'));?></th>
  </tr>
  <?php
    if (!empty($lendNames)):
      $i = 0;
      foreach ($lendNames as $lendName):
        $class = null;
        if ($i++ % 2 == 0) {
          $class = ' class="altrow"';
        }
  ?>
  <tr<?php echo $class;?>>
	<td class="text-left"><?php echo $this->Html->link($this->Html->cText($lendName['LendName']['name'], false), array('controller'=>'lends','action'=>'myfunds','lend_name_id'=> $lendName['LendName']['id']), array('class' => 'js-no-pjax ', 'escape' => false, 'data-target'=>"#myModal1", 'data-toggle'=>"modal")); ?></td>
	<td class="text-center"><?php echo $this->Html->link($this->Html->cInt($lendName['LendName']['project_fund_count'], false), array('controller'=>'lends','action'=>'myfunds','lend_name_id'=>$lendName['LendName']['id']), array('class' => 'js-no-pjax', 'escape' => false, 'data-target'=>"#myModal1", 'data-toggle'=>"modal")); ?></td>
    <td class="text-center"><?php echo$this->Html->cFloat($lendName['LendName']['average_rate'], false). '%' ; ?></td>
    <td class="text-right"><?php echo $this->Html->cCurrency($lendName['LendName']['amount']) ;?></td>
    <td class="text-right"><?php echo $this->Html->cCurrency($lendName['LendName']['total_repayment_amount']) ;?></td>
    <td class="text-right"><?php echo $this->Html->cCurrency($lendName['LendName']['total_repayment_interest_amount']) ;?></td>
    <td class="text-right"><?php echo $this->Html->cDateTimeHighlight($lendName['LendName']['created']) ;?></td>

  </tr>
  <?php
      endforeach;
    else:
  ?>
  <tr>
    <td colspan="7">
	  <div class="text-center">
		<p><?php echo sprintf(__l('No %s available'), Configure::read('project.alt_name_for_lend_plural_caps'));?></p>
	  </div>
	</td>
  </tr>
  <?php
    endif;
  ?>
  </table>
  <?php if (!empty($projectFunds)) { ?>
  <div class="clearfix">
  <div class=" pull-right paging js-pagination js-no-pjax"> <?php echo $this->element('paging_links'); ?> </div>
  </div>
  <?php } ?>
  <?php if (!$this->request->params['isAjax']) { ?>
</div>
<?php } ?>
  <div id="myModal1" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header hide"></div>
			<div class="modal-body js-social-link-div clearfix">
				<div class="text-center">
				<?php echo $this->Html->image('throbber.gif', array('alt' => __l('[Image:Loader]') ,'width' => 25, 'height' => 25, 'class' => 'js-loader')); ?></div>
			</div>
			<div class="modal-footer"> <a href="#" class="btn js-no-pjax" data-dismiss="modal"><?php echo __l('Close'); ?></a> </div>
		</div>
	</div>
  </div>
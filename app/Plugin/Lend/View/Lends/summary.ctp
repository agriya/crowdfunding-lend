<?php /* SVN: $Id: index.ctp 2879 2010-08-27 11:08:48Z sakthivel_135at10 $ */ ?>
<div class="js-response">
  <table class="table table-striped table-bordered table-condensed table-hover">
  <tr>
    <th><?php echo __l('Borrowing State');?></th>
    <th class="text-right"><?php echo __l('No. of Loans');?></th>
    <th class="text-right"><?php echo __l('Average Rate');?></th>
    <th class="text-right"><?php echo __l('Total Lent'). ' (' . Configure::read('site.currency') . ')';?></th>
    <th class="text-right"><?php echo __l('Toal Captital Returned'). ' (' . Configure::read('site.currency') . ')';?></th>
    <th class="text-right"><?php echo __l('Toal Interest Returned'). ' (' . Configure::read('site.currency') . ')';?></th>
  </tr>
  <?php
    if (!empty($user)):
      $i = 0;
	  $status = array('withdrawn' => 'Withdrawn', 'collection' => 'Collection', 'closed' => 'Closed', 'default' => 'Default');
      foreach ($status as $key => $stats):
        $class = null;
        if ($i++ % 2 == 0) {
          $class = ' class="altrow"';
        }

    ?>
  <tr<?php echo $class;?>>
	<td class="text-left"><?php echo $this->Html->cText($stats);?></td>
    <td class="text-center"><?php echo $this->Html->cInt($user['User'][$key.'_no_of_loans']);?></td>
    <td class="text-center"><?php echo $this->Html->cFloat($user['User'][$key.'_average_rate'], false).'%';?></td>
    <td class="text-right"><?php echo $this->Html->cCurrency($user['User'][$key.'_total_lent']);?></td>
	<td class="text-right"><?php echo $this->Html->cCurrency($user['User'][$key.'_total_capital_returned']);?></td>
	<td class="text-right"><?php echo $this->Html->cCurrency($user['User'][$key.'_total_interest_returned']);?></td>
  </tr>
  <?php
      endforeach;
  ?>
  <?php
    endif;
  ?>
  </table>

</div>

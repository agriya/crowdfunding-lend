<?php
/**
 * CrowdFunding
 *
 * PHP version 5
 *
 * @category   PHP
 * @package    Crowdfunding
 * @subpackage Core
 * @author     Agriya <info@agriya.com>
 * @copyright  2018 Agriya Infoway Private Ltd
 * @license    http://www.agriya.com/ Agriya Infoway Licence
 * @link       http://www.agriya.com
 */
class RepaymentSchedule extends AppModel
{
    public $name = 'RepaymentSchedule';
    public $displayField = 'name';
    //$validate set in __construct for multi-language support
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    public $hasMany = array(
        'Lend' => array(
            'className' => 'Lend.Lend',
            'foreignKey' => 'repayment_schedule_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        )
    );
    function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->_permanentCacheAssociations = array(
            'Project'
        );
        $this->moreActions = array(
            ConstMoreAction::Disapproved => __l('Inactive') ,
            ConstMoreAction::Approved => __l('Active') ,
            ConstMoreAction::Delete => __l('Delete')
        );
        $this->validate = array(
            'name' => array(
                'rule' => 'notempty',
                'allowEmpty' => false,
                'message' => __l('Required')
            ) ,
            'day' => array(
                'rule2' => array(
                    'rule' => '_validateDay',
                    'allowEmpty' => false,
                    'message' => __l('Enter the valid particular day of month (within 28)') ,
                ) ,
                'rule1' => array(
                    'rule' => 'numeric',
                    'allowEmpty' => false,
                    'message' => __l('The day should be a numeric value.') ,
                ) ,
            ) ,
        );
    }
    function _validateDay() 
    {
        if (!empty($this->data['RepaymentSchedule']['day'])) {
            if ($this->data['RepaymentSchedule']['is_particular_day_of_month']) {
                if ($this->data['RepaymentSchedule']['day'] > 28) {
                    return false;
                }
            }
        }
        return true;
    }
}
?>
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
class ProjectFundRepayment extends AppModel
{
    public $name = 'ProjectFundRepayment';
    public $displayField = 'id';
    //$validate set in __construct for multi-language support
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
        'OwnerUser' => array(
            'className' => 'User',
            'foreignKey' => 'owner_user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
        'Project' => array(
            'className' => 'Projects.Project',
            'foreignKey' => 'project_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
        'ProjectFund' => array(
            'className' => 'Projects.ProjectFund',
            'foreignKey' => 'project_fund_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
        'ProjectRepayment' => array(
            'className' => 'Lend.ProjectRepayment',
            'foreignKey' => 'project_repayment_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
    );
    function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->validate = array(
            'amount' => array(
                'rule' => 'notempty',
                'allowEmpty' => false,
                'message' => __l('Required')
            ) ,
            'interest' => array(
                'rule' => 'notempty',
                'allowEmpty' => false,
                'message' => __l('Required')
            ) ,
            'interest_rate' => array(
                'rule' => 'notempty',
                'allowEmpty' => false,
                'message' => __l('Required')
            ) ,
            'term' => array(
                'rule' => 'notempty',
                'allowEmpty' => false,
                'message' => __l('Required')
            ) ,
        );
    }
}
?>
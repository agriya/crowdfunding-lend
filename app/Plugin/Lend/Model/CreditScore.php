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
class CreditScore extends AppModel
{
    public $name = 'CreditScore';
    public $displayField = 'name';
    //$validate set in __construct for multi-language support
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    public $hasMany = array(
        'Lend' => array(
            'className' => 'Lend.Lend',
            'foreignKey' => 'credit_score_id',
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
    public $hasAndBelongsToMany = array(
        'LendName' => array(
            'className' => 'LendName',
            'joinTable' => 'lend_names_credit_scores',
            'foreignKey' => 'credit_score_id',
            'associationForeignKey' => 'lend_name_id',
            'unique' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'finderQuery' => '',
            'deleteQuery' => '',
            'insertQuery' => ''
        ) ,
    );
    function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->_permanentCacheAssociations = array(
            'Project'
        );
        $this->moreActions = array(
            ConstMoreAction::Disapproved => __l('Disapprove') ,
            ConstMoreAction::Approved => __l('Approve') ,
            ConstMoreAction::Delete => __l('Delete')
        );
        $this->validate = array(
            'name' => array(
                'rule' => 'notempty',
                'allowEmpty' => false,
                'message' => __l('Required')
            ) ,
            'interest_rate' => array(
                'rule' => 'numeric',
                'allowEmpty' => false,
                'message' => __l('The interest rate should be a numeric value.')
            ) ,
            'suggested_interest_rate' => array(
                'rule' => 'numeric',
                'allowEmpty' => false,
                'message' => __l('The suggested interest rate should be a numeric value.')
            ) ,
        );
    }
	public function GetProjectRelatedMasters() 
    {
        $LendCreditScore = $this->find('list', array(
            'conditions' => array(
                'CreditScore.is_approved' => 1
            ) ,
            'order' => array(
                'CreditScore.name' => 'ASC'
            ) ,
        ));
        $LendLoanTerm = $this->Lend->LoanTerm->find('list', array(
            'conditions' => array(
                'LoanTerm.is_approved' => 1
            ) ,
            'order' => array(
                'LoanTerm.name' => 'ASC'
            ) ,
        ));
        $LendRepaymentSchedule = $this->Lend->RepaymentSchedule->find('list', array(
            'conditions' => array(
                'RepaymentSchedule.is_approved' => 1
            ) ,
            'order' => array(
                'RepaymentSchedule.name' => 'ASC'
            ) ,
        ));
        $response['creditScores'] = $LendCreditScore;
        $response['loanTerms'] = $LendLoanTerm;
        $response['repaymentSchedules'] = $LendRepaymentSchedule;
		return $response;
    }
}
?>
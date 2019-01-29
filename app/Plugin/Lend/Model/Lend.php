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
class Lend extends AppModel
{
    public $name = 'Lend';
    var $useTable = 'project_lend_fields';
    public $displayField = 'id';
    public $actsAs = array(
        'Sluggable' => array(
            'label' => array(
                'name'
            )
        ) ,
    );
    //$validate set in __construct for multi-language support
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    public $belongsTo = array(
        'LendProjectCategory' => array(
            'className' => 'Lend.LendProjectCategory',
            'foreignKey' => 'lend_project_category_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'counterCache' => true,
            'counterScope' => '',
        ) ,
        'LendProjectStatus' => array(
            'className' => 'Lend.LendProjectStatus',
            'foreignKey' => 'lend_project_status_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'counterCache' => true,
            'counterScope' => '',
        ) ,
        'Project' => array(
            'className' => 'Projects.Project',
            'foreignKey' => 'project_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
        'CreditScore' => array(
            'className' => 'Lend.CreditScore',
            'foreignKey' => 'credit_score_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
        ) ,
        'LoanTerm' => array(
            'className' => 'Lend.LoanTerm',
            'foreignKey' => 'loan_term_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
        ) ,
        'RepaymentSchedule' => array(
            'className' => 'Lend.RepaymentSchedule',
            'foreignKey' => 'repayment_schedule_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
        ) ,
    );
    function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->_permanentCacheAssociations = array(
            'Project'
        );
        $this->validate = array(
            'project_funding_end_date' => array(
                'rule2' => array(
                    'rule' => array(
                        'comparison',
                        '>=',
                        date('Y-m-d') ,
                    ) ,
                    'message' => sprintf(__l('%s lending end date should be greater than to today') , Configure::read('project.alt_name_for_project_singular_caps'))
                ) ,
                'rule1' => array(
                    'rule' => 'date',
                    'message' => __l('Enter valid date')
                )
            ) ,
            'lend_project_category_id' => array(
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            ) ,
            'repayment_schedule_id' => array(
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            ) ,
            'target_interest_rate' => array(
                'rule2' => array(
                    'rule' => '_validateInterestRate',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                ) ,
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                ) ,
            ) ,
            'loan_term_id' => array(
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            ) ,
            'credit_score_id' => array(
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            ) ,
        );
    }
    function _validateInterestRate() 
    {
        if (!empty($this->data['Lend']['target_interest_rate']) && !empty($this->data['Lend']['credit_score_id']) && $this->data['Lend']['target_interest_rate'] > 0) {
            $credit_score = $this->CreditScore->find('first', array(
                'conditions' => array(
                    'id' => $this->data['Lend']['credit_score_id'],
                )
            ));
            if ($this->data['Lend']['target_interest_rate'] < $credit_score['CreditScore']['interest_rate']) {
                $this->validator()->getField('target_interest_rate')->getRule('rule2')->message = sprintf(__l('Your interest rate could not be less then %s') , $credit_score['CreditScore']['interest_rate']);
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    function minMaxAmount($field1, $field = null) 
    {
        return ($this->data[$this->name][$field] >= Configure::read('Project.minimum_amount') && $this->data[$this->name][$field] <= Configure::read('Project.maximum_amount'));
    }
    function updateProjectStatus($project_fund_id) 
    {
        $projectFund = $this->Project->ProjectFund->find('first', array(
            'conditions' => array(
                'ProjectFund.id' => $project_fund_id
            ) ,
            'contain' => array(
                'Project' => array(
                    'Lend',
                ) ,
            ) ,
            'recursive' => 2
        ));
        if ($projectFund['Project']['collected_amount'] == $projectFund['Project']['needed_amount']) {
            if ($projectFund['Project']['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending && $projectFund['Project']['Lend']['lend_project_status_id'] != ConstLendProjectStatus::ProjectAmountRepayment) {
                $this->updateStatus(ConstLendProjectStatus::ProjectAmountRepayment, $projectFund['Project']['id']);
            }
        }
    }
    function updateStatus($to_project_status_id, $project_id) 
    {
        $project = $this->Project->find('first', array(
            'conditions' => array(
                'Project.id' => $project_id,
            ) ,
            'contain' => array(
                'Lend' => array(
                    'LoanTerm'
                ) ,
                'User',
                'ProjectType',
                'Attachment',
            ) ,
            'recursive' => 2,
        ));
        $_data = array();
        $_data['Lend']['lend_project_status_id'] = $to_project_status_id;
        if ($to_project_status_id == ConstLendProjectStatus::ProjectAmountRepayment) {
            $_data['Lend']['project_fund_goal_reached_date'] = date('Y-m-d H:i:s');
        }
        if ($to_project_status_id == ConstLendProjectStatus::ProjectCanceled) {
            $_data['Project']['project_cancelled_date'] = date('Y-m-d H:i:s');
        }
        $_data['Lend']['id'] = $project['Lend']['id'];
        $this->save($_data);
        $tmp_project = $this->
        {
            'processStatus' . $to_project_status_id}($project);
            $_data = array();
            $_data['from_project_status_id'] = $project['Lend']['lend_project_status_id'];
            $_data['to_project_status_id'] = $to_project_status_id;
            if ($_data['from_project_status_id'] != $_data['to_project_status_id']) {
                $this->postActivity($project, ConstProjectActivities::StatusChange, $_data);
            }
        }
        function processStatus2($project) 
        {
            // Open For Lending //
            if (isPluginEnabled('SocialMarketing')) {
                App::import('Model', 'SocialMarketing.UserFollower');
                $this->UserFollower = new UserFollower();
                $this->UserFollower->send_follow_mail($_SESSION['Auth']['User']['id'], 'added', $project);
            }
            $data['Project']['project_start_date'] = date('Y-m-d');
            $data['Project']['id'] = $project['Project']['id'];
            $this->Project->save($data);
            $total_needed_amount = $project['User']['total_needed_amount']+$project['Project']['needed_amount'];
            $this->Project->updateAll(array(
                'User.total_needed_amount' => $total_needed_amount
            ) , array(
                'User.id' => $project['User']['id']
            ));
            $this->Project->postOnSocialNetwork($project);
            $data = array();
            $data['User']['id'] = $project['Project']['user_id'];
            $data['User']['is_idle'] = 0;
            $data['User']['is_project_posted'] = 1;
            $this->Project->User->save($data);
        }
        function processStatus3($project) 
        {
            // Project Closed //
            $this->Project->ProjectFund->updateAll(array(
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Closed,
                'ProjectFund.is_collection' => 0
            ) , array(
                'ProjectFund.project_id' => $project['Lend']['project_id']
            ));
        }
        function processStatus4($project) 
        {
            // Project Expired //
            // refund backed amount to backer
            $this->Project->_refund($project['Project']['id']);
            $this->Project->Message->updateActivitiesHideFromPublic($project['Project']['id']);
        }
        function processStatus5($project) 
        {
            // Project Canceled //
            // refund backed amount to backer
            $data['Project']['project_cancelled_date'] = date('Y-m-d H:i:s');
            $data['Project']['id'] = $project['Project']['id'];
            $this->Project->save($data);
            $this->Project->_refund($project['Project']['id'], 1);
            $this->Project->Message->updateActivitiesHideFromPublic($project['Project']['id']);
        }
        function processStatus6($project) 
        {
            // Goal Reached //
            $this->updateFundUsers($project['Project']['id']);
            $this->Project->_executepay($project['Project']['id']);
            $this->updateRepaymentDetails($project['Project']['id']);
            $this->postNotifyMail($project, ConstProjectActivities::AmountRepayment);
        }
        function processStatus8($project) 
        {
            // Open For Idea //
            $data = array();
            $data['User']['id'] = $project['Project']['user_id'];
            $data['User']['is_idle'] = 0;
            $data['User']['is_project_posted'] = 1;
            $this->Project->User->save($data);
        }
        function processStatus7() 
        {
            // Open For Idea //
            
        }
        function processStatus10($project) 
        {
            // DefaultProject //
            $this->updateFundUsers($project['Project']['id']);
        }
        function updateRepaymentDetails($projectId) 
        {
            $project = $this->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $projectId,
                ) ,
                'contain' => array(
                    'Lend' => array(
                        'RepaymentSchedule',
                        'LoanTerm',
                    ) ,
                ) ,
                'recursive' => 2,
            ));
            if (!empty($project)) {
                if (empty($project['Lend']['total_no_of_repayment'])) {
                    if (!empty($project['Lend']['RepaymentSchedule']['is_particular_day_of_month'])) {
                        $project['Lend']['total_no_of_repayment'] = $project['Lend']['LoanTerm']['months'];
                    } else {
                        $start_ts = strtotime(date('Y-m-d'));
                        $end_ts = strtotime(date('Y-m-d', strtotime('now +' . $project['Lend']['LoanTerm']['months'] . ' months')));
                        $diff = $end_ts-$start_ts;
                        $total_days = round($diff/86400);
                        $project['Lend']['total_no_of_repayment'] = ceil($total_days/$project['Lend']['RepaymentSchedule']['day']);
                    }
                }
                if (!empty($project['Lend']['RepaymentSchedule']['is_particular_day_of_month'])) {
                    $project['Lend']['next_repayment_date'] = date('Y-m-' . $project['Lend']['RepaymentSchedule']['day'], strtotime('now +1 months'));
                } else {
                    $project['Lend']['next_repayment_date'] = date('Y-m-d', strtotime('now +' . $project['Lend']['RepaymentSchedule']['day'] . ' days'));
                }
                $check_rate_result = $this->check_rate_calculate($project['Project']['needed_amount'], $project['Lend']['target_interest_rate'], $project['Lend']['total_no_of_repayment']);
                $project['Lend']['next_repayment_amount'] = $check_rate_result['per_month'];
                $this->Project->Lend->save($project);
                $this->Project->ProjectFund->updateAll(array(
                    'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
                    'ProjectFund.is_collection' => 0
                ) , array(
                    'ProjectFund.project_id' => $project['Lend']['project_id']
                ));
            }
        }
        function check_rate_calculate($amount, $rate, $term) 
        {
            $result['periodic_rate'] = $rate/12/100;
            $result['per_month'] = round($amount*($result['periodic_rate']/(1-pow((1/(1+$result['periodic_rate'])) , $term))) , 2);
            $result['total_amount'] = round($result['per_month']*$term, 2);
            $result['total_interest'] = round($result['total_amount']-$amount, 2);
            $result['total_principal'] = round($result['total_amount']-$result['total_interest'], 2);
            $result['this_month_total_amount'] = round(($result['total_principal']/$term) , 2);
            $result['this_month_total_interest'] = round(($result['total_interest']/$term) , 2);
            return $result;
        }
        function onRepaymentAdd($project_id) 
        {
            $project = $this->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $project_id,
                ) ,
                'contain' => array(
                    'Lend' => array(
                        'LoanTerm',
                        'RepaymentSchedule',
                    ) ,
                    'User',
                    'ProjectType',
                ) ,
                'recursive' => 2
            ));
            $check_rate_result = $this->check_rate_calculate($project['Project']['needed_amount'], $project['Lend']['target_interest_rate'], $project['Lend']['total_no_of_repayment']);
            $this_month_principal = $check_rate_result['this_month_total_amount'];
            $this_month_interest = $check_rate_result['this_month_total_interest'];
            $update_lend_data['Lend']['id'] = $project['Lend']['id'];
            $update_lend_data['Lend']['is_repayment_notified'] = 0;
            $update_lend_data['Lend']['is_collection'] = 0;
            if ($project['Lend']['repayment_count'] != $project['Lend']['total_no_of_repayment']) {
                if ($project['Lend']['RepaymentSchedule']['is_particular_day_of_month']) {
                    $update_lend_data['Lend']['next_repayment_date'] = date('Y-m-d', strtotime($project['Lend']['next_repayment_date'] . '+ 1 months'));
                } else {
                    $update_lend_data['Lend']['next_repayment_date'] = date('Y-m-d', strtotime($project['Lend']['next_repayment_date'] . '+ ' . $project['Lend']['RepaymentSchedule']['day'] . ' days'));
                }
            }
            $update_lend_data['Lend']['repayment_count'] = $project['Lend']['repayment_count']+1;
            $update_lend_data['Lend']['repayment_amount'] = $project['Lend']['repayment_amount']+$this_month_principal;
            $update_lend_data['Lend']['repayment_percentage'] = round(($update_lend_data['Lend']['repayment_amount']/$project['Project']['needed_amount']) *100);
            $update_lend_data['Lend']['repayment_interest_amount'] = $project['Lend']['repayment_interest_amount']+$check_rate_result['this_month_total_interest'];
            $this->save($update_lend_data);
            $update_project = array();
            $update_project['Project']['id'] = $project['Project']['id'];
            $update_project['Project']['is_pending_action_to_admin'] = 0;
            $this->Project->save($update_project);
        }
        function updateFundUsers($project_id) 
        {
            $projectFundUsers = $this->Project->ProjectFund->find('list', array(
                'conditions' => array(
                    'ProjectFund.project_id' => $project_id
                ) ,
                'fields' => array(
                    'ProjectFund.id',
                    'ProjectFund.user_id'
                ) ,
                'group' => array(
                    'ProjectFund.user_id'
                ) ,
                'recursive' => -1,
            ));
            foreach($projectFundUsers as $user_id) {
                $this->updateUserDetails($user_id);
            }
        }
        function updateUserDetails($userId) 
        {
            $status_ids = array(
                'withdrawn' => array(
                    'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
                    'ProjectFund.is_collection' => 0
                ) ,
                'collection' => array(
                    'ProjectFund.is_collection' => 1
                ) ,
                'default' => array(
                    'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::DefaultFund,
                    'ProjectFund.is_collection' => 0
                ) ,
                'closed' => array(
                    'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Closed,
                    'ProjectFund.is_collection' => 0
                )
            );
            $conditions = array(
                'ProjectFund.user_id' => $userId,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend,
            );
            $fund_ids = array();
            $data = array();
            foreach($status_ids as $key => $value) {
                $new_conditions = array_merge($conditions, $value);
                $fund_ids[$key] = $this->Project->ProjectFund->find('list', array(
                    'conditions' => $new_conditions,
                    'fields' => array(
                        'ProjectFund.id',
                        'ProjectFund.id'
                    ) ,
                    'recursive' => -1,
                ));
                $projectFundIds = $this->Project->ProjectFund->find('list', array(
                    'conditions' => $new_conditions,
                    'fields' => array(
                        'ProjectFund.project_id'
                    ) ,
                    'recursive' => -1
                ));
                $projectLendFields = $this->Project->Lend->find('first', array(
                    'conditions' => array(
                        'Lend.project_id' => $projectFundIds
                    ) ,
                    'fields' => array(
                        'AVG(Lend.target_interest_rate) as total_avg',
                    ) ,
                    'recursive' => -1
                ));
                $projectFundDetails = $this->Project->ProjectFund->find('all', array(
                    'conditions' => $new_conditions,
                    'fields' => array(
                        'ProjectFund.project_fund_status_id',
                        'COUNT(*) as no_of_loans',
                        'SUM(ProjectFund.amount) as total_lent',
                    ) ,
                    'contain' => array(
                        'LendFund' => array(
                            'fields' => array(
                                'AVG(LendFund.interest_rate) as total_avg',
                            ) ,
                        ) ,
                    ) ,
                    'group' => 'ProjectFund.project_fund_status_id',
                    'recursive' => 1,
                ));
                $projectRepaymentDetails = $this->Project->ProjectFund->ProjectFundRepayment->find('all', array(
                    'conditions' => array(
                        'ProjectFundRepayment.project_fund_id' => $fund_ids[$key]
                    ) ,
                    'fields' => array(
                        'SUM(ProjectFundRepayment.amount) as total_repay_amount',
                        'SUM(ProjectFundRepayment.interest) as total_repay_interest',
                    ) ,
                    'recursive' => -1,
                ));
                $update_field = array(
                    '_no_of_loans',
                    '_average_rate',
                    '_total_lent',
                    '_total_capital_returned',
                    '_total_interest_returned'
                );
                $data['User.' . $key . $update_field[0]] = (!empty($projectFundDetails[0][0]['no_of_loans'])) ? $projectFundDetails[0][0]['no_of_loans'] : 0;
                $data['User.' . $key . $update_field[1]] = (!empty($projectLendFields[0]['total_avg'])) ? $projectLendFields[0]['total_avg'] : 0;
                $data['User.' . $key . $update_field[2]] = (!empty($projectFundDetails[0][0]['total_lent'])) ? $projectFundDetails[0][0]['total_lent'] : 0;
                $data['User.' . $key . $update_field[3]] = (!empty($projectRepaymentDetails[0][0]['total_repay_amount'])) ? $projectRepaymentDetails[0][0]['total_repay_amount'] : 0;
                $data['User.' . $key . $update_field[4]] = (!empty($projectRepaymentDetails[0][0]['total_repay_interest'])) ? $projectRepaymentDetails[0][0]['total_repay_interest'] : 0;
            }
            $this->Project->ProjectFund->User->updateAll($data, array(
                'User.id' => $userId
            ));
        }
		public function deductFromCollectedAmount($project) 
		{
			$projectTypeName = ucwords($project['ProjectType']['name']);
			$lends = $this->find('all', array(
				'conditions' => array(
					'Lend.project_id' => $project['Project']['id'],
				) ,
				'contain' => array(
					'LendProjectStatus'
				) ,
				'recursive' => 0
			));
			$projectDetails = array();
			foreach($lends as $key => $lend) {
				$projectDetails[$lend['Lend']['project_id']] = $lend['LendProjectStatus'];
			}
			if (in_array($projectDetails[$project['Project']['id']]['id'], array(
						ConstLendProjectStatus::ProjectCanceled,
						ConstLendProjectStatus::ProjectExpired
					))) {
				return false;
			} else {
				return true;
			}
			
		}
		public function getCategoryConditions($category = null, $is_slug = true)  
		{
			if(!empty($is_slug)) {
				App::import('Model', 'Lend.LendProjectCategory');
				$this->LendProjectCategory = new LendProjectCategory();
				$category = $this->LendProjectCategory->find('first', array(
					'conditions' => array(
						'LendProjectCategory.slug' => $category
					) ,
					'recursive' => -1
				));
				$response['category_details'] = $category['LendProjectCategory'];
				$response['conditions'] = array(
					'Lend.lend_project_category_id' => $category['LendProjectCategory']['id']
				);
			} else {
				$response['conditions'] = array(
					'Lend.lend_project_category_id' => $category
				);
			}
			return $response;
		}
		public function onProjectCategories($is_slug = false)  
		{
			$fields = array(
				'LendProjectCategory.slug',
				'LendProjectCategory.name'
			);
			if(!$is_slug) {
				$fields = array(
					'LendProjectCategory.id',
					'LendProjectCategory.name'
				);
			}	
			$lendProjectCategory = $this->LendProjectCategory->find('list', array(
				'conditions' => array(
					'LendProjectCategory.is_approved' => 1
				) ,
				'fields' => $fields,
				'order' => array(
					'LendProjectCategory.name' => 'ASC'
				) ,
			));
			$response['lendCategories'] = $lendProjectCategory;
			return $response;
		}
		public function isAllowToPublish($project_id) 
		{
			$project = $this->find('count', array(
				'conditions' => array(
					'Lend.project_id' => $project_id,
					'Lend.lend_project_status_id' => array(
						ConstLendProjectStatus::OpenForIdea,
						ConstLendProjectStatus::ProjectAmountRepayment,
						ConstLendProjectStatus::OpenForLending
					)
				)
			));
			$response['is_allow_to_publish'] = 1;
			return $response;
		}
		public function isAllowToProcessPayment($project_id) 
		{
			$project = $this->find('count', array(
				'conditions' => array(
					'Lend.project_id' => $project_id,
					'Lend.lend_project_status_id' => ConstLendProjectStatus::Pending,
					'Project.is_paid' => 0,
				) ,
				'recursive' => 0
			));
			if (!empty($project)) {
				$response['is_allow_process_payment'] = 1;
				return $response;
			}
		}
		public function isAllowToViewProject($project, $funded_users, $followed_user) 
		{
			$response['is_allow_to_view_project'] = 1;
			if ((in_array($project['Lend']['lend_project_status_id'], array(
				ConstLendProjectStatus::Pending,
				ConstLendProjectStatus::ProjectExpired,
				ConstLendProjectStatus::ProjectCanceled
			))) && (!$funded_users) && (!$followed_user) && (!$_SESSION['Auth']['User']['id'] || ($_SESSION['Auth']['User']['id'] && $_SESSION['Auth']['User']['id'] != $project['Project']['user_id'] && (!$funded_users) && $_SESSION['Auth']['User']['role_id'] != ConstUserTypes::Admin))) {
				$response['is_allow_to_view_project'] = 0;
			}
			return $response;
		}
		public function onProjectViewMessageDisplay($project) 
		{
			$lend = $this->find('first', array(
				'conditions' => array(
					'Lend.lend_project_status_id' => array(
						ConstLendProjectStatus::OpenForIdea,
						ConstLendProjectStatus::OpenForLending,
						ConstLendProjectStatus::ProjectAmountRepayment,
						ConstLendProjectStatus::ProjectClosed,
					) ,
					'Lend.project_id' => $project['Project']['id']
				) ,
				'fields' => array(
					'Lend.project_id'
				)
			));
			$response['is_comment_allow'] = 0;
			if (!empty($lend)) {
				$response['is_comment_allow'] = 1;
			}
			return $response;
		}
		public function getUserOpenProjectCount($user_id){
			$lend_count = $this->Project->find('count',array(
					'conditions' => array(
							'Lend.lend_project_status_id' => ConstLendProjectStatus::OpenForLending,
							'Project.user_id' => $user_id ,
					) ,
					'contain' => array(
							'Lend'
					) ,
					'recursive' => 0
			));
			return $lend_count;
		}
    }
?>
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
class LendsController extends AppController
{
    public $name = 'Lends';
    public function beforeFilter() 
    {
        $this->Security->disabledFields = array(
            'Project.id',
            'Lend.lend_project_category_id',
            'Project.needed_amount',
            'Lend.credit_score_id',
            'Lend.search',
            'Lend.wallet',
            'Lend.payment_gateway_id',
            'Lend.is_agree_terms_conditions',
            'Project.CheckRate',
        );
        parent::beforeFilter();
	
	if($this->RequestHandler->prefers('json') && ($this->request->params['action'] == 'check_rate')) {
	    $this->Security->validatePost = false;
	}
    }
    public function index() 
    {
        if (!empty($this->request->data)) {
            $conditions = array();
            $conditions['Project.project_type_id'] = ConstProjectTypes::Lend;
            $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForLending;
            $conditions['Project.is_active'] = 1;
            $conditions['Project.user_id != '] = $this->Auth->user('id');
            if ($this->request->data['Lend']['lend_project_category_id']) {
                $conditions['OR']['Lend.lend_project_category_id'] = $this->request->data['Lend']['lend_project_category_id'];
            }
            if ($this->request->data['Lend']['loan_term_id']) {
                $conditions['OR']['Lend.loan_term_id'] = $this->request->data['Lend']['loan_term_id'];
            }
            if ($this->request->data['Lend']['credit_score_id']) {
                $conditions['OR']['Lend.credit_score_id'] = $this->request->data['Lend']['credit_score_id'];
            }
            $contain = array(
                'Project' => array(
                    'ProjectType',
                    'User' => array(
                        'UserAvatar'
                    ) ,
                    'Attachment',
                ) ,
                'LendProjectStatus',
                'LendProjectCategory',
                'LoanTerm',
                'CreditScore',
            );
            $this->paginate = array(
                'conditions' => $conditions,
                'contain' => $contain,
                'recursive' => 3,
                'limit' => 50,
            );
            $lends = $this->paginate();
            $this->set('lends', $lends);
			if(!empty($this->request->data['Lend']['wallet'])){
				 if (!empty($this->request->data['Project'])) {
					$is_checked_project = false;
					$lend_projects = array();
					$i = 0;
					foreach($this->request->data['Project'] as $project_id => $is_checked) {
						if ($is_checked['id']) {
							$is_checked_project = true;
							$lend_projects[$i]['project_id'] = $project_id;
							$lend_projects[$i]['amount'] = $is_checked['amount'];
							$i++;
						}
					}
					if ($is_checked_project && !empty($lend_projects)) {
						foreach($lend_projects as $project_id => $is_checked)
						{
							if (empty($is_checked['amount']) || $is_checked['amount']=='0')
							{
								$this->Session->setFlash(__l('Please Enter Lend Amount for Selected Project') , 'default', null, 'error');
							}
							else
							{
							$this->add($lend_projects, $this->request->data['Lend']['lend_name'], $this->request->data['Lend']['payment_gateway_id'], $this->request->data['Lend']['is_anonymous']);
							}
						}
					} else {
						if (!$is_checked_project) {
							$this->Session->setFlash(__l('Select atleast one project and lend project') , 'default', null, 'error');
						}
					}
				} else {
					if (!empty($lends)) {
						$total_amount = $this->request->data['Lend']['lend_amount'];
						$split_amount = $total_amount/count($lends);
						$fill_amount = array();
						$exces_need_amount = array();
						foreach($lends as $key => $lend) {
							$amount = $split_amount;
							if (($lend['Project']['needed_amount']-$lend['Project']['collected_amount']) < $amount) {
								$amount = ($lend['Project']['needed_amount']-$lend['Project']['collected_amount']);
							}
							if (($lend['Project']['needed_amount']-$lend['Project']['collected_amount']) > $amount) {
								$exces_need_amount[$key] = ($lend['Project']['needed_amount']-$lend['Project']['collected_amount']) -$amount;
							}
							$fill_amount[$lend['Project']['id']] = $amount;
							if ($total_amount >= $amount) {
								$total_amount-= $amount;
								if ($total_amount == 0) break;
							} else {
								break;
							}
						}
						if ($total_amount > 0) {
							foreach($exces_need_amount as $key => $need_amount) {
								if ($need_amount <= $total_amount) {
									$fill_amount[$lends[$key]['Project']['id']] = $fill_amount[$lends[$key]['Project']['id']]+$need_amount;
									$total_amount-= $need_amount;
								} else {
									$fill_amount[$lends[$key]['Project']['id']] = $fill_amount[$lends[$key]['Project']['id']]+$total_amount;
									$total_amount = 0;
								}
								if ($total_amount <= 0) {
									break;
								}
							}
						}
						foreach($fill_amount as $key => $value) {
							$this->request->data['Project'][$key]['amount'] = $value;
						}
					}
           		}
			}
        }
        $lendProjectCategories = $this->Lend->LendProjectCategory->find('list', array(
            'conditions' => array(
                'LendProjectCategory.is_approved' => 1
            ) ,
        ));
        $loanTerms = $this->Lend->LoanTerm->find('list', array(
            'conditions' => array(
                'LoanTerm.is_approved' => 1
            ) ,
        ));
        $creditScores = $this->Lend->CreditScore->find('list', array(
            'conditions' => array(
                'CreditScore.is_approved' => 1
            ) ,
        ));
        $radio_options = array(
            ConstAnonymous::None => __l('Visible') ,
            ConstAnonymous::Username => __l('Show your amount, but hide the name') ,
            ConstAnonymous::FundedAmount => __l('Show your name, but hide the amount') ,
            ConstAnonymous::Both => __l('Anonymous')
        );
        $this->set('heading', __l('Bulk Lending'));
        $this->set(compact('lendProjectCategories', 'loanTerms', 'creditScores', 'radio_options'));
        unset($this->Lend->validate['lend_project_category_id']);
        unset($this->Lend->validate['loan_term_id']);
        unset($this->Lend->validate['credit_score_id']);
    }
    public function add($lend_projects = array() , $lend_name, $payment_gateway_id, $is_anonymous = 0) 
    {
        if (!empty($lend_projects)) {
            $lend_name_data = array();
            $lend_name_data['LendName']['name'] = $lend_name;
            $total_lending_amount = 0;
            $total_interest_rate = 0;
            foreach($lend_projects As $lend_project) {
                $total_lending_amount = $total_lending_amount+$lend_project['amount'];
                $lend = $this->Lend->find('first', array(
                    'conditions' => array(
                        'Lend.project_id' => $lend_project['project_id'],
                    ) ,
                    'recursive' => -1
                ));
                $total_interest_rate = $lend['Lend']['target_interest_rate'];
            }
			$this->loadModel('Lend.LendName');
            $lend_name_data['LendName']['amount'] = $total_lending_amount;
            $lend_name_data['LendName']['user_id'] = $this->Auth->user('id');
            $lend_name_data['LendName']['average_rate'] = $total_interest_rate/count($lend_projects);
            $this->LendName->save($lend_name_data);
            $lend_name_id = $this->LendName->id;
            $lend_name_data['LendName']['id'] = $lend_name_id;
            foreach($lend_projects As $lend_project) {
                $project = $this->Lend->Project->find('first', array(
                    'conditions' => array(
                        'Project.id' => $lend_project['project_id'],
                    ) ,
                    'contain' => array(
                        'Lend',
                        'User',
                        'ProjectType',
                    ) ,
                    'recursive' => 1
                ));
                $fund_data = array();
                $fund_commission_percentage = (is_null($project['ProjectType']['commission_percentage'])) ? Configure::read('Project.fund_commission_percentage') : $project['ProjectType']['commission_percentage'];
                $fund_data['ProjectFund']['user_id'] = $this->Auth->user('id');
                $fund_data['ProjectFund']['amount'] = $lend_project['amount'];
                $fund_data['ProjectFund']['site_fee'] = round($lend_project['amount']*($fund_commission_percentage/100) , 2);
                $fund_data['ProjectFund']['is_delayed_chained_payment'] = 0;
                $fund_data['ProjectFund']['project_type_id'] = $project['Project']['project_type_id'];
                $fund_data['ProjectFund']['owner_user_id'] = $project['Project']['user_id'];
                $fund_data['ProjectFund']['payment_gateway_id'] = $payment_gateway_id;
                $fund_data['ProjectFund']['project_fund_status_id'] = ConstProjectFundStatus::PendingToPay;
                $fund_data['ProjectFund']['project_reward_id'] = 0;
                $fund_data['ProjectFund']['project_widget_id'] = 0;
                $fund_data['ProjectFund']['is_agree_terms_conditions'] = 1;
                $fund_data['ProjectFund']['project_id'] = $project['Project']['id'];
                $fund_data['ProjectFund']['lend_name_id'] = $lend_name_id;
                $fund_data['ProjectFund']['is_anonymous'] = $is_anonymous;
                $this->Lend->Project->ProjectFund->create();
                $this->Lend->Project->ProjectFund->save($fund_data);
                $fund_data['ProjectFund']['item_id'] = $this->Lend->Project->ProjectFund->id;
                $fund_data['ProjectFund']['id'] = $this->Lend->Project->ProjectFund->id;
                $response = Cms::dispatchEvent('Controller.ProjectFunds.afterAdd', $this, array(
                    'data' => $fund_data
                ));
                if ($fund_data['ProjectFund']['payment_gateway_id'] == ConstPaymentGateways::Wallet and isPluginEnabled('Wallet')) {
                    $this->loadModel('Wallet.Wallet');
                    $return = $this->Wallet->processOrder($this->Auth->user('id') , $fund_data['ProjectFund']);
                    if (!$return) {
                        $this->Session->setFlash(__l('Your wallet has insufficient money') , 'default', null, 'error');
                        $this->redirect(array(
                            'controller' => 'lends',
                            'action' => 'index',
                        ));
                    }
                }
            }
            $this->Session->setFlash(__l('You have lent successfully') , 'default', null, 'success');
            $this->redirect(array(
                'controller' => 'lends',
                'action' => 'index',
            ));
        }
    }
    public function check_rate() 
    {
	    $response = $this->Lend->onProjectCategories();
        $lendCategories = $response['lendCategories'];
        $creditScores = $this->Lend->CreditScore->find('list', array(
            'conditions' => array(
                'CreditScore.is_approved' => 1
            ) ,
        ));
	if ($this->RequestHandler->prefers('json') && ($this->request->is('post')))
	{
	    $this->request->data['Project']['needed_amount'] = $this->request->data['needed_amount'];
	    $this->request->data['Lend']['lend_project_category_id'] = $this->request->data['lend_project_category_id'];
	    $this->request->data['Lend']['credit_score_id'] = $this->request->data['credit_score_id'];
	}
        if (!empty($this->request->data) && ($this->request->is('post'))) {
            $credit_score = $this->Lend->CreditScore->find('first', array(
                'conditions' => array(
                    'id' => $this->request->data['Lend']['credit_score_id'],
                )
            ));
            $rate = $credit_score['CreditScore']['interest_rate'];
            $terms = Configure::read('lend.default_terms');
            $rate_calculation_result = $this->Lend->check_rate_calculate($this->request->data['Project']['needed_amount'], $rate, $terms);
            $_SESSION['lendDetails']['lend_needed_amount'] = $this->request->data['Project']['needed_amount'];
            $_SESSION['lendDetails']['lend_interest_rate'] = $rate;
            $_SESSION['lendDetails']['lend_category_id'] = $this->request->data['Lend']['lend_project_category_id'];
            $_SESSION['lendDetails']['lend_credit_score_id'] = $this->request->data['Lend']['credit_score_id'];
            $_SESSION['lendDetails']['lend_per_month'] = $rate_calculation_result['per_month'];
	    if(!$this->RequestHandler->prefers('json')) {
		$this->redirect(array(
		    'controller' => 'projects',
		    'action' => 'add',
		    'project_type' => 'lend'
		));
	    } else {
		$this->set('iphone_response', array("message" => __l('Success'), "lend_interest_rate" => $rate, "lend_per_month" => $rate_calculation_result['per_month'], "lend_terms" => $terms, "error" => 0));
	    }
        }
        $this->pageTitle = Configure::read('project.alt_name_for_lend_singular_caps') . ' ' . ' - ' . __l('Check Rate');
        $this->set(compact('lendCategories', 'creditScores', 'rate_calculation_result', 'rate'));
	
	$form_data = array();
	foreach($lendCategories as $k=>$val){
	   $form_data['LendCategory']['id'][] =$k;
	   $form_data['LendCategory']['name'][] =$val;
	}
	foreach($creditScores as $k=>$val){
	   $form_data['CreditScore']['id'][] =$k;
	   $form_data['CreditScore']['name'][] =$val;
	}
	if ($this->RequestHandler->prefers('json')) {
            $response = Cms::dispatchEvent('Controller.Lend.CheckRate', $this, array('form_data'=>$form_data));
        }

    }
    public function overview() 
    {
        $user_id = $this->Auth->user('id');
        if (!empty($user_id)) {
            $periods = array(
                'day' => array(
                    'display' => __l('Today') ,
                    'conditions' => array(
                        'Project.created =' => date('Y-m-d', strtotime('now')) ,
                    )
                ) ,
                'week' => array(
                    'display' => __l('This Week') ,
                    'conditions' => array(
                        'Project.created =' => date('Y-m-d', strtotime('now -7 days')) ,
                    )
                ) ,
                'month' => array(
                    'display' => __l('This Month') ,
                    'conditions' => array(
                        'Project.created =' => date('Y-m-d', strtotime('now -30 days')) ,
                    )
                ) ,
                'total' => array(
                    'display' => __l('Total') ,
                    'conditions' => array()
                )
            );
            $models[] = array(
                'Transaction' => array(
                    'display' => __l('Cleared') ,
                    'projectconditions' => array(
                        'Project.user_id' => $user_id,
                        'Lend.lend_project_status_id' => array(
                            ConstLendProjectStatus::ProjectClosed,
                            ConstLendProjectStatus::ProjectAmountRepayment,
                        )
                    ) ,
                    'alias' => 'Cleared',
                    'type' => 'cInt',
                    'isSub' => 'Project',
                    'class' => 'highlight-cleared'
                )
            );
            $models[] = array(
                'Transaction' => array(
                    'display' => __l('Pipeline') ,
                    'projectconditions' => array(
                        'Project.user_id' => $user_id,
                        'Lend.lend_project_status_id' => array(
                            ConstLendProjectStatus::Pending,
                            ConstLendProjectStatus::OpenForLending,
                            ConstLendProjectStatus::OpenForIdea,
                        )
                    ) ,
                    'alias' => 'Pipeline',
                    'type' => 'cInt',
                    'isSub' => 'Projects',
                    'class' => 'highlight-pipeline'
                )
            );
            $models[] = array(
                'Transaction' => array(
                    'display' => __l('Lost') ,
                    'projectconditions' => array(
                        'Project.user_id' => $user_id,
                        'Lend.lend_project_status_id' => array(
                            ConstLendProjectStatus::ProjectExpired,
                            ConstLendProjectStatus::ProjectCanceled
                        )
                    ) ,
                    'alias' => 'Lost',
                    'type' => 'cInt',
                    'isSub' => 'PropertyUsers',
                    'class' => 'highlight-lost'
                )
            );
            foreach($models as $unique_model) {
                foreach($unique_model as $model => $fields) {
                    foreach($periods as $key => $period) {
                        if ($fields['alias'] == 'Cleared') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::ProjectBacked
                            ));
                        } elseif ($fields['alias'] == 'Pipeline') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::ProjectBacked
                            ));
                        } elseif ($fields['alias'] == 'PipelineReverse') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::Refunded
                            ));
                        } elseif ($fields['alias'] == 'Lost') {
                            $period['conditions'] = array_merge($period['conditions'], array(
                                'Transaction.transaction_type_id' => ConstTransactionTypes::Refunded
                            ));
                        }
                        $conditions = $period['conditions'];
                        if (!empty($fields['conditions'])) {
                            $conditions = array_merge($periods[$key]['conditions'], $fields['conditions']);
                        }
                        $projectConditions = array(
                            'Project.user_id' => $this->Auth->user('id')
                        );
                        if (!empty($fields['projectconditions'])) {
                            $projectConditions = $fields['projectconditions'];
                        }
                        $project_list = $this->Lend->Project->find('list', array(
                            'conditions' => $projectConditions,
                            'fields' => array(
                                'Project.id',
                            ) ,
                            'recursive' => 1
                        ));
                        $conditions['ProjectFund.project_id'] = $project_list;
                        $conditions['Transaction.class'] = 'ProjectFund';
                        $aliasName = !empty($fields['alias']) ? $fields['alias'] : $model;
                        $result = $this->Lend->Project->Transaction->find('first', array(
                            'fields' => array(
                                'SUM(Transaction.amount) as amount',
                            ) ,
                            'conditions' => $conditions,
                            'recursive' => 1
                        ));
                        $this->set($aliasName . $key, $result[0]['amount']);
                    }
                }
            }
        }
        $this->set(compact('periods', 'models'));
    }
    public function myprojects() 
    {
        $conditions['Project.project_type_id'] = ConstProjectTypes::Lend;
        $conditions['Project.user_id'] = $this->Auth->user('id');
        $order = array(
            'Project.project_end_date' => 'asc'
        );
        if (!$this->Auth->user('id')) {
            if ($this->RequestHandler->prefers('json')){
		$this->set('iphone_response', array("message" =>__l('Invalid request') , "error" => 1));
	    }else{
		throw new NotFoundException(__l('Invalid request'));
	    }
        }
        if (!empty($this->request->params['named']['status'])) {
            if ($this->request->params['named']['status'] == 'pending') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::Pending;
            } elseif ($this->request->params['named']['status'] == 'idea') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForIdea;
            } elseif ($this->request->params['named']['status'] == 'cancelled') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::ProjectCanceled;
                unset($conditions['Project.project_end_date >= ']);
            } elseif ($this->request->params['named']['status'] == 'expired') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::ProjectExpired;
                unset($conditions['Project.project_end_date >= ']);
            } elseif ($this->request->params['named']['status'] == 'closed') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::ProjectClosed;
            } elseif ($this->request->params['named']['status'] == 'goal') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::ProjectAmountRepayment;
            } elseif ($this->request->params['named']['status'] == 'draft') {
                $conditions['Project.is_draft'] = 1;
            } elseif ($this->request->params['named']['status'] == 'open_for_funding') {
                $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForLending;
            } elseif ($this->request->params['named']['status'] == 'flexible') {
                $conditions['Project.payment_method_id'] = ConstPaymentMethod::KiA;
            } elseif ($this->request->params['named']['status'] == 'fixed') {
                $conditions['Project.payment_method_id'] = ConstPaymentMethod::AoN;
            }
        }
	//Todo: Need to change for default status 
	/*else {
            $conditions['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForLending;
        }*/
        $heading = sprintf(__l('My %s') , Configure::read('project.alt_name_for_project_plural_caps'));
        $contain = array(
            'Project' => array(
                'ProjectType',
                'User' => array(
                    'UserAvatar'
                ) ,
                'Message' => array(
                    'conditions' => array(
                        'Message.is_activity' => 0,
                        'Message.is_sender' => 0
                    ) ,
                ) ,
                'Attachment',
                'Transaction',
                'ProjectRepayment'
            ) ,
            'LendProjectStatus',
            'LoanTerm',
        );
        if (isPluginEnabled('Idea')) {
            $contain['Project']['ProjectRating'] = array(
                'conditions' => array(
                    'ProjectRating.user_id' => $this->Auth->user('id') ,
                )
            );
        }
        if (!isPluginEnabled('Idea')) {
            $conditions['Lend.lend_project_status_id !='] = ConstLendProjectStatus::OpenForIdea;
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => $order,
            'recursive' => 3,
            'limit' => 20,
        );
        $projects = $this->paginate();
        $this->set('projects', $projects);
	
	if ($this->RequestHandler->prefers('json') && !empty($this->request->query['key'])) {
	    $event_data['contain'] = $contain;
	    $event_data['conditions'] = $conditions;
	    $event_data['order'] = $order;
	    $event_data['limit'] = 20;
	    $event_data['model'] = "Lend";
	    $event_data = Cms::dispatchEvent('Controller.Lend.myprojects', $this, array(
		'data' => $event_data
	    ));
	}

        $lendStatuses = $this->Lend->LendProjectStatus->find('list', array(
            'recursive' => -1
        ));
        $projectStatuses = array();
        foreach($lendStatuses as $key => $status) {
            $status_condition = array(
                'Lend.lend_project_status_id ' => $key,
                'Project.user_id' => $this->Auth->user('id')
            );
            if ($key != ConstLendProjectStatus::ProjectCanceled) {
                $status_condition['Project.is_active'] = 1;
            }
            $project_status = $this->Lend->Project->find('list', array(
                'conditions' => $status_condition,
                'contain' => array(
                    'Lend'
                ) ,
                'recursive' => 0
            ));
            $project_status = $this->Lend->Project->find('count', array(
                'conditions' => $status_condition,
                'contain' => array(
                    'Lend'
                ) ,
                'recursive' => 0
            ));
            $projectStatuses[$key] = $project_status;
        }
        $this->set('system_drafted', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_draft = ' => 1,
                'Project.user_id' => $this->Auth->user('id') ,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('projectStatuses', $projectStatuses);
        $count = $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_active' => 1,
                'Project.user_id' => $this->Auth->user('id') ,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        ));
        $this->set('total_flexible_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.payment_method_id' => ConstPaymentMethod::KiA,
                'Project.project_type_id' => ConstProjectTypes::Lend,
                'Project.user_id' => $this->Auth->user('id')
            ) ,
            'recursive' => -1
        )));
        $this->set('total_fixed_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.payment_method_id' => ConstPaymentMethod::AoN,
                'Project.project_type_id' => ConstProjectTypes::Lend,
                'Project.user_id' => $this->Auth->user('id')
            ) ,
            'recursive' => -1
        )));
        $this->set('count', $count);
        if (!empty($this->request->params['named']['from'])) {
            $this->render('project_filter');
        }
        $countDetail = $this->Lend->Project->getAdminRejectApproveCount(ConstProjectTypes::Lend, ConstLendProjectStatus::Pending, 'Lend', 'Lend.lend_project_status_id');
        $this->set('formFieldSteps', $countDetail['formFieldSteps']);
        $this->set('rejectedCount', $countDetail['rejectedCount']);
        $this->set('approvedCount', $countDetail['approvedCount']);
        $this->set('rejectedProjectIds', $countDetail['rejectedProjectIds']);
        $this->set('approvedProjectIds', $countDetail['approvedProjectIds']);
    }
    public function myfunds() 
    {
        $conditions = array();
        $this->loadModel("Projects.ProjectFund");
        $conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Lend;
        $conditions['ProjectFund.user_id'] = $this->Auth->user('id');
        if (!empty($this->request->params['named']['lend_name_id'])) {
            $conditions['ProjectFund.lend_name_id'] = $this->request->params['named']['lend_name_id'];
        }
        if (isset($this->request->params['named']['status'])) {
            if ($this->request->params['named']['status'] == 'refunded') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Expired;
            } else if ($this->request->params['named']['status'] == 'paid') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
            } else if ($this->request->params['named']['status'] == 'cancelled') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Canceled;
            }
        }
        $this->set('fund_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id' => $this->Auth->user('id') ,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('refunded_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id = ' => $this->Auth->user('id') ,
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Expired,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('paid_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id = ' => $this->Auth->user('id') ,
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('cancelled_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id = ' => $this->Auth->user('id') ,
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Canceled,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $contain = array(
            'User' => array(
                'UserAvatar'
            ) ,
            'Project' => array(
                'User' => array(
                    'fields' => array(
                        'User.username',
                        'User.id'
                    )
                ) ,
                'Lend' => array(
                    'LendProjectStatus',
                    'CreditScore',
                    'LoanTerm'
                ) ,
                'Attachment',
            )
        );
        $paging_array = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'recursive' => 3,
            'order' => array(
                'ProjectFund.id' => 'desc'
            )
        );
        $limit = 20;
        if (!empty($limit)) {
            $paging_array['limit'] = $limit;
        }
        $this->paginate = $paging_array;
        $this->set('projectFunds', $this->paginate('ProjectFund'));
        $this->set('all_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.user_id' => $this->Auth->user('id') ,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            )
        )));
        $conditions['ProjectFund.is_given'] = 1;
        $conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Lend;
        $this->set('given_count', $this->ProjectFund->find('count', array(
            'conditions' => $conditions
        )));
        if (!empty($this->request->params['named']['from'])) {
            $this->render('lend_filter');
        }
    }
    function admin_index() 
    {
        $this->_redirectGET2Named(array(
            'filter_id',
            'project_category_id',
            'q'
        ));
        if (!empty($this->request->data['Project']['q'])) {
            $this->request->params['named']['q'] = $this->request->data['Project']['q'];
        }
        App::import('Model', 'Projects.FormFieldStep');
        $FormFieldStep = new FormFieldStep();
        $formFieldSteps = $FormFieldStep->find('list', array(
            'conditions' => array(
                'FormFieldStep.project_type_id' => ConstProjectTypes::Lend,
                'FormFieldStep.is_splash' => 1
            ) ,
            'fields' => array(
                'FormFieldStep.order',
                'FormFieldStep.name'
            ) ,
            'recursive' => -1
        ));
        $this->set('formFieldSteps', $formFieldSteps);
        $this->pageTitle = Configure::read('project.alt_name_for_lend_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps');
        $conditions = array();
        $conditions['Project.project_type_id'] = ConstProjectTypes::Lend;
        // check the filer passed through named parameter
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['Project']['filter_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['Project']['filter_id'])) {
            if ($this->request->data['Project']['filter_id'] == ConstMoreAction::Suspend) {
                $conditions['Project.is_admin_suspended'] = 1;
                $this->pageTitle.= ' - ' . __l('Suspended');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Active) {
                $conditions['Project.is_active'] = 1;
                $this->pageTitle.= ' - ' . __l('Active');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Inactive) {
                $conditions['Project.is_active'] = 0;
                $this->pageTitle.= ' - ' . __l('Inactive');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Featured) {
                $conditions['Project.is_featured'] = 1;
                $this->pageTitle.= ' - ' . __l('Featured');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Flagged) {
                $conditions['Project.is_system_flagged'] = 1;
                $this->pageTitle.= ' - ' . __l('System Flagged');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::UserFlagged) {
                $conditions['Project.is_user_flagged'] = 1;
                $this->pageTitle.= ' - ' . __l('User Flagged');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Drafted) {
                $conditions['Project.is_draft'] = 1;
                $this->pageTitle.= ' - ' . __l('Drafted');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Flexible) {
                $conditions['Project.payment_method_id'] = ConstPaymentMethod::KiA;
                $this->pageTitle.= ' - ' . __l('Flexible');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Fixed) {
                $conditions['Project.payment_method_id'] = ConstPaymentMethod::AoN;
                $this->pageTitle.= ' - ' . __l('Fixed');
            } elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::PendingPayment) {
                $conditions['Lend.is_collection'] = 1;
                $this->pageTitle.= ' - ' . __l('Pending Payment');
            }
            $this->request->params['named']['filter_id'] = $this->request->data['Project']['filter_id'];
        }
        if (!empty($this->request->data['Project']['project_status_id'])) {
            $this->request->params['named']['project_status_id'] = $this->request->data['Project']['project_status_id'];
            $conditions['Lend.lend_project_status_id'] = $this->request->data['Project']['project_status_id'];
        } elseif (!empty($this->request->params['named']['project_status_id'])) {
            $this->request->data['Project']['project_status_id'] = $this->request->params['named']['project_status_id'];
            $conditions['Lend.lend_project_status_id'] = $this->request->data['Project']['project_status_id'];
        } elseif (!empty($this->request->params['named']['transaction_type_id']) && $this->request->params['named']['transaction_type_id'] == ConstTransactionTypes::ListingFee) {
            $this->pageTitle.= ' - ' . __l('Listing Fee Paid');
            $this->request->data['Project']['transaction_type_id'] = $this->request->params['named']['transaction_type_id'];
            $foreigns = $this->Lend->Project->Transaction->find('list', array(
                'conditions' => array(
                    'Transaction.class' => 'Project',
                    'Transaction.transaction_type_id' => ConstTransactionTypes::ListingFee,
                    'Project.project_type_id' => ConstProjectTypes::Lend
                ) ,
                'fields' => array(
                    'Transaction.foreign_id'
                ) ,
                'recursive' => 0
            ));
            $conditions['Project.id'] = $foreigns;
        }
        if (!empty($this->request->data['Project']['project_status_id']) or !empty($this->request->data['Project']['project_status_id'])) {
            switch ($conditions['Lend.lend_project_status_id']) {
                case ConstLendProjectStatus::Pending:
                    $this->pageTitle.= ' - ' . __l('Pending');
                    break;

                case ConstLendProjectStatus::OpenForLending:
                    $this->pageTitle.= ' - ' . __l('Open for Lending');
                    break;

                case ConstLendProjectStatus::OpenForIdea:
                    $this->pageTitle.= ' - ' . __l('Open for Voting');
                    break;

                case ConstLendProjectStatus::ProjectClosed:
                    $this->pageTitle.= ' - ' . __l('Project Closed');
                    break;

                case ConstLendProjectStatus::ProjectExpired:
                    $this->pageTitle.= ' - ' . __l('Project Expired');
                    break;

                case ConstLendProjectStatus::ProjectCanceled:
                    $this->pageTitle.= ' - ' . __l('Canceled');
                    break;

                case ConstLendProjectStatus::ProjectAmountRepayment:
                    $this->pageTitle.= ' - ' . __l('Amount Repayment');
                    break;

                case ConstLendProjectStatus::PendingAction:
                    $this->pageTitle.= ' - ' . __l('Pending Action to Admin');
                    break;

                default:
                    break;
            }
        }
        if (isset($this->request->params['named']['q'])) {
            $conditions['AND']['OR'][]['Project.name LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['User.username LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
            $this->request->data['Project']['q'] = $this->request->params['named']['q'];
        }
        if (!empty($this->request->params['named']['type']) && $this->request->params['named']['type'] == 'listing_fee') {
            $conditions['Project.fee_amount !='] = '0.00';
        }
        if (!empty($this->request->params['named']['project_flag_category_id'])) {
            $project_flag = $this->Lend->Project->ProjectFlag->find('list', array(
                'conditions' => array(
                    'ProjectFlag.project_flag_category_id' => $this->request->params['named']['project_flag_category_id'],
                    'Project.project_type_id' => ConstProjectTypes::Lend
                ) ,
                'fields' => array(
                    'ProjectFlag.id',
                    'ProjectFlag.project_id'
                ) ,
                'recursive' => -1
            ));
            $conditions['Project.id'] = $project_flag;
        }
        if (!empty($this->request->params['named']['project_category_id'])) {
            $conditions['Lend.lend_project_category_id'] = $this->request->params['named']['project_category_id'];
            $lendProjectCategory = $this->Lend->LendProjectCategory->find('first', array(
                'conditions' => array(
                    'LendProjectCategory.id' => $this->request->params['named']['project_category_id']
                ) ,
                'fields' => array(
                    'LendProjectCategory.id',
                    'LendProjectCategory.name'
                ) ,
                'recursive' => -1
            ));
            if (empty($lendProjectCategory)) {
                throw new NotFoundException(__l('Invalid request'));
            }
            $this->pageTitle.= ' - ' . $lendProjectCategory['LendProjectCategory']['name'];
        } elseif (!empty($this->request->params['named']['user_id'])) {
            $user = $this->{$this->modelClass}->User->find('first', array(
                'conditions' => array(
                    'User.id' => $this->request->params['named']['user_id']
                ) ,
                'fields' => array(
                    'User.id',
                    'User.username'
                ) ,
                'recursive' => -1
            ));
            if (empty($user)) {
                throw new NotFoundException(__l('Invalid request'));
            }
            $conditions['Project.user_id'] = $this->request->params['named']['user_id'];
            $this->pageTitle.= ' - ' . $user['User']['username'];
        }
        $contain = array(
            'User',
            'ProjectType',
            'Attachment',
            'Lend' => array(
                'LendProjectStatus',
                'LendProjectCategory'
            ) ,
            'Ip' => array(
                'City' => array(
                    'fields' => array(
                        'City.name',
                    )
                ) ,
                'State' => array(
                    'fields' => array(
                        'State.name',
                    )
                ) ,
                'Country' => array(
                    'fields' => array(
                        'Country.name',
                        'Country.iso_alpha2',
                    )
                ) ,
                'fields' => array(
                    'Ip.ip',
                    'Ip.latitude',
                    'Ip.longitude',
                    'Ip.host'
                )
            ) ,
        );
        if (!empty($this->request->data['Project']['project_status_id']) && $this->request->data['Project']['project_status_id'] == ConstLendProjectStatus::PendingAction) {
            $conditions['Project.is_pending_action_to_admin'] = 1;
            unset($conditions['Lend.lend_project_status_id']);
            App::import('Model', 'Projects.FormFieldStep');
            $FormFieldStep = new FormFieldStep();
            $splashStep = $FormFieldStep->find('first', array(
                'conditions' => array(
                    'FormFieldStep.project_type_id' => ConstProjectTypes::Lend,
                    'FormFieldStep.is_splash' => 1
                ) ,
                'fields' => array(
                    'FormFieldStep.order'
                ) ,
                'recursive' => -1
            ));
            $this->set('splashStep', $splashStep['FormFieldStep']['order']);
        }
        if (!empty($this->request->params['named']['step'])) {
            $admin_pending_projects = $this->Lend->Project->find('all', array(
                'conditions' => $conditions,
                'recursive' => -1
            ));
            $projectIds = array();
            foreach($admin_pending_projects as $admin_project) {
                if (max(array_keys(unserialize($admin_project['Project']['tracked_steps']))) == $this->request->params['named']['step']) {
                    $projectIds[] = $admin_project['Project']['id'];
                }
            }
            $conditions['Project.id'] = $projectIds;
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => array(
                'Project.id' => 'desc'
            ) ,
            'recursive' => 3
        );
        /// Status Based on Count
        $this->set('opened_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::OpenForLending,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('idea_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('pending_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::Pending,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('canceled_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::ProjectCanceled,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('goal_reached', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id =' => ConstLendProjectStatus::ProjectAmountRepayment,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('closed_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::ProjectClosed,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('open_for_idea', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('expired_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::ProjectExpired,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('paid_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Lend,
                'Project.is_paid' => 1
            ) ,
            'recursive' => 0
        )));
        // total openid users list
        $this->set('suspended', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_admin_suspended = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('user_flagged', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_user_flagged = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('system_flagged', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_system_flagged = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('system_drafted', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_draft = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('successful_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_successful = ' => 0,
                'Lend.lend_project_status_id' => ConstLendProjectStatus::ProjectClosed,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('failed_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_successful = ' => 1,
                'Lend.lend_project_status_id' => ConstLendProjectStatus::ProjectClosed,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('active_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_active' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('inactive_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_active' => 0,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('featured_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_featured' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('total_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('total_flexible_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.payment_method_id' => ConstPaymentMethod::KiA,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('total_fixed_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.payment_method_id' => ConstPaymentMethod::AoN,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('projects', $this->paginate('Project'));
        $filters = $this->Lend->Project->isFilterOptions;
        $moreActions = $this->Lend->Project->moreActions;
        if (empty($this->request->data['Project']['project_status_id']) || $this->request->data['Project']['project_status_id'] != ConstLendProjectStatus::ProjectClosed) {
            unset($moreActions[ConstMoreAction::Successful]);
            unset($moreActions[ConstMoreAction::Failed]);
        }
        $projectStatuses = $this->Lend->LendProjectStatus->find('list', array(
            'conditions' => array(
                'LendProjectStatus.is_active' => 1
            ) ,
            'recursive' => -1
        ));
        $this->set('moreActions', $moreActions);
        $this->set('filters', $filters);
        $this->set('projectStatuses', $projectStatuses);
        if (!empty($this->request->data['Project']['project_status_id']) && $this->request->data['Project']['project_status_id'] == ConstLendProjectStatus::PendingAction) {
            $this->set('step_count', $this->Lend->Project->getStepCount(ConstProjectTypes::Lend));
            $this->render('admin_index_pending');
        }
    }
    public function admin_lend_svg() 
    {
        $this->loadModel('Projects.FormFieldStep');
        $formFieldStep = $this->FormFieldStep->find('count', array(
            'conditions' => array(
                'FormFieldStep.is_splash' => 1,
                'FormFieldStep.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        ));
        $this->set('formFieldStep', $formFieldStep);
        /// Status Based on Count
        $this->set('opened_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::OpenForLending,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('pending_action_to_admin_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_pending_action_to_admin' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('idea_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('pending_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::Pending,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('canceled_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::ProjectCanceled,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('goal_reached', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id =' => ConstLendProjectStatus::ProjectAmountRepayment,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('closed_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::ProjectClosed,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('open_for_idea', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::OpenForIdea,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('expired_project_count', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Lend.lend_project_status_id = ' => ConstLendProjectStatus::ProjectExpired,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('paid_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Lend,
                'Project.is_paid' => 1
            ) ,
            'recursive' => 0
        )));
        // total openid users list
        $this->set('suspended', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_admin_suspended = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('user_flagged', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_user_flagged = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('system_flagged', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_system_flagged = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('system_drafted', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_draft = ' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('successful_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_successful = ' => 1,
                'Lend.lend_project_status_id' => ConstLendProjectStatus::ProjectClosed,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('failed_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.is_successful = ' => 0,
                'Lend.lend_project_status_id' => ConstLendProjectStatus::ProjectClosed,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        )));
        $this->set('total_projects', $this->Lend->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->layout = 'ajax';
    }
    public function admin_funds() 
    {
        $this->_redirectPOST2Named(array(
            'q'
        ));
        $this->loadModel('Projects.ProjectFund');
        $this->pageTitle = sprintf(__l('%s %s Lends') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'));
        $conditions = array();
        $project_ids = $this->Lend->find('list', array(
            'conditions' => array(
                'Lend.lend_project_status_id' => ConstLendProjectStatus::OpenForLending
            ) ,
            'fields' => array(
                'Lend.project_id'
            ) ,
            'recursive' => -1
        ));
        if (!empty($this->request->params['named']['project_id'])) {
            $conditions['ProjectFund.project_id'] = $this->request->params['named']['project_id'];
        }
        if (isset($this->request->params['named']['status'])) {
            if ($this->request->params['named']['status'] == 'refunded') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Expired;
            } else if ($this->request->params['named']['status'] == 'paid') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
            } else if ($this->request->params['named']['status'] == 'cancelled') {
                $conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Canceled;
            }
        }
        $this->set('fund_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('refunded_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Expired,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('paid_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $this->set('cancelled_count', $this->ProjectFund->find('count', array(
            'conditions' => array(
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Canceled,
                'ProjectFund.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        )));
        $conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Lend;
        if (!empty($this->request->params['named']['project'])) {
            $conditions['ProjectFund.project_id'] = $this->request->params['named']['project'];
            $project_name = $this->ProjectFund->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $this->request->params['named']['project'],
                ) ,
                'fields' => array(
                    'Project.name',
                ) ,
                'recursive' => -1,
            ));
            $this->pageTitle.= ' - ' . $project_name['Project']['name'];
        }
        if (!empty($this->request->params['named']['project_id'])) {
            $conditions['ProjectFund.project_id'] = $this->request->params['named']['project_id'];
            $project_name = $this->ProjectFund->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $this->request->params['named']['project_id'],
                ) ,
                'fields' => array(
                    'Project.name',
                ) ,
                'recursive' => -1,
            ));
            $this->pageTitle.= ' - ' . $project_name['Project']['name'];
        } elseif (!empty($this->request->params['named']['user_id'])) {
            $conditions['ProjectFund.user_id'] = $this->request->params['named']['user_id'];
            $user = $this->{$this->modelClass}->User->find('first', array(
                'conditions' => array(
                    'User.id' => $this->request->params['named']['user_id']
                ) ,
                'fields' => array(
                    'User.id',
                    'User.username'
                ) ,
                'recursive' => -1
            ));
            if (empty($user)) {
                throw new NotFoundException(__l('Invalid request'));
            }
            $this->pageTitle.= ' - ' . $user['User']['username'];
        }
        if (!empty($this->request->params['named']['q'])) {
            $conditions['AND']['OR'][]['User.username LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['Project.name LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['Project.description LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $conditions['AND']['OR'][]['Project.short_description LIKE'] = '%' . $this->request->params['named']['q'] . '%';
            $this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
            $this->request->data['ProjectFund']['q'] = $this->request->params['named']['q'];
        }
        $contain = array(
            'Project' => array(
                'Lend' => array(
                    'LendProjectStatus'
                )
            ) ,
            'User',
        );
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => $contain,
            'order' => array(
                'ProjectFund.id' => 'desc'
            ) ,
            'recursive' => 3
        );
        $this->set('projectFunds', $this->paginate('ProjectFund'));
        $total_lend_conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Authorized;
        $lend = $this->ProjectFund->find('first', array(
            'conditions' => $total_lend_conditions,
            'fields' => array(
                'SUM(ProjectFund.amount) as total_amount',
            ) ,
            'recursive' => -1
        ));
        $total_lend = ($lend[0]['total_amount']) ? $lend[0]['total_amount'] : 0;
        $total_paid_conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
        $paid = $this->ProjectFund->find('first', array(
            'conditions' => $total_paid_conditions,
            'fields' => array(
                'SUM(ProjectFund.amount) as total_amount',
            ) ,
            'recursive' => -1
        ));
        $total_paid = ($paid[0]['total_amount']) ? $paid[0]['total_amount'] : 0;
        $total_refunded_conditions['ProjectFund.project_fund_status_id'] = array(
            ConstProjectFundStatus::Expired,
            ConstProjectFundStatus::Canceled
        );
        $refunded = $this->ProjectFund->find('first', array(
            'conditions' => $total_refunded_conditions,
            'fields' => array(
                'SUM(ProjectFund.amount) as total_amount',
            ) ,
            'recursive' => -1
        ));
        $total_refunded = ($refunded[0]['total_amount']) ? $refunded[0]['total_amount'] : 0;
        $this->set(compact('projectStatuses'));
        $this->set('total_lend', $total_lend);
        $this->set('total_paid', $total_paid);
        $this->set('total_refunded', $total_refunded);
        if (!empty($this->request->params['named']['project_id'])) {
            $this->set("project_id", $this->request->params['named']['project_id']);
        }
    }
    public function summary() 
    {
        $this->pageTitle = sprintf(__l('%s summary') , Configure::read('project.alt_name_for_lend_singular_caps'));
        $user = $this->Lend->Project->User->find('first', array(
            'conditions' => array(
                'User.id' => $this->Auth->user('id')
            ) ,
            'recursive' => -1
        ));
        $this->set("user", $user);
    }
}
?>
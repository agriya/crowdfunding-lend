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
class LendEventHandler extends Object implements CakeEventListener
{
    /**
     * implementedEvents
     *
     * @return array
     */
    public function implementedEvents() 
    {
        return array(
            'View.Project.displaycategory' => array(
                'callable' => 'onCategorydisplay'
            ) ,
            'View.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox'
            ) ,
            'Controller.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox'
            ) ,
            'Behavior.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox',
            ) ,
            'View.Project.onCategoryListing' => array(
                'callable' => 'onCategoryListingRender',
            ) ,
            'View.Project.projectStatusValue' => array(
                'callable' => 'getProjectStatusValue'
            ) ,
            'Model.Project.beforeAdd' => array(
                'callable' => 'onProjectValidation',
            ) ,
            'Controller.Projects.afterAdd' => array(
                'callable' => 'onProjectAdd',
            ) ,
            'Controller.Projects.afterCheckRate' => array(
                'callable' => 'onLendProjectAdd',
            ) ,
            'Controller.Projects.afterEdit' => array(
                'callable' => 'onProjectEdit',
            ) ,
            'Controller.ProjectFunds.beforeAdd' => array(
                'callable' => 'isAllowAddFund',
            ) ,
            'Controller.ProjectFunds.beforeValidation' => array(
                'callable' => 'onProjectFundValidation',
            ) ,
            'Controller.ProjectFunds.afterAdd' => array(
                'callable' => 'onProjectFundAdd',
            ) ,
            'Controller.Project.openFunding' => array(
                'callable' => 'onOpenFunding',
            ) ,
            'Model.Project.openFunding' => array(
                'callable' => 'onOpenFunding',
            ) ,
            'Controller.ProjectType.projectIds' => array(
                'callable' => 'onMessageDisplay',
            ) ,
            'Controller.ProjectType.ClosedProjectIds' => array(
                'callable' => 'getClosedProjectIds',
            ) ,
            'Controller.ProjectType.getConditions' => array(
                'callable' => 'getConditions',
            ) ,
            'Controller.ProjectType.getContain' => array(
                'callable' => 'getContain',
            ) ,
            'Controller.ProjectType.getProjectTypeStatus' => array(
                'callable' => 'getProjectTypeStatus',
            ) ,
            'View.Project.howitworks' => array(
                'callable' => 'howitworks',
                'priority' => 4
            ) ,
            'View.AdminDasboard.onActionToBeTaken' => array(
                'callable' => 'onActionToBeTakenRender'
            ) ,
            'Controller.Project.projectStart' => array(
                'callable' => 'onProjectStart',
            ) ,
            'Controller.FeatureProject.getConditions' => array(
                'callable' => 'getFeatureProjectList'
            ) ,
        );
    }
    /**
     * onCategoryListing
     *
     * @param CakeEvent $event
     * @return void
     */
    public function onProjectStart($event) 
    {
        $controller = $event->subject();
        if (!empty($controller->request->named['project_type']) && $controller->request->named['project_type'] == 'lend' && empty($_SESSION['lendDetails']) && empty($controller->request->pass)) {
            $controller->redirect(array(
                'controller' => 'lends',
                'action' => 'check_rate',
                'admin' => false
            ));
        }
    }
    public function onCategoryListingRender($event) 
    {
        $content = '';
        if (!empty($event->data['data']['project_type']) && $event->data['data']['project_type'] == 'lend') {
            $view = $event->subject();
            App::import('Model', 'Lend.LendProjectCategory');
            $this->LendProjectCategory = new LendProjectCategory();
            $projectCategories = $this->LendProjectCategory->find('all', array(
                'fields' => array(
                    'LendProjectCategory.name',
                    'LendProjectCategory.slug'
                ) ,
                'limit' => 10,
                'order' => 'LendProjectCategory.name asc'
            ));
            if (!empty($projectCategories)) {
                $content = '<h4>' . __l('Filter by Category') . '</h4>
        	     <ul class="nav navbar-nav nav-tabs nav-stacked">';
                foreach($projectCategories as $project_category) {
                    $class = (!empty($event->data['data']['category']) && $event->data['data']['category'] == $project_category['LendProjectCategory']['slug']) ? ' class="active"' : null;
                    $content.= '<li' . $class . '>' . $view->Html->link($project_category['LendProjectCategory']['name'], array(
                        'controller' => 'projects',
                        'action' => 'index',
                        'category' => $project_category['LendProjectCategory']['slug'],
                        'project_type' => 'lend',
                    ) , array(
                        'title' => $project_category['LendProjectCategory']['name']
                    )) . '</li>';
                }
                $content.= '</ul>';
            }
        }
        $event->data['content'] = $content;
    }
    public function onProjectValidation($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        $error = array();
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            App::import('Model', 'Lend.Lend');
            $this->Lend = new Lend();
            $this->Lend->set($data);
            if (!$this->Lend->validates()) {
                $error = $this->Lend->validationErrors;
            }
        }
        $event->data['error']['Lend'] = $error;
    }
    public function onProjectAdd($event) 
    {
        $controller = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            $lend = $controller->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'Lend.id',
                ) ,
                'recursive' => 0
            ));
            if (!empty($lend) && !empty($lend['Lend']['id'])) {
                $data['Lend']['id'] = $lend['Lend']['id'];
            }
            $data['Lend']['project_id'] = $data['Project']['id'];
            $data['Lend']['user_id'] = $controller->Auth->user('id');
            $controller->Project->Lend->save($data);
        }
    }
    public function onLendProjectAdd($event) 
    {
        $controller = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            $lend = $controller->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'Lend.id',
                ) ,
                'recursive' => 0
            ));
            if (!empty($lend) && !empty($lend['Lend']['id'])) {
                $data['Lend']['id'] = $lend['Lend']['id'];
            }
            if (empty($lend['Lend']['lend_project_status_id'])) {
                if (!isset($data['Project']['is_draft'])) {
                    $data['Lend']['lend_project_status_id'] = ConstLendProjectStatus::Pending;
                } else {
                    $data['Lend']['lend_project_status_id'] = 0;
                }
            }
            $data['Lend']['project_id'] = $data['Project']['id'];
            $data['Lend']['user_id'] = $controller->Auth->user('id');
            $controller->Project->Lend->save($data);
        }
    }
    public function onProjectEdit($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            App::import('Model', 'Lend.Lend');
            $this->Lend = new Lend();
            $lend_data = $this->Lend->find('first', array(
                'conditions' => array(
                    'Lend.project_id' => $data['Project']['id']
                ) ,
                'recursive' => -1
            ));
            if (!empty($data['Project']['publish']) && empty($lend_data['Lend']['lend_project_status_id'])) {
                $data['Lend']['lend_project_status_id'] = ConstLendProjectStatus::Pending;
            }
            $data['Lend']['id'] = $lend_data['Lend']['id'];
            $this->Lend->save($data);
        }
    }
    public function isAllowAddFund($event) 
    {
        $project = $event->data['data'];
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            App::import('Model', 'Lend.Lend');
            $this->Lend = new Lend();
            $lend_data = $this->Lend->find('first', array(
                'conditions' => array(
                    'Lend.project_id' => $project['Project']['id']
                ) ,
                'recursive' => -1
            ));
            if (strtotime(date('Y-m-d 23:59:59', strtotime($project['Project']['project_end_date']))) > time() && $project['Project']['needed_amount'] <= $project['Project']['collected_amount']) {
                $event->data['error'] = sprintf(__l('%s has been not allowed overfunding') , Configure::read('project.alt_name_for_project_singular_caps'));
            } else {
                $event->data['lend'] = $lend_data;
            }
        }
    }
    public function onProjectFundValidation($event) 
    {
        $validationErrors = '';
        $data = $event->data['data'];
        App::import('Model', 'Project.Project');
        $this->Project = new Project();
        $project = $this->Project->find('first', array(
            'conditions' => array(
                'Project.id' => $data['ProjectFund']['project_id']
            ) ,
            'recursive' => -1
        ));
		if ($project['Project']['project_type_id'] == ConstProjectTypes::Lend) {
			if (($data['ProjectFund']['amount'] > $project['Project']['needed_amount']-$project['Project']['collected_amount'])) {
				$validationErrors['amount'] = __l('The amount should be less than needed amount.');
			}
			$event->data['error'] = $validationErrors;
		}
    }
    public function onProjectFundAdd($event) 
    {
        $project_fund = $event->data['data'];
        $controller = $event->subject();
        if (!empty($project_fund['Lend']['is_agree_terms_conditions'])) {
            App::import('Model', 'Project.Project');
            $this->Project = new Project();
            $lend = $this->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $project_fund['ProjectFund']['project_id']
                ) ,
                'contain' => array(
                    'Lend',
                ) ,
                'recursive' => 0
            ));
            if (!empty($project_fund['ProjectFund'])) {
                if ($lend['Project']['project_type_id'] == ConstProjectTypes::Lend) {
                    App::import('Model', 'Lend.LendName');
                    $this->LendName = new LendName();
                    if (empty($project_fund['ProjectFund']['lend_name_id'])) {
                        $data['LendName']['name'] = $lend['Project']['name'];
                        $data['LendName']['amount'] = $project_fund['ProjectFund']['amount'];
                        $data['LendName']['average_rate'] = $lend['Lend']['target_interest_rate'];
                        $data['LendName']['user_id'] = $controller->Auth->user('id');
                        $this->LendName->save($data);
                        $project_fund['ProjectFund']['lend_name_id'] = $this->LendName->id;
                        $this->Project->ProjectFund->save($project_fund['ProjectFund']);
                    }
                    $term_data['LendNamesLoanTerm']['lend_name_id'] = $project_fund['ProjectFund']['lend_name_id'];
                    $term_data['LendNamesLoanTerm']['loan_term_id'] = $lend['Lend']['loan_term_id'];
                    $this->LendName->LendNamesLoanTerm->save($term_data);
                    $category_data['LendNamesLendProjectCategory']['lend_name_id'] = $project_fund['ProjectFund']['lend_name_id'];
                    $category_data['LendNamesLendProjectCategory']['lend_project_category_id'] = $lend['Lend']['lend_project_category_id'];
                    $this->LendName->LendNamesLendProjectCategory->save($category_data);
                    $credit_data['LendNamesCreditScore']['lend_name_id'] = $project_fund['ProjectFund']['lend_name_id'];
                    $credit_data['LendNamesCreditScore']['credit_score_id'] = $lend['Lend']['credit_score_id'];
                    $this->LendName->LendNamesCreditScore->save($credit_data);
                }
            }
        }
    }
    public function onOpenFunding($event) 
    {
        $controller = $event->subject();
        if (is_object($controller->Project)) {
            $obj = $controller->Project;
        } else {
            $obj = $controller;
        }
        $event_data = $event->data['data'];
        $type = $event->data['type'];
        $project = $obj->find('first', array(
            'conditions' => array(
                'Project.id' => $event_data['project_id']
            ) ,
            'contain' => array(
                'Lend'
            ) ,
            'recursive' => 0
        ));
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            if (isPluginEnabled('Idea') && ($type == 'approve' || $type == 'vote')) {
                if ($project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::Pending) {
                    $obj->Lend->updateStatus(ConstLendProjectStatus::OpenForIdea, $event_data['project_id']);
                    $event->data['message'] = __l('Idea has been opened for voting');
                } else {
                    $event->data['error_message'] = __l('Idea has been already opened for voting');
                }
            } else {
                if ($project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::Pending || $project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForIdea) {
                    $obj->Lend->updateStatus(ConstLendProjectStatus::OpenForLending, $event_data['project_id']);
                    $event->data['message'] = sprintf(__l('%s has been opened for %s') , Configure::read('project.alt_name_for_project_singular_caps') , Configure::read('project.alt_name_for_lender_present_continuous'));
                } else {
                    $event->data['error_message'] = sprintf(__l('%s has been already opened for %s') , Configure::read('project.alt_name_for_project_singular_caps') , Configure::read('project.alt_name_for_lender_present_continuous'));
                }
            }
        }
    }
    public function onCategorydisplay($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        $class = '';
		if(isset($event->data['class'])){
			$class = $event->data['class'];
		}
        $extra_arr = array();
        if (!empty($event->data['target'])) {
            $extra_arr['target'] = '_blank';
        }
        $return = '';
        if ($data['ProjectType']['id'] == ConstProjectTypes::Lend) {
            App::import('Model', 'Lend.Lend');
            $Lend = new Lend;
            $lend = $Lend->find('first', array(
                'conditions' => array(
                    'Lend.project_id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'LendProjectCategory'
                ) ,
                'recursive' => 0
            ));
            if (!empty($lend['LendProjectCategory'])) {
                if ($class == 'categoryname') {
                    $return = $lend['LendProjectCategory']['name'];
                } else {
                    if ($lend['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForIdea) {
                        $return.= $obj->Html->link($lend['LendProjectCategory']['name'], array(
                            'controller' => 'projects',
                            'action' => 'index',
                            'category' => $lend['LendProjectCategory']['slug'],
                            'project_type' => 'lend',
                            'idea' => 'idea'
                        ) , array_merge(array(
                            'title' => $lend['LendProjectCategory']['name'],
                            'class' => 'text-danger' .$class
                        ) , $extra_arr));
                    } else {
                        $return.= $obj->Html->link($lend['LendProjectCategory']['name'], array(
                            'controller' => 'projects',
                            'action' => 'index',
                            'category' => $lend['LendProjectCategory']['slug'],
                            'project_type' => 'lend',
                        ) , array_merge(array(
                            'title' => $lend['LendProjectCategory']['name'],
                            'class' => 'text-danger' .$class
                        ) , $extra_arr));
                    }
                }
            }
            $event->data['content'] = $return;
        }
    }
    public function onMessageDisplay($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        App::import('Model', 'Lend.Lend');
        $Lend = new Lend;
        $projectIds = $Lend->find('list', array(
            'conditions' => array(
                'Lend.lend_project_status_id' => array(
                    ConstLendProjectStatus::OpenForLending,
                    ConstLendProjectStatus::ProjectAmountRepayment,
                ) ,
                'Lend.user_id' => $obj->Auth->user('id') ,
            ) ,
            'fields' => array(
                'Lend.project_id'
            )
        ));
        $projectIds = array_unique(array_merge($projectIds, $data));
        $event->data['ids'] = $projectIds;
        $event->data['projectStatus'] = $this->__getProjectStatus($projectIds);
    }
    public function __getProjectStatus($projectIds) 
    {
        App::import('Model', 'Lend.Lend');
        $Lend = new Lend;
        $lends = $Lend->find('all', array(
            'conditions' => array(
                'Lend.project_id' => $projectIds,
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
        return $projectDetails;
    }
    public function getProjectStatusValue($event) 
    {
        $projectStatusIds = $event->data['status_id'];
        $projectTypeId = $event->data['project_type_id'];
        if ($projectTypeId == ConstProjectTypes::Lend) {
            $lendProjectStatus = array(
                ConstLendProjectStatus::Pending => __l('Pending') ,
                ConstLendProjectStatus::OpenForLending => __l('Open for Lending') ,
                ConstLendProjectStatus::ProjectClosed => __l('Project Closed') ,
                ConstLendProjectStatus::ProjectExpired => sprintf(__l('%s Expired') , Configure::read('project.alt_name_for_project_singular_caps')) ,
                ConstLendProjectStatus::ProjectCanceled => sprintf(__l('%s Canceled') , Configure::read('project.alt_name_for_project_singular_caps')) ,
                ConstLendProjectStatus::ProjectAmountRepayment => __l('Goal Reached') ,
                ConstLendProjectStatus::OpenForIdea => __l('Open for voting')
            );
            if (array_key_exists($projectStatusIds, $lendProjectStatus)) {
                $event->data['response'] = $lendProjectStatus[$projectStatusIds];
            } else {
                $event->data['response'] = 0;
            }
        }
    }
    public function onMessageInbox($event) 
    {
        $obj = $event->subject();
        $projectStatus = $event->data['projectStatus'];
        $project = $event->data['project'];
        if (!empty($project['Project']['project_type_id']) && $project['Project']['project_type_id'] == ConstProjectTypes::Lend) {
            $projectStatusNew = $this->__getProjectStatus($project['Project']['id']);
            if (!empty($event->data['type']) && $event->data['type'] == 'status') {
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::ProjectClosed
                ))) {
                    $event->data['is_allow_to_print_voucher'] = 1;
                    $event->data['is_allow_to_change_given'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::OpenForLending
                ))) {
                    $event->data['is_allow_to_cancel_lend'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::OpenForIdea
                ))) {
                    $event->data['is_allow_to_vote'] = 1;
                    $event->data['is_allow_to_move_for_funding'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_move_for_voting'] = 1;
                    $event->data['is_allow_to_move_for_funding'] = 1;
                    if (isPluginEnabled('Idea')) {
                        $event->data['is_show_vote'] = 1;
                    }
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::ProjectClosed,
                    ConstLendProjectStatus::ProjectAmountRepayment
                ))) {
                    $event->data['is_allow_to_change_status'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::OpenForLending,
                    ConstLendProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_cancel_project'] = 1;
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::ProjectCanceled,
                    ConstLendProjectStatus::ProjectExpired
                ))) {
                    $event->data['is_allow_to_follow'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::Pending,
                    ConstLendProjectStatus::ProjectCanceled
                ))) {
                    $event->data['is_affiliate_status_pending'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::ProjectClosed
                ))) {
                    $event->data['is_not_show_you_here'] = 1;
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::Pending,
                    ConstLendProjectStatus::OpenForIdea
                ))) {
                    $event->data['is_show_project_funding_tab'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::OpenForLending,
                    ConstLendProjectStatus::ProjectAmountRepayment
                ))) {
                    $event->data['is_allow_to_fund'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::OpenForIdea,
                    ConstLendProjectStatus::OpenForLending
                ))) {
                    $event->data['is_allow_to_share'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_pay_listing_fee'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    0,
                    ConstLendProjectStatus::Pending,
                    ConstLendProjectStatus::OpenForIdea
                )) || (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::OpenForLending
                )) && Configure::read('Project.is_allow_project_owner_to_edit_project_in_open_status'))) {
                    $event->data['is_allow_to_edit_fund'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstLendProjectStatus::Pending
                ))) {
                    $event->data['is_pending_status'] = 1;
                }
            }
            if (empty($projectStatus)) {
                $event->data['projectStatus'] = $projectStatusNew;
            } else {
                $event->data['projectStatus'] = $projectStatusNew+$projectStatus;
            }
        }
    }
    public function getClosedProjectIds($event) 
    {
        $obj = $event->subject();
        $project_ids = $event->data['project_ids'];
        $status_id = ConstLendProjectStatus::ProjectClosed;
        $conditions = array();
        $conditions['Lend.project_id'] = $project_ids;
        $conditions['Lend.lend_project_status_id'] = $status_id;
        $tmp_project_ids = $this->__getProjectIds($conditions);
        $conditions = array();
        $conditions['Lend.user_id'] = $obj->Auth->user('id');
        $conditions['Lend.lend_project_status_id'] = $status_id;
        $tmp1_project_ids = $this->__getProjectIds($conditions);
        $event->data['project_ids'] = array_unique(array_merge($tmp_project_ids, $tmp1_project_ids));
    }
    public function __getProjectIds($conditions) 
    {
        App::import('Model', 'Lend.Lend');
        $lend = new Lend();
        $projectIds = $lend->find('list', array(
            'conditions' => $conditions,
            'fields' => array(
                'Lend.project_id'
            )
        ));
        return $projectIds;
    }
    public function getConditions($event) 
    {
        if (!empty($event->data['data'])) {
            $data = $event->data['data'];
        }
        if (!empty($event->data['type'])) {
            $type = $event->data['type'];
        }
        if (!empty($event->data['page'])) {
            $page = $event->data['page'];
        }
        if (!empty($data) && $data['ProjectType']['id'] == ConstProjectTypes::Lend) {
            if ($type == 'idea') {
                $event->data['conditions'] = array(
                    'Lend.lend_project_status_id' => ConstLendProjectStatus::OpenForIdea
                );
            } elseif ($type == 'open') {
                $event->data['conditions']['OR'][]['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForLending;
            } elseif ($type == 'search') {
                $event->data['conditions']['OR'][]['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForIdea;
                $event->data['conditions']['OR'][]['Lend.lend_project_status_id'] = ConstLendProjectStatus::OpenForLending;
            } elseif ($type == 'closed') {
                $event->data['conditions']['OR'][]['Lend.lend_project_status_id'] = ConstLendProjectStatus::ProjectClosed;
                $event->data['conditions']['OR'][]['Lend.lend_project_status_id'] = ConstLendProjectStatus::ProjectAmountRepayment;
            } elseif ($type == 'notclosed') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id NOT' => array(
                        ConstLendProjectStatus::ProjectClosed,
                        ConstLendProjectStatus::ProjectAmountRepayment
                    )
                );
            }
        } elseif (!empty($page)) {
            if ($type == 'idea') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id' => ConstLendProjectStatus::OpenForIdea
                );
            } elseif ($type == 'myprojects') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id NOT' => array(
                        ConstLendProjectStatus::Pending,
                        ConstLendProjectStatus::OpenForIdea,
                        ConstLendProjectStatus::ProjectCanceled,
                        ConstLendProjectStatus::ProjectExpired
                    )
                );
            } elseif ($type == 'search') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id NOT' => array(
                        ConstLendProjectStatus::Pending,
                    )
                );
            } elseif ($type == 'open') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id' => array(
                        ConstLendProjectStatus::OpenForLending,
                        ConstLendProjectStatus::ProjectAmountRepayment,
                    )
                );
            } elseif ($type == 'project_count') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id' => array(
                        ConstLendProjectStatus::OpenForLending,
                        ConstLendProjectStatus::ProjectClosed,
                        ConstLendProjectStatus::ProjectAmountRepayment
                    )
                );
            } elseif ($type == 'all_project_count') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id NOT' => array(
                        ConstLendProjectStatus::OpenForIdea,
                    )
                );
            } elseif ($type == 'idea_count') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id' => array(
                        ConstLendProjectStatus::OpenForIdea
                    )
                );
            } elseif ($type == 'count') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id' => array(
                        ConstLendProjectStatus::OpenForLending,
                        ConstLendProjectStatus::ProjectClosed,
                        ConstLendProjectStatus::ProjectAmountRepayment,
                        ConstLendProjectStatus::OpenForIdea
                    )
                );
            } elseif ($type == 'city_count') {
                $event->data['conditions']['OR'][] = array(
                    'Lend.lend_project_status_id' => array(
                        ConstLendProjectStatus::OpenForLending,
                        ConstLendProjectStatus::ProjectAmountRepayment
                    )
                );
            } elseif ($type == 'iphone') {
                $event->data['conditions']['AND'][] = array(
                    'Lend.lend_project_status_id' => array(
                        ConstLendProjectStatus::OpenForLending,
                        ConstLendProjectStatus::ProjectAmountRepayment
                    )
                );
            }
        }
    }
    public function getContain($event) 
    {
        $obj = $event->subject();
        switch ($event->data['type']) {
            case 1:
                $event->data['contain']['Lend'] = array(
                    'LendProjectCategory',
                    'LendProjectStatus',
                    'LoanTerm',
                    'CreditScore',
                    'RepaymentSchedule'
                );
                break;

            case 2:
                $event->data['contain']['Lend'] = array(
                    'fields' => array(
                        'id'
                    )
                );
                break;
        }
    }
    public function getProjectTypeStatus($event) 
    {
        $obj = $event->subject();
        $project = $event->data['project'];
        if (!empty($project['Lend'])) {
            $data = array();
            $data['Project_funding_text'] = __l('Lending amount');
            $data['Project_funded_text'] = Configure::read('project.alt_name_for_lend_past_tense_small');
            $data['Project_fund_button_lable'] = Configure::read('project.alt_name_for_lend_singular_caps');
            $data['Project_status_name'] = $project['Lend']['LendProjectStatus']['name'];
            if (($project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::OpenForLending || $project['Lend']['lend_project_status_id'] == ConstLendProjectStatus::ProjectAmountRepayment)) {
                if (($obj->Auth->user('id') != $project['Project']['user_id']) || Configure::read('Project.is_allow_owner_fund_own_project')) {
                    $data['Project_fund_button_status'] = true;
                    $data['Project_fund_button_url'] = Router::url(array(
                        'controller' => 'project_funds',
                        'action' => 'add',
                        $project['Project']['id']
                    ) , true);
                } else {
                    $data['Project_fund_button_status'] = false;
                    $data['Project_fund_button_url'] = '';
                }
            } else {
                $data['Project_fund_button_status'] = false;
            }
            if ((strtotime($project['Project']['project_end_date']) < strtotime(date('Y-m-d'))) && ($project['Project']['needed_amount'] != $project['Project']['collected_amount'])) {
                $data['Project_status'] = -1;
            } else if ($project['Project']['needed_amount'] == $project['Project']['collected_amount']) {
                $data['Project_status'] = 1;
            } else {
                $data['Project_status'] = 0;
            }
            $data['Lended'] = $project['Project']['collected_amount'];
            $data['Category_name'] = $project['LendProjectCategory']['name'];
            $event->data['data'] = $data;
        }
    }
    public function howitworks($event) 
    {
        $view = $event->subject();
        App::import('Model', 'PaymentGatewaySetting');
        $this->PaymentGatewaySetting = new PaymentGatewaySetting();
        $arrLendWallet = $this->PaymentGatewaySetting->find('first', array(
            'conditions' => array(
                'PaymentGatewaySetting.payment_gateway_id' => ConstPaymentGateways::Wallet,
                'PaymentGatewaySetting.name' => 'is_enable_for_lend'
            ) ,
            'recursive' => 0
        ));
        if ($arrLendWallet['PaymentGateway']['is_test_mode']) {
            $data['is_lend_wallet_enabled'] = $arrLendWallet['PaymentGatewaySetting']['test_mode_value'];
        } else {
            $data['is_lend_wallet_enabled'] = $arrLendWallet['PaymentGatewaySetting']['live_mode_value'];
        }
        echo $view->element('Lend.how_it_works', $data);
    }
    public function onActionToBeTakenRender($event) 
    {
        $view = $event->subject();
        App::import('Model', 'User');
        $user = new User();
        App::import('Model', 'Lend.Lend');
        $lend = new Lend();
        $data['lend_pending_for_approval_count'] = $lend->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Lend,
                'Project.is_pending_action_to_admin ' => 1
            ) ,
            'recursive' => -1
        ));
        $data['lend_user_flagged_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Project.is_user_flagged' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        ));
        $data['lend_system_flagged_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Project.is_system_flagged' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => -1
        ));
        $data['lend_late_payment_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Lend.is_collection' => 1,
                'Project.project_type_id' => ConstProjectTypes::Lend
            ) ,
            'recursive' => 0
        ));
        $event->data['content']['PendingProject'].= $view->element('Lend.admin_action_taken_pending', $data);
        $event->data['content']['FlaggedProjects'].= $view->element('Lend.admin_action_taken', $data);
        $event->data['content']['LendLatePayment'].= $view->element('Lend.admin_lend_late_payments', $data);
    }
    public function getFeatureProjectList($event) 
    {
        $controller = $event->subject();
		$conditions = array();
		$conditions['Project.is_active'] = 1;
		$conditions['Project.is_draft'] = 0;
		$conditions['Project.is_admin_suspended'] = '0';
		$conditions['Project.project_end_date >= '] = date('Y-m-d');
		$conditions['Project.project_type_id'] = ConstProjectTypes::Lend;
		
		$conditions['NOT'] = array( 'Lend.lend_project_status_id' => array(
				ConstLendProjectStatus::Pending,
				ConstLendProjectStatus::ProjectExpired,
				ConstLendProjectStatus::ProjectCanceled
			));
		
		$contain = array(
			'Attachment',
			'Lend'
		);
		$order = array(
			'Project.is_featured' => 'desc',
			'Project.id' => 'desc'
		);            
		$lend = $controller->Project->find('all', array(
			'conditions' => $conditions,
			'contain' => $contain,
			'recursive' => 3,
			'order' => $order,
			'limit' => 4
		));
		$event->data['content']['Lend'] = $lend;
    }
}
?>
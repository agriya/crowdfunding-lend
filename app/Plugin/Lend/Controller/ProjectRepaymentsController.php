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
class ProjectRepaymentsController extends AppController
{
    public $name = 'ProjectRepayments';
    public $uses = array(
        'ProjectRepayment',
        'Lend',
        'LendName',
    );
    public function beforeFilter() 
    {
        $this->Security->disabledFields = array(
            'ProjectRepayment.project_id',
            'ProjectRepayment.wallet',
            'ProjectRepayment.payment_gateway_id',
            'ProjectRepayment.sudopay_gateway_id',
        );
        parent::beforeFilter();
    }
    public function add($id = null) 
    {
        $this->pageTitle = __l('Pay Repayment');
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if (!empty($this->request->data['ProjectRepayment']['project_id'])) {
            $id = $this->request->data['ProjectRepayment']['project_id'];
        }
        $repayment_project = $this->ProjectRepayment->Project->find('first', array(
            'conditions' => array(
                'Project.id' => $id,
            ) ,
            'contain' => array(
                'Attachment',
                'Lend' => array(
                    'LoanTerm',
                    'CreditScore',
                    'RepaymentSchedule',
                ) ,
                'User' => array(
                    'UserAvatar',
                ) ,
                'ProjectFund' => array(
                    'LendName',
                ) ,
                'ProjectRepayment'
            ) ,
            'recursive' => 2
        ));
        $check_rate_result = $this->Lend->check_rate_calculate($repayment_project['Project']['needed_amount'], $repayment_project['Lend']['target_interest_rate'], $repayment_project['Lend']['total_no_of_repayment']);
        $this_month_principal = $check_rate_result['this_month_total_amount'];
        $this_month_interest = $check_rate_result['this_month_total_interest'];
        if (!empty($this->request->data)) {
            if (!empty($repayment_project)) {
                $this->request->data['ProjectRepayment']['user_id'] = $this->Auth->user('id');
                $this->request->data['ProjectRepayment']['amount'] = $repayment_project['Lend']['next_repayment_amount'];
                $this->request->data['ProjectRepayment']['interest'] = $this_month_interest;
                $this->request->data['ProjectRepayment']['term'] = $repayment_project['Lend']['repayment_count']+1;
                if ($repayment_project['Lend']['next_repayment_date'] < date('Y-m-d')) {
                    $this->request->data['ProjectRepayment']['late_fee'] = Configure::read('lend.late_fee');
                    $this->request->data['ProjectRepayment']['is_late'] = 1;
                    $is_late = 1;
                } else {
                    $this->request->data['ProjectRepayment']['late_fee'] = 0;
                    $this->request->data['ProjectRepayment']['is_late'] = 0;
                    $is_late = 0;
                }
                $this->ProjectRepayment->save($this->request->data);
                $total_repayment_amount = $this->request->data['ProjectRepayment']['amount']+$this->request->data['ProjectRepayment']['late_fee'];
                $blocked_user_balance = $repayment_project['User']['blocked_amount']+$total_repayment_amount;
                $available_user_balance = $repayment_project['User']['available_wallet_amount']-$total_repayment_amount;
                $this->ProjectRepayment->User->updateAll(array(
                    'User.blocked_amount' => "'" . $blocked_user_balance . "'",
                    'User.available_wallet_amount' => "'" . $available_user_balance . "'"
                ) , array(
                    'User.id' => $this->Auth->user('id')
                ));
                $project_repayment_id = $this->ProjectRepayment->id;
                foreach($repayment_project['ProjectFund'] AS $project_fund) {
                    $this->ProjectRepayment->ProjectFundRepayment->create();
                    $project_fund_repayment_data['ProjectFundRepayment']['user_id'] = $this->Auth->user('id');
                    $project_fund_repayment_data['ProjectFundRepayment']['owner_user_id'] = $project_fund['user_id'];
                    $project_fund_repayment_data['ProjectFundRepayment']['project_id'] = $repayment_project['Project']['id'];
                    $project_fund_repayment_data['ProjectFundRepayment']['project_fund_id'] = $project_fund['id'];
                    $project_fund_repayment_data['ProjectFundRepayment']['project_repayment_id'] = $project_repayment_id;
                    $check_rate_result = $this->Lend->check_rate_calculate($project_fund['amount'], $repayment_project['Lend']['target_interest_rate'], $repayment_project['Lend']['total_no_of_repayment']);
                    $this_principal = $check_rate_result['per_month'];
                    $this_interest = $check_rate_result['this_month_total_interest'];
                    $project_fund_repayment_data['ProjectFundRepayment']['amount'] = $this_principal;
                    $project_fund_repayment_data['ProjectFundRepayment']['interest'] = $this_interest;
                    $project_fund_repayment_data['ProjectFundRepayment']['interest_rate'] = $repayment_project['Lend']['target_interest_rate'];
                    $project_fund_repayment_data['ProjectFundRepayment']['term'] = $repayment_project['Lend']['repayment_count']+1;
                    $project_fund_repayment_data['ProjectFundRepayment']['is_late'] = $is_late;
                    $lend_name_data = array();
                    $lend_name_data['LendName']['id'] = $project_fund['LendName']['id'];
                    $lend_name_data['LendName']['total_repayment_amount'] = $project_fund['LendName']['total_repayment_amount']+$project_fund_repayment_data['ProjectFundRepayment']['amount'];
                    $lend_name_data['LendName']['total_repayment_percentage'] = round(($lend_name_data['LendName']['total_repayment_amount']/$project_fund['LendName']['amount']) *100);
                    $lend_name_data['LendName']['total_repayment_interest_amount'] = $project_fund['LendName']['total_repayment_interest_amount']+$project_fund_repayment_data['ProjectFundRepayment']['interest'];
                    $this->ProjectRepayment->ProjectFundRepayment->save($project_fund_repayment_data);
                    $this->ProjectRepayment->User->Transaction->log($this->ProjectRepayment->ProjectFundRepayment->id, 'Lend.ProjectFundRepayment', ConstPaymentGateways::Wallet, ConstTransactionTypes::ProjectRepayment);
                    $this->ProjectRepayment->User->updateAll(array(
                        'User.available_wallet_amount' => 'User.available_wallet_amount + ' . ($project_fund_repayment_data['ProjectFundRepayment']['amount'])
                    ) , array(
                        'User.id' => $project_fund['user_id']
                    ));
                    $this->LendName->save($lend_name_data);
                }
                $this->Lend->onRepaymentAdd($repayment_project['Project']['id']);
                if ($repayment_project['Lend']['repayment_count'] == $repayment_project['Lend']['LoanTerm']['months']) {
                    $this->Lend->updateStatus(ConstLendProjectStatus::ProjectClosed, $repayment_project['Project']['id']);
                }
                $this->Lend->updateFundUsers($repayment_project['Project']['id']);
            }
            $this->Session->setFlash(__l('Repayment has been paid successfully') , 'default', null, 'success');
            $this->redirect(array(
                'controller' => 'users',
                'action' => 'dashboard',
            ));
        }
        $this->set('this_month_principal', $this_month_principal);
        $this->set('this_month_interest', $this_month_interest);
        $this->set('project', $repayment_project);
    }
}
?>
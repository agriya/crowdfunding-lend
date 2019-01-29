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
class LendCronComponent extends Component
{
    public function main() 
    {
        App::import('Model', 'Lend.Lend');
        $this->Lend = new Lend();
        $projects = $this->Lend->find('all', array(
            'conditions' => array(
                'Project.is_draft' => 0,
                'Lend.lend_project_status_id' => array(
                    ConstLendProjectStatus::OpenForLending,
                    ConstLendProjectStatus::ProjectAmountRepayment,
                ) ,
            ) ,
            'contain' => array(
                'Project'
            ) ,
            'recursive' => 0
        ));
        foreach($projects as $project) {
            if (($project['Project']['collected_amount'] >= $project['Project']['needed_amount'] && strtotime($project['Project']['project_end_date'] . ' 23:55:59') <= strtotime(date('Y-m-d H:i:s'))) || (strtotime($project['Project']['project_end_date'] . ' 23:55:59') <= strtotime(date('Y-m-d H:i:s')) && $project['Project']['payment_method_id'] == ConstPaymentMethod::KiA)) {
                if (empty($project['Project']['project_fund_count'])) {
                    $this->Lend->updateStatus(ConstLendProjectStatus::ProjectExpired, $project['Project']['id']);
                } elseif ($project['Lend']['lend_project_status_id'] != ConstLendProjectStatus::ProjectAmountRepayment) {
                    $this->Lend->updateStatus(ConstLendProjectStatus::ProjectAmountRepayment, $project['Project']['id']);
                }
            } elseif (strtotime($project['Project']['project_end_date'] . ' 23:55:59') <= strtotime(date('Y-m-d H:i:s'))) {
                $this->Lend->updateStatus(ConstLendProjectStatus::ProjectExpired, $project['Project']['id']);
            }
        }
    }
    public function daily() 
    {
        App::import('Model', 'Lend.Lend');
        $this->Lend = new Lend();
        // Repayment Notification
        $projects = $this->Lend->find('all', array(
            'conditions' => array(
                'Project.is_draft' => 0,
                'Lend.is_repayment_notified' => 0,
                "Lend.next_repayment_date BETWEEN '" . date('Y-m-d', strtotime("now")) . "' AND '" . date('Y-m-d', strtotime("+ " . Configure::read('lend.repayment_notification_send_before_days') . " day")) . "'",
                'Lend.lend_project_status_id' => array(
                    ConstLendProjectStatus::ProjectAmountRepayment,
                ) ,
            ) ,
            'contain' => array(
                'Project' => array(
                    'ProjectType',
                    'User',
                ) ,
            ) ,
            'recursive' => 2
        ));
        foreach($projects as $project) {
            $this->Lend->postNotifyMail($project, ConstProjectActivities::RepaymentNotification);
            $Data['Lend']['id'] = $project['Lend']['id'];
            $Data['Lend']['is_repayment_notified'] = 1;
            $this->Lend->save($Data['Lend']);
        }
        // Late Repayment Notification
        $latePaymentProjects = $this->Lend->find('all', array(
            'conditions' => array(
                'Project.is_draft' => 0,
                'Lend.is_late_repayment_notified' => 0,
                'Lend.next_repayment_date < ' => date('Y-m-d', strtotime("now")) ,
                'Lend.lend_project_status_id' => array(
                    ConstLendProjectStatus::ProjectAmountRepayment,
                ) ,
            ) ,
            'contain' => array(
                'Project' => array(
                    'ProjectType',
                    'User',
                ) ,
            ) ,
            'recursive' => 2
        ));
        foreach($latePaymentProjects as $project) {
            $this->Lend->postNotifyMail($project, ConstProjectActivities::LateRepaymentNotification);
            $data['Lend']['id'] = $project['Lend']['id'];
            $data['Lend']['is_late_repayment_notified'] = 1;
            $data['Lend']['is_collection'] = 1;
            $data['Lend']['late_repayment_count'] = $project['Lend']['late_repayment_count']+1;
            $data['Lend']['total_arrear_count'] = $project['Lend']['total_arrear_count']+1;
            $this->Lend->save($data);
            $projectData['Project']['id'] = $project['Project']['id'];
            $this->Lend->Project->save($projectData);
            $this->Lend->Project->ProjectFund->updateAll(array(
                'ProjectFund.is_collection' => 1,
            ) , array(
                'ProjectFund.project_id' => $project['Project']['id']
            ));
            $projectFundUsers = $this->Lend->Project->ProjectFund->find('list', array(
                'conditions' => array(
                    'ProjectFund.project_id' => $project['Project']['id']
                ) ,
                'fields' => array(
                    'ProjectFund.id',
                    'ProjectFund.user_id'
                ) ,
                'group' => array(
                    'ProjectFund.user_id',
                    'ProjectFund.id'
                ) ,
                'recursive' => -1,
            ));
            foreach($projectFundUsers as $user_id) {
                $this->Lend->updateUserDetails($user_id);
            }
        }
        // Dafault project fund status updated
        $lateProjects = $this->Lend->find('all', array(
            'conditions' => array(
                'Lend.total_arrear_count' => Configure::read('lend.total_arrear_count_for_default') ,
                'Lend.lend_project_status_id' => array(
                    ConstLendProjectStatus::ProjectAmountRepayment,
                ) ,
            ) ,
            'recursive' => -1
        ));
        foreach($lateProjects as $project) {
            $this->Lend->Project->ProjectFund->updateAll(array(
                'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::DefaultFund,
            ) , array(
                'ProjectFund.project_id' => $project['Lend']['project_id']
            ));
        }
    }
}

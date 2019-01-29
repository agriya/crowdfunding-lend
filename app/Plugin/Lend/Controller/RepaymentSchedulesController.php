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
class RepaymentSchedulesController extends AppController
{
    public $name = 'RepaymentSchedules';
    public function admin_index() 
    {
        $this->pageTitle = __l('Repayment Schedules');
        $conditions = array();
        $this->set('approved', $this->RepaymentSchedule->find('count', array(
            'conditions' => array(
                'RepaymentSchedule.is_approved' => 1
            ) ,
            'recursive' => -1
        )));
        $this->set('pending', $this->RepaymentSchedule->find('count', array(
            'conditions' => array(
                'RepaymentSchedule.is_approved' => 0
            ) ,
            'recursive' => -1
        )));
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['RepaymentSchedule']['filter_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['RepaymentSchedule']['filter_id'])) {
            if ($this->request->data['RepaymentSchedule']['filter_id'] == ConstMoreAction::Active) {
                $conditions['RepaymentSchedule.is_approved'] = 1;
                $this->pageTitle.= ' - ' . __l('Active');
            } else if ($this->request->data['RepaymentSchedule']['filter_id'] == ConstMoreAction::Inactive) {
                $conditions['RepaymentSchedule.is_approved'] = 0;
                $this->pageTitle.= ' - ' . __l('Inactive');
            }
        }
        $this->paginate = array(
            'conditions' => $conditions
        );
        $this->set('repaymentSchedules', $this->paginate());
        $moreActions = $this->RepaymentSchedule->moreActions;
        $this->set('moreActions', $moreActions);
    }
    public function admin_add() 
    {
        $this->pageTitle = __l('Add Repayment Schedule');
        if (!empty($this->request->data)) {
            $this->RepaymentSchedule->create();
            if ($this->RepaymentSchedule->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been added') , __l('Repayment Schedule')) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be added. Please, try again.') , __l('Repayment Schedule')) , 'default', null, 'error');
            }
        }
    }
    public function admin_edit($id = null) 
    {
        $this->pageTitle = __l('Edit Repayment Schedule');
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if (!empty($this->request->data)) {
            if ($this->RepaymentSchedule->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been updated') , __l('Repayment Schedule')) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be updated. Please, try again.') , __l('Repayment Schedule')) , 'default', null, 'error');
            }
        } else {
            $this->request->data = $this->RepaymentSchedule->read(null, $id);
            if (empty($this->request->data)) {
                throw new NotFoundException(__l('Invalid request'));
            }
        }
        $this->pageTitle.= ' - ' . $this->request->data['RepaymentSchedule']['name'];
    }
    public function admin_delete($id = null) 
    {
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if ($this->RepaymentSchedule->delete($id)) {
            $this->Session->setFlash(sprintf(__l('%s deleted') , __l('Repayment Schedule')) , 'default', null, 'success');
            if (!empty($this->request->query['r'])) {
                $this->redirect(Router::url('/', true) . $this->request->query['r']);
            } else {
                $this->redirect(array(
                    'action' => 'index'
                ));
            }
        } else {
            throw new NotFoundException(__l('Invalid request'));
        }
    }
}
?>
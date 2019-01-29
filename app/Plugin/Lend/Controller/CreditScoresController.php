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
class CreditScoresController extends AppController
{
    public $name = 'CreditScores';
    public function admin_index() 
    {
        $this->pageTitle = __l('Credit Scores');
        $conditions = array();
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['CreditScore']['filter_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['CreditScore']['filter_id'])) {
            if ($this->request->data['CreditScore']['filter_id'] == ConstMoreAction::Active) {
                $conditions['CreditScore.is_approved'] = 1;
                $this->pageTitle.= ' - ' . __l('Approved');
            } else if ($this->request->data['CreditScore']['filter_id'] == ConstMoreAction::Inactive) {
                $conditions['CreditScore.is_approved'] = 0;
                $this->pageTitle.= ' - ' . __l('Disapproved');
            }
        }
        $this->paginate = array(
            'conditions' => $conditions,
        );
        $this->set('creditScores', $this->paginate());
        $moreActions = $this->CreditScore->moreActions;
        $this->set('moreActions', $moreActions);
        $this->set('approved', $this->CreditScore->find('count', array(
            'conditions' => array(
                'CreditScore.is_approved' => 1
            )
        )));
        $this->set('pending', $this->CreditScore->find('count', array(
            'conditions' => array(
                'CreditScore.is_approved' => 0
            )
        )));
    }
    public function admin_credit_summary() 
    {
        $this->pageTitle = __l('Credit Scores Summary');
        $credit_scores = $this->CreditScore->find('all', array(
            'fields' => array(
                'CreditScore.id',
                'CreditScore.name',
            ) ,
            'contain' => array(
                'Lend' => array(
                    'conditions' => array(
                        'Lend.lend_project_status_id' => array(
                            ConstLendProjectStatus::ProjectClosed,
                            ConstLendProjectStatus::DefaultProject,
                            ConstLendProjectStatus::ProjectAmountRepayment
                        ) ,
                    ) ,
                    'fields' => array(
                        'Lend.id',
                        'Lend.lend_project_status_id',
                        'Lend.credit_score_id',
                        'Lend.target_interest_rate'
                    ) ,
                    'order' => array(
                        'Lend.project_fund_goal_reached_date' => 'desc'
                    ) ,
                    'limit' => 5
                )
            ) ,
            'recursive' => 1
        ));
        $this->set('credit_scores', $credit_scores);
    }
    public function admin_add() 
    {
        $this->pageTitle = __l('Add Credit Score');
        if (!empty($this->request->data)) {
            $this->CreditScore->create();
            if ($this->CreditScore->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been added') , __l('Credit Score')) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be added. Please, try again.') , __l('Credit Score')) , 'default', null, 'error');
            }
        }
    }
    public function admin_edit($id = null) 
    {
        $this->pageTitle = __l('Edit Credit Score');
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if (!empty($this->request->data)) {
            if ($this->CreditScore->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been updated') , __l('Credit Score')) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be updated. Please, try again.') , __l('Credit Score')) , 'default', null, 'error');
            }
        } else {
            $this->request->data = $this->CreditScore->read(null, $id);
            if (empty($this->request->data)) {
                throw new NotFoundException(__l('Invalid request'));
            }
        }
        $this->pageTitle.= ' - ' . $this->request->data['CreditScore']['name'];
    }
    public function admin_delete($id = null) 
    {
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if ($this->CreditScore->delete($id)) {
            $this->Session->setFlash(sprintf(__l('%s deleted') , __l('Credit Score')) , 'default', null, 'success');
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
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
class LoanTermsController extends AppController
{
    public $name = 'LoanTerms';
    public function admin_index() 
    {
        $this->pageTitle = __l('Loan Terms');
        $conditions = array();
        $this->set('approved', $this->LoanTerm->find('count', array(
            'conditions' => array(
                'LoanTerm.is_approved' => 1
            ) ,
            'recursive' => -1
        )));
        $this->set('pending', $this->LoanTerm->find('count', array(
            'conditions' => array(
                'LoanTerm.is_approved' => 0
            ) ,
            'recursive' => -1
        )));
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['LoanTerm']['filter_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['LoanTerm']['filter_id'])) {
            if ($this->request->data['LoanTerm']['filter_id'] == ConstMoreAction::Active) {
                $conditions['LoanTerm.is_approved'] = 1;
                $this->pageTitle.= ' - ' . __l('Active');
            } else if ($this->request->data['LoanTerm']['filter_id'] == ConstMoreAction::Inactive) {
                $conditions['LoanTerm.is_approved'] = 0;
                $this->pageTitle.= ' - ' . __l('Inactive');
            }
        }
        $this->paginate = array(
            'conditions' => $conditions
        );
        $this->set('loanTerms', $this->paginate());
        $moreActions = $this->LoanTerm->moreActions;
        $this->set('moreActions', $moreActions);
    }
    public function admin_add() 
    {
        $this->pageTitle = __l('Add Loan Term');
        if (!empty($this->request->data)) {
            $this->LoanTerm->create();
            if ($this->LoanTerm->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been added') , __l('Loan Term')) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be added. Please, try again.') , __l('Loan Term')) , 'default', null, 'error');
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
            if ($this->LoanTerm->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been updated') , __l('Loan Term')) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be updated. Please, try again.') , __l('Loan Term')) , 'default', null, 'error');
            }
        } else {
            $this->request->data = $this->LoanTerm->read(null, $id);
            if (empty($this->request->data)) {
                throw new NotFoundException(__l('Invalid request'));
            }
        }
        $this->pageTitle.= ' - ' . $this->request->data['LoanTerm']['name'];
    }
    public function admin_delete($id = null) 
    {
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if ($this->LoanTerm->delete($id)) {
            $this->Session->setFlash(sprintf(__l('%s deleted') , __l('Loan Term')) , 'default', null, 'success');
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
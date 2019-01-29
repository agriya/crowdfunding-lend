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
class LendProjectCategoriesController extends AppController
{
    public $name = 'LendProjectCategories';
    public function index() 
    {
        $this->pageTitle = sprintf(__l('%s %s Categories') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'));
        $this->LendProjectCategory->recursive = 0;
        if (!empty($this->request->params['named']['type']) && $this->request->params['named']['type'] == 'home') {
            $limit = 12;
        }
        $this->paginate = array(
            'fields' => array(
                'LendProjectCategory.name',
                'LendProjectCategory.slug'
            ) ,
            'limit' => $limit,
            'recursive' => -1,
            'order' => array(
                'LendProjectCategory.name' => 'asc'
            )
        );
        $this->set('projectCategories', $this->paginate());
        if (!empty($this->request->params['named']['type']) && $this->request->params['named']['type'] == 'home') {
            $this->autoRender = false;
            $this->render('home');
        }
    }
    public function admin_index() 
    {
        $this->pageTitle = sprintf(__l('%s %s Categories') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'));
        $conditions = array();
        $this->set('approved', $this->LendProjectCategory->find('count', array(
            'conditions' => array(
                'LendProjectCategory.is_approved' => 1
            ) ,
            'recursive' => -1
        )));
        $this->set('pending', $this->LendProjectCategory->find('count', array(
            'conditions' => array(
                'LendProjectCategory.is_approved' => 0
            ) ,
            'recursive' => -1
        )));
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['LendProjectCategory']['filter_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['LendProjectCategory']['filter_id'])) {
            if ($this->request->data['LendProjectCategory']['filter_id'] == ConstMoreAction::Active) {
                $conditions['LendProjectCategory.is_approved'] = 1;
                $this->pageTitle.= ' - ' . __l('Active');
            } else if ($this->request->data['LendProjectCategory']['filter_id'] == ConstMoreAction::Inactive) {
                $conditions['LendProjectCategory.is_approved'] = 0;
                $this->pageTitle.= ' - ' . __l('Inactive');
            }
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array(
                'LendProjectCategory.id' => 'desc'
            ) ,
        );
        $this->set('projectCategories', $this->paginate());
        $moreActions = $this->LendProjectCategory->moreActions;
        $this->set('moreActions', $moreActions);
    }
    public function admin_add() 
    {
        $this->pageTitle = sprintf(__l('Add %s') , sprintf(__l('%s %s Category') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')));
        if (!empty($this->request->data)) {
            $this->LendProjectCategory->create();
            if ($this->LendProjectCategory->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been added') , sprintf(__l('%s %s Category') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'))) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be added. Please, try again.') , sprintf(__l('%s %s Category') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'))) , 'default', null, 'error');
            }
        }
    }
    public function admin_edit($id = null) 
    {
        $this->pageTitle = sprintf(__l('Edit %s') , sprintf(__l('%s %s Category') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')));
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if (!empty($this->request->data)) {
            if ($this->LendProjectCategory->save($this->request->data)) {
                $this->Session->setFlash(sprintf(__l('%s has been updated') , sprintf(__l('%s %s Category') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'))) , 'default', null, 'success');
                $this->redirect(array(
                    'action' => 'index'
                ));
            } else {
                $this->Session->setFlash(sprintf(__l('%s could not be updated. Please, try again.') , sprintf(__l('%s %s Category') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'))) , 'default', null, 'error');
            }
        } else {
            $this->request->data = $this->LendProjectCategory->read(null, $id);
            if (empty($this->request->data)) {
                throw new NotFoundException(__l('Invalid request'));
            }
        }
        $this->pageTitle.= ' - ' . $this->request->data['LendProjectCategory']['name'];
    }
    public function admin_delete($id = null) 
    {
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if ($this->LendProjectCategory->delete($id)) {
            $this->Session->setFlash(sprintf(__l('%s %s Category deleted') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) , 'default', null, 'success');
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
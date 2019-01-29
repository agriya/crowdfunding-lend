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
class LendNamesController extends AppController
{
    public $name = 'LendNames';
    public function index() 
    {
        $this->pageTitle = sprintf(__l('%s Names') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'));
        $this->LendName->recursive = 0;
        $this->paginate = array(
            'conditions' => array(
                'LendName.user_id' => $this->Auth->user('id')
            ) ,
            'order' => array(
                'LendName.id' => 'desc'
            ) ,
            'recursive' => -1,
        );
        $this->set('lendNames', $this->paginate());
    }
}
?>
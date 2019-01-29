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
require_once 'constants.php';
CmsNav::add('Projects', array(
    'title' => 'Projects',
    'url' => array(
        'controller' => 'projects',
        'action' => 'index',
    ) ,
    'data-bootstro-step' => "4",
    'data-bootstro-content' => __l("To monitor the summary, price point statistics of site and also to manage all the projects posted in the site.") ,
    'weight' => 30,
    'icon-class' => 'file',
    'children' => array(
        'Lend Projects' => array(
            'title' => Configure::read('project.alt_name_for_lend_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps') ,
            'url' => array(
                'controller' => 'lends',
                'action' => 'index'
            ) ,
            'weight' => 70,
        ) ,
    ) ,
));
CmsNav::add('masters', array(
    'title' => 'Masters',
    'weight' => 200,
    'children' => array(
        'Lend Projects' => array(
            'title' => Configure::read('project.alt_name_for_lend_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps') ,
            'url' => '',
            'weight' => 800,
        ) ,
        'Lend Project Categories' => array(
            'title' => sprintf(__l('%s %s Categories') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) ,
            'url' => array(
                'controller' => 'lend_project_categories',
                'action' => 'index',
            ) ,
            'weight' => 810,
        ) ,
        'Lend Project Statuses' => array(
            'title' => sprintf(__l('%s %s Statuses') , Configure::read('project.alt_name_for_lend_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) ,
            'url' => array(
                'controller' => 'lend_project_statuses',
                'action' => 'index',
            ) ,
            'weight' => 820,
        ) ,
        'Repayment Schedules' => array(
            'title' => __l('Repayment Schedules') ,
            'url' => array(
                'controller' => 'repayment_schedules',
                'action' => 'index',
            ) ,
            'weight' => 830,
        ) ,
        'Credit Scores' => array(
            'title' => __l('Credit Scores') ,
            'url' => array(
                'controller' => 'credit_scores',
                'action' => 'index',
            ) ,
            'weight' => 840,
        ) ,
        'Loan Terms' => array(
            'title' => __l('Loan Terms') ,
            'url' => array(
                'controller' => 'loan_terms',
                'action' => 'index',
            ) ,
            'weight' => 850,
        ) ,
    )
));
CmsNav::add('payments', array(
    'title' => __l('Payments') ,
    'weight' => 50,
    'children' => array(
        'Projects Funded' => array(
            'title' => __l('Projects Funded') ,
            'url' => '',
            'weight' => 300,
        ) ,
        'Lend Project Funds' => array(
            'title' => sprintf(__l('%s') , Configure::read('project.alt_name_for_lend_plural_caps')) ,
            'url' => array(
                'controller' => 'lends',
                'action' => 'funds',
            ) ,
            'weight' => 340,
        ) ,
    )
));
$defaultModel = array(
    'Transaction' => array(
        'belongsTo' => array(
            'ProjectFundRepayment' => array(
                'className' => 'Lend.ProjectFundRepayment',
                'foreignKey' => 'foreign_id',
                'conditions' => '',
                'fields' => '',
                'order' => '',
            )
        ) ,
    ) ,
    'User' => array(
        'hasMany' => array(
            'ProjectRepayment' => array(
                'className' => 'Lend.ProjectRepayment',
                'foreignKey' => 'user_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
            'ProjectFundRepayment' => array(
                'className' => 'Lend.ProjectFundRepayment',
                'foreignKey' => 'user_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
    ) ,
    'Project' => array(
        'hasOne' => array(
            'Lend' => array(
                'className' => 'Lend.Lend',
                'foreignKey' => 'project_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
            'ProjectRepayment' => array(
                'className' => 'Lend.ProjectRepayment',
                'foreignKey' => 'project_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
    ) ,
    'ProjectFund' => array(
        'hasOne' => array(
            'LendFund' => array(
                'className' => 'Lend.LendFund',
                'foreignKey' => 'project_fund_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
        'hasMany' => array(
            'ProjectFundRepayment' => array(
                'className' => 'Lend.ProjectFundRepayment',
                'foreignKey' => 'project_fund_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
        'belongsTo' => array(
            'LendName' => array(
                'className' => 'LendName',
                'foreignKey' => 'lend_name_id',
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'counterCache' => true,
                'counterScope' => '',
            )
        ) ,
    ) ,
);
CmsHook::bindModel($defaultModel);

<?php
abstract class App_Controller_Action_Crud extends Zend_Controller_Action 
{
	protected $_model;
	protected $_basicForm;
	protected $_listForm;
	protected $_layout = 'admin';
	protected $_add_action_title  = 'Add';
	protected $_edit_action_title = 'Edit';
	
    /**
     * Должен возвращать массив строк для администрирования
     * как правило это все что нужно определять в потомках
     */
    abstract protected function getElements();
	
	public function init() {
		$this->_helper->layout->setLayout($this->_layout);
	}

	public function indexAction()
	{			
    	$elements = $this->getElements();    		
    	$options  = array('rows' => $elements); 	   	
    	
    	$form = new $this->_listForm($options);
    	
    	if ($this->getRequest()->isPost())
		    $this->processGroupForm($form);
 	    
    	$this->view->form     = $form;
    	$this->view->elements = $elements;
    	$this->view->title    = $this->getTitle();    	
	}
	
	public function addAction()
	{					
		$form = new $this->_basicForm();
		if ($this->isSubmit())
		    $this->processForm($form);
		
		$this->view->form  = $form;
		$this->view->title = $this->_add_action_title;
	}
	
	public function editAction()
	{
		$id = (int)$this->getRequest()->getParam('id');
					
		$model = new $this->_model();
		$model->getById($id);		

		$form = new $this->_basicForm();
		$form->setDefaults($model->toArray());
		if ($this->getRequest()->isPost())				
		    $this->processForm($form, $model);
		
		$this->view->form    = $form;
		$this->view->title   = $this->getTitle();
		$this->view->element = $model;
		$this->_helper->viewRenderer('add');										
	}
	
	public function deleteAction()
	{
		$id = (int)$this->getRequest()->getParam('id');		
		$model = new $this->_model();
		$model->getById($id);		
		$model->delete();
		$this->_helper->redirector('index');
	}
	
	protected function processGroupForm($form)
	{		
		App_Stat::set('processGroupForm start');
		$id   = $this->getRequest()->getParam('id');
    	$post = $this->getRequest()->getPost();    	
    	if ($form->isValid($post)) {
    		foreach ($post as $key => $val) {
    			if (!is_numeric($key))
    				continue;
    			$model = new $this->_model();
    			$model->getById($key);
    			$model->setOptions($val);
    			$model->save();
    		}    		
    		$this->_helper->redirector->gotoRoute(array('action' => 'index')); // Save All Get Request Params
    	}
    	App_Stat::set('processGroupForm end');
	}
	
	protected function processForm($form, $model = null) {		   	   		
    	$data = $this->getRequest()->getPost();
    	if ($form->isValid($data)) {
    		if (empty($model))
    		    $model = new $this->_model();
    		$model->setOptions($data);
    		$model->save();    			
    		$this->_helper->redirector->gotoRoute(array('action' => 'index')); // Save All Get Request Params
    	}
	}
	
    /**
     * Возвращает title текущей страницы
     */
    protected function getTitle() 
    {
    	$navigation = App_Resource::get('navigation');
    	$current = $this->view->navigation()->findActive($navigation);    	
    	if (!isset($current['page']))
    		return 'Page Name Not Found';
    	return $current['page']->getLabel();
    }    

	/**
	 * Для использования например совместно с facebook
	 * (так как у него всегда post)
	 */
	protected function isSubmit() {
		return $this->getRequest()->isPost() 
		           && ($this->_getParam('submit') || $this->_getParam('save'));
	}
}
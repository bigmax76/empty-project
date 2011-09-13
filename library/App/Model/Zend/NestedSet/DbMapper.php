<?php
class Model_Category_DbMapper extends App_Model_Abstract_Mapper
{	
    protected $_dbTableName = 'App_Model_Zend_NestedSet_DbTable';	
	
    protected function getOptions(App_Model_Abstract $model)
    {
   		$data = array(               
         	'id'                 => $model->getId(),
   		    'name'       	     => $model->getName(),
   		    'parent_id'          => $model->getParent_id(),
   		    'left_key'           => $model->getLeft_key(),
   		    'right_key'          => $model->getRight_key(),
   			'level'              => $model->getLevel(),  
   		    'sort'               => $model->getSort(),
        );   
        return $data;  
    }
    
    protected function setOptions(App_Model_Abstract $model, $data)
    { 
   		$model->setId                ($data->id)
   		      ->setParent_id         ($data->parent_id)
   		      ->setName              ($data->name)
   		      ->setLeft_key          ($data->left_key)
   		      ->setRight_key         ($data->right_key)
   		      ->setLevel             ($data->level)
   		      ->setSort              ($data->sort);
    }
}
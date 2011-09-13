<?php
class App_PPage_Invoice_AbstractDbMapper extends App_Model_Mapper_Abstract
{
	
    protected $_dbTableName = 'App_PPage_Invoice_AbstractDbTable';	
	
    protected function getOptions(App_Model_Abstract $shop)
    {
   		$data = array(               
         	'id'              => $shop->getId(),
            'user_id'         => $shop->getUser_id(),    	 
            'ppage_id'        => $shop->getPpage_id(),
            'amount'          => $shop->getAmount(),          
            'payment_type'    => $shop->getPayment_type(),
   		    'payment_info'    => $shop->getPayment_info(),
            'is_paid'         => $shop->getIs_paid(),    	 
            'comments'        => $shop->getComments(),
            'created_ts'      => $shop->getCreated_ts(), 
   		    'paid_ts'         => $shop->getPaid_ts(),   	
        );   
        return $data;  
    }
    
    protected function setOptions(App_Model_Abstract $shop, $data)
    {
   		$shop->setId          ($data->id)
   		     ->setUser_id     ($data->user_id)    	 
             ->setPpage_id    ($data->ppage_id)
             ->setAmount      ($data->amount)          
             ->setPayment_type($data->payment_type)
   		     ->setPayment_info($data->payment_info)
             ->setIs_paid     ($data->is_paid)    	 
             ->setComments    ($data->comments)
             ->setCreated_ts  ($data->created_ts) 
   		     ->setPaid_ts     ($data->paid_ts); 
             }
}
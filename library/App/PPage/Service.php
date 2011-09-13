<?php
/**
 * ��������� �������, ���������� ������ ������, 
 * ��������� � ������������� ���������� ��������� � ����  
 * @author BigMax
 *
 */
class App_PPage_Service
{	
	
	
	/**
	 * ������� ���������� ������ App_Model_Shop �� ��� code
	 * @param unknown_type $id
	 * @return unknown_type
	 */
    static function getByCode($code)
	{		
		$db = new App_Model_DbTable_Shop();
		$select = $db->select()->where('code = ?' , $code); 		
		$result = $db->fetchAll($select)->toArray();
	    if (0 == count($result)) {
            return;
        }
		$shop = new App_Model_Shop($result[0]);
		return $shop;
	}
	
	
	
	/**
     * ���������� ������ �������� App_Model_Shop ������������� ������������ $user_id
     * @param unknown_type $user_id
     * @return unknown
     */
    static function getCollectionByUser($user_id)
    {
        $db = new Model_DbTable_Shop();
		$select = $db->select()->where('user_id = ?' , $user_id); 		
		$results = $db->fetchAll($select)->toArray();
        if (0 == count($results)) {
            return;
        }
	    foreach ($results as $result)
	    {
	    	$arShops[] = new Model_Shop($result);	    			   
		}    				
		return $arShops;
    }
    
}
<?php
/**
 * Класс позволяющий помечать и голосовать за записи 
 */
class App_Vote
{	
	protected $_user_class      = 'Model_User'; // класс отвечающий за пользователя
	protected $_node_class      = 'Model_Node'; // класс отвечающий за голосуемую запись 	
	protected $_dbTableName     = 'app_votes';  // таблица данных голосований
	protected $_dbTableNameFlag = 'app_flag';   // таблица пометок	
	
	protected $_dbTable         = null;		
	
	protected $user = null;
	protected $node = null;
	
	// options
	protected $user_id;
	protected $node_id;
	protected $action_id;
	
	// config
	protected $rep_to_vote_up   = 0;    
	protected $rep_to_vote_down = 0;
	protected $rep_to_flag      = 0;
	
	// результат
	protected $result = array(
		'success'   => 'false',
		'new_score' => 0,
		'message'   => 'Hеизвестная ошибка',
	);
	
    public function vote($user_id = null, $node_id = null, $action_id = null)
	{		
		$this->user_id   = $user_id;
		$this->node_id   = $node_id;
		$this->action_id = $action_id;

		if (!$this->validate())
			return $this->result;
	
	    $shift = $this->getVoteShift();
		$node  = $this->getNode();
		$node->votes  = $node->votes  + $shift;   
		$node->marked = $node->marked + $this->getMarkedShift();			
		$node->save();	
		
		// подсчет ответов с голосом вверх
		if ($node->type == 'answer')
		{			
			$shift = false;
			if ($node->votes == 1  && $action_id == App_Vote_Action::$Up){
			    $shift = 1;			    
			}
			if ($node->votes == 1  && $action_id == App_Vote_Action::$DownDisable){
			    $shift = 1;			   
			}
			if ($node->votes == 0  && $action_id == App_Vote_Action::$Down){
			    $shift = -1;
			}
			if ($node->votes == 0  && $action_id == App_Vote_Action::$UpDisable) {
			    $shift = -1;
			}
				
			if ($shift !== false) {						
				$question_id = $node->parent_id;
			    $question = new Model_Node();
			    $question->getById($question_id);
			    $question->answers_up_cnt = $question->answers_up_cnt + $shift;			   
			    $question->save();
			}		
		}
		
		$action = $this->getLastAction();
		$action->action_id = $action_id;
		$action->action_ts = date('Y-m-d H:i:s');
		$action->save();
		
		$result['new_score'] = ($this->is_flag_action()) ? $node->marked : $node->votes;
		$result['success']   = true;
		$result['message']   = 'Success';		
		
		if ($action_id == App_Vote_Action::$Up)
			$event_id = Model_EventSystem::VOTE_UP;
			
		if ($action_id == App_Vote_Action::$DownDisable)
			$event_id = Model_EventSystem::VOTE_DOWN_DISABLE;
			
		if ($action_id == App_Vote_Action::$Down)
			$event_id = Model_EventSystem::VOTE_DOWN;
			
		if ($action_id == App_Vote_Action::$UpDisable)
			$event_id = Model_EventSystem::VOTE_UP_DISABLE;
			
		if ($action_id == App_Vote_Action::$Flag)
			$event_id = Model_EventSystem::FAVORITE;
			
		if ($action_id == App_Vote_Action::$FlagDisable)
			$event_id = Model_EventSystem::FAVORITE_DISABLE;	
		
		$dispatcher = Model_EventSystem_Dispatcher::getInstance();
		$event = new sfEvent(null, $event_id, array('node' => $node));
		$dispatcher->notify($event);
		
		return $result;
			
	}
	
	protected function validate()
	{
		if (empty($this->node_id) || empty($this->action_id)) {
			$this->result['message'] = 'Empty Node_id or Action_Id'; 
			return false;
		}
		
	    $user = $this->getUser();		
		if (!$user->isAuthorized()) {
			$this->result['message'] = 'Please login or register to vote for this post.';
			return false;		
		}
		
	    $node = $this->getNode();	   	
		if ($node->id == null) {
			$this->result['message'] = 'Post not found';
			return false;	
		}
		
	    $last_action = $this->getLastAction();
		if ($this->action_id == $last_action->action_id) {
			$this->result['message'] = 'Действие уже применено!';
			return false;	
		}
		
	    if ($this->action_id == App_Vote_Action::$Up) {
		    if(REP_TO_VOTE_UP > $this->getUserRep()){
				$this->result['message'] = 'Vote Up requires ' . REP_TO_VOTE_UP  . ' reputation';
				return false;				
			}			
		}
		
	    if ($this->action_id == App_Vote_Action::$Down) {
		    if(REP_TO_VOTE_DOWN > $this->getUserRep()){
				$this->result['message'] = 'Vote Down requires ' . REP_TO_VOTE_DOWN  . ' reputation';
				return false;				
			}			
		}
		
	    if ($this->action_id == App_Vote_Action::$Flag) {
		    if(REP_TO_FLAG > $this->getUserRep()){
				$this->result['message'] = 'Flag requires ' . REP_TO_FLAG  . ' reputation';
				return false;				
			}			
		}
		
		return true;
	}
		
	protected function getUser() 
	{
		if (null == $this->user) {
			$user = new $this->_user_class();
			$user->getById($this->user_id);
			$this->user = $user;
		}
		return $this->user;
	}
	
    protected function getNode()
	{
		if (null == $this->node) {
			$node = new $this->_node_class();
			$node->getById($this->node_id);
			$this->node = $node;
		}
		return $this->node;
	}
	
	protected function getUserRep() {		
		return $this->getUser()->reputation;
	}
	
    protected function getLastAction()
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->where('user_id = ?', $this->user_id)
			   ->where('node_id = ?', $this->node_id);
	    $row = $table->fetchRow($select);
	    if (null === $row) {
	    	$row = $table->createRow();
	    	$row->user_id   = $this->user_id;
	    	$row->node_id   = $this->node_id;	    	
	        $row->action_id = App_Vote_Action::$None;
	        $row->action_ts = date('Y-m-d H:i:s');	        
	    }
	    return $row;	   
	}
	
	protected function getVoteShift()
	{
		$last_action = $this->getLastAction();
		$shift = 0;
		
		if ($this->action_id == App_Vote_Action::$Up)		    
			$shift = ($last_action->action_id == App_Vote_Action::$Down) ? 2 : 1;
			
		if ($this->action_id == App_Vote_Action::$Down)		    
			$shift = ($last_action->action_id == App_Vote_Action::$Up) ? -2 : -1;
				
		if ($this->action_id == App_Vote_Action::$UpDisable)
			$shift = -1;
		
		if ($this->action_id == App_Vote_Action::$DownDisable)
			$shift = 1;
			
		return $shift;		
	}

	protected function getMarkedShift()
	{
		$last_action = $this->getLastAction();
		$shift = 0;
		
		if ($this->action_id == App_Vote_Action::$Flag)		    
			$shift = 1;
			
		if ($this->action_id == App_Vote_Action::$FlagDisable)		    
			$shift = -1;
			
		return $shift;		
	}
	
	protected function getDbTable()
    {
        if (null === $this->_dbTable) {             	
        	$table_name = ($this->is_flag_action()) 
        				?  $this->_dbTableNameFlag 
        				:  $this->_dbTableName;
            $this->setDbTable($table_name);
        }
        return $this->_dbTable;
    }
      
    protected function setDbTable($dbTable)
    {       	
        if (is_string($dbTable)) {
        	$dbTable = new Zend_Db_Table(array('name' => $dbTable));
            //$dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    protected function is_flag_action()
    {
    	$action_flag = array(
        		App_Vote_Action::$Flag, 
        		App_Vote_Action::$FlagDisable
        );
        if (in_array($this->action_id, $action_flag))
        	return true;
        	
        return false;
    }
    
}
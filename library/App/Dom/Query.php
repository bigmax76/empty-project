<?php
/**
 * Каждый запрос Zend_Dom_Query происходит над оригиналом документа.
 * App_Dom_Query позволяет проводить запросы над документом который 
 * подвергался манипуляции (например removeChild)
 * @author bigmax
 */
class App_Dom_Query extends Zend_Dom_Query
{
	protected $_domDoc   = null;
	protected $_success = null;
	
	protected function getDomDoc() 
	{
		if (null === $this->_domDoc) 
		{
		    $encoding = $this->getEncoding();	        
	        if (null === $encoding) {
	            $domDoc = new DOMDocument('1.0');
	        } else {
	            $domDoc = new DOMDocument('1.0', $encoding);
	        }
	        $this->_domDoc = $domDoc;
		}
		return $this->_domDoc;	
	}		
	
	protected function getSuccess()
	{
		if (null === $this->_success) 
		{		
			if (null === ($document = $this->getDocument())) {
	            //require_once 'Zend/Dom/Exception.php';
	            throw new Zend_Dom_Exception('Cannot query; no document registered');
	        }
        	
	        $domDoc = $this->getDomDoc();
	        $type   = $this->getDocumentType();
	        switch ($type) {
	            case self::DOC_XML:
	                $success = $domDoc->loadXML($document);
	                break;
	            case self::DOC_HTML:
	            case self::DOC_XHTML:
	            default:
	                $success = $domDoc->loadHTML($document);
	                break;
	        }
	        $this->_success = $success;	        
		}
		return $this->_success;
	}

	public function queryXpath($xpathQuery, $query = null)
    {        
        libxml_use_internal_errors(true);
                
        $success = $this->getSuccess();
        
        $errors = libxml_get_errors();
	        if (!empty($errors)) {
	            $this->_documentErrors = $errors;
	            libxml_clear_errors();
	        }
	    libxml_use_internal_errors(false);

        if (!$success) {
            //require_once 'Zend/Dom/Exception.php';
            throw new Zend_Dom_Exception(sprintf('Error parsing document (type == %s)', $type));
        }

        $nodeList   = $this->_getNodeList($this->_domDoc, $xpathQuery);
        return new Zend_Dom_Query_Result($query, $xpathQuery, $this->_domDoc, $nodeList);
    }
}

<?php
class App_View_Filter_Translate implements Zend_Filter_Interface
{
    const I18N_DELIMITER_START = '<i18n>';
    const I18N_DELIMITER_END   = '</i18n>';
    /**
     * Attribute name
     * Value is used for replacing content with vsprintf
     */
    const REPLACEMENT_ATTR = 'replacement';
    /**
     * If there is more than one value to replace, delimite them with this string
     */
    const REPLACEMENT_ATTR_DELIMITER = ',';
    
    public function filter($value) {
        $startDelimiterLength = strlen(self::I18N_DELIMITER_START);
        $endDelimiterLength   = strlen(self::I18N_DELIMITER_END);
        
        $translator = Zend_Registry::get('Zend_Translate');
        
        $delimiterStart = substr(self::I18N_DELIMITER_START, 0, -1);

        $offset = 0;
        while (($posStart = strpos($value, $delimiterStart, $offset)) !== false) {
            $offset = $posStart + $startDelimiterLength;
            
            // check for an tag ending '>'
            $posTagEnd = strpos($value, '>', $offset - 1);
            $formatValues = null;
            // if '<i18n' is not followed by char '>' directly, then we obviously have attributes in our tag 
            if ($posTagEnd - $posStart + 1 > $startDelimiterLength) {
                $format = substr($value, $offset, $posTagEnd - $offset); 
                $matches = array();
                // check for value of 'format' attribute and explode it into $formatValues
                preg_match('/' . self::REPLACEMENT_ATTR . '="([^"]*)"/', $format, $matches);
                if (isset($matches[1])) $formatValues = explode(self::REPLACEMENT_ATTR_DELIMITER, $matches[1]);
                $offset = $posTagEnd + 1;
            }
            
            if (($posEnd = strpos($value, self::I18N_DELIMITER_END, $offset)) === false) {
                throw new Exception("No ending tag after position [$offset] found!");        
            }
            $translate = substr($value, $offset, $posEnd - $offset);
           
            $translate = $translator->_($translate);
            if (is_array($formatValues)) $translate = vsprintf($translate, $formatValues);

            $offset = $posEnd + $endDelimiterLength;
            $value = substr_replace($value, $translate, $posStart, $offset - $posStart);
            $offset = $offset - $startDelimiterLength - $endDelimiterLength;
        }

        return $value;    
    }
}
<?php
/**
 * Таблица для хранения информации о пополненинии счетов персональных страниц
 * @author таргет
 *
 */
class App_PPage_Invoice_AbstractDbTable extends Zend_Db_Table_Abstract {
  protected $_name = 'ppage_invoices';
  protected $_primary = 'id';
}
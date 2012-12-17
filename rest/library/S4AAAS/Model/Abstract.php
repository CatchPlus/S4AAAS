<?php

/**
 * Abstract Model, which manages stuff like 
 * 
 * @author Herbert Kruitbosch
 */
abstract class S4AAAS_Model_Abstract {
    private $_dao=null;
    
    protected static function plainSelect($dao) {
        $select = $dao->select()
                ->from($dao->getName())
                ;
        return $select;
    }
    
    protected static function fetchByField($dao, $fieldName, $fieldValue, $result) {
        $select = self::plainSelect($dao)
                ->where($fieldName . ' = ?', $fieldValue);
		
		$rows = $dao->fetchAll($select);
        
        if(count($rows) > 0) {
            $result->populate($rows[0]->toArray());
            return $result;
        }

        return null;
    }
	
	// @TODO test!!
    protected static function fetchManyByField($dao, $fieldName, $fieldValue, $classType) {
        $select = self::plainSelect($dao)
                ->where($fieldName . ' = ?', $fieldValue);
		
		$rows = $dao->fetchAll($select);
        
        if(count($rows) == 0)
		{
			return null;
		}
		
		$result = array();
		foreach ($rows as $row)
		{
			$result[] = new $classType($row->toArray());
		}
		
        return $result;
    }
	
	public function update()
	{		
        $dao = $this->dao();
        $where='';
        foreach($this->_magicPrimary as $pkey)
		{
            if($where!=='')
			{
				$where = $where . ' AND ';
			}
			
            $where .= $dao->getAdapter()->quoteInto($pkey . ' = ?', $this->_magicProperties[$pkey]);
        }

        $dao->update($this->_magicProperties, $where);
    }
	
    public function prefixedFields($alias, $prefix) {
        $result = array();
        foreach($this->_magicProperties as $key => $value) {
            $result[$prefix . $key] = $alias . '.' . $key;
        }
        return $result;
    }
    
    public function insert() {
        $dao = $this->dao();
        $primaryKey = $dao->insert($this->_magicProperties);
		//print_r($this->dao());
        //die;
		return $primaryKey;
    }
    
    public function dao() {
        if($this->_dao===null) $this->_dao = new Zend_Db_Table(array(
            'name' => $this->_magicName,
            'primary' => $this->_magicPrimary,
            'sequence' => false
        ));
        return $this->_dao;
    }
    
    protected function populate($values, $prefix='') {
        foreach($this->_magicProperties as $key => $value) {
            $this->_magicProperties[$key] = $values[$prefix . $key];
        }
    }
    
    public function __call($method, $parameters) {
		//for this to be a setSomething or getSomething, the name has to have 
        //at least 4 chars as in, setX or getX
        if (strlen($method) < 4)
            throw new Exception('Method does not exist');

        //take first 3 chars to determine if this is a get or set
        $prefix = substr($method, 0, 3);

        //take last chars and convert first char to lower to get required property
        $suffix = substr($method, 3);
        $suffix[0] = strtolower($suffix[0]);

        if ($prefix == 'get') {
            if ($this->_hasProperty($suffix) && count($parameters) == 0)
                return $this->_magicProperties[$this->_toProperty($suffix)];
            else
                throw new Exception('Getter does not exist');
        }

        if ($prefix == 'set') {
            if ($this->_hasProperty($suffix) && count($parameters) == 1)
                $this->_magicProperties[$this->_toProperty($suffix)] = $parameters[0];
            else
                throw new Exception('Setter does not exist');
        }
    }
    
    private function _toProperty($name) {
        $name = preg_replace('/\B([A-Z])/', '_$1', $name);
        return strtoupper($name); 
    }

    private function _hasProperty($name) {
        return array_key_exists($this->_toProperty($name), ($this->_magicProperties));
    }
    
    public function collect($rows, $classname, $prefix, $from=0, $to = -1) {
        if($to==-1) $to=count($rows);
        $result = array();
        $object = new $classname;
        for($i=$from; $i<$to; $i++) {
            $values = $rows[$i];
            if($object->_magicProperties[$object->_magicPrimary[0]] !== $values[$prefix . $object->_magicPrimary[0]]) {
                if($object->getId()!=null) {
                    $object->to=$i;
                    $result[]=$object;
                    $object = new $classname;
                }
                $object->populate($values, $prefix);
                $object->from = $i;
            }
        }
        if($object->getId()!=null) {
            $object->to=$i;
            $result[]=$object;
        }
        return $result;
    }
    
}

?>
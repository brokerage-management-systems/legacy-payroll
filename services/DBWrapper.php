<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Broker Structure"; //pagetitle
$SITEVARS[4] = "user"; //user access
$SITEVARS[5] = "payrollData"; //permission access

require_once '../includes/config.php';
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
    $init->printMessage("accessRights");
    exit();
}
?>

<?php
class DbWrapper
{
    private $dbh;
    
    public function __construct($dbh, $table)
    {
        $this->dbh = $dbh;
        $this->table = $table;
    }
    
    private function _parseParams($params)
    {

    }
    
    public function executeQuery($params)
    {
        //var_dump($params);
        $query = '';
        foreach(array_keys($params) as $key) {
            if ($key === 'select') {
                $query .= strtoupper($key).' ';
                $query .= join($params[$key], ',');
            } else if ($key === 'insert') {
            } else if ($key === 'update') {
            } else if ($key === 'delete') {
            }
            
            if ($key === 'values') {
                
            }
            
            if ($key === 'where') {
                $query .= ' '.strtoupper($key).' ';
                $query .= join($params[$key], ' AND ');
            }
            
            if ($key === 'order+by') {
                $query .= ' '.strtoupper($key).' ';
                $query .= join($params[$key], ' AND ');
            }
            
            if ($key === 'limit') {
                $query .= ' '.strtoupper($key).' ';
                $query .= join($params[$key], ',');
            }
        }
        echo $query;
        exit;
        // switch () {
        //     
        // }
        
        // $insert
        // $select
        // $update
        // $delete
        // 
        // $from
        // $into
        // 
        // $join
        // 
        // $where
        // $limit
        // $order
        
    }
    
    public function get($id)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE id=:id';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(':id' => $id));
        return $sth->fetchObject();
    }
    
    public function getAll($options = array())
    {
        $sql = 'SELECT * FROM '.$this->table;
        if (isset($options->{'limit'})) {
            $sql .= ' LIMIT '.$this->dbh->quote($options->{'limit'}, PDO::PARAM_INT);
        }
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_CLASS);
    }

    public function getByFields($values)
    {
        //return var_dump($values);
        $binds = array();
        $bindnames = array();
        foreach (array_keys($values) as $key) {
            $key = $this->clean($key);
            $binds[":$key"] = $values[$key];
            $bindnames[] = "$key=:$key";
        }
        $bindnames = join($bindnames, ' AND ');
        $sql = 'SELECT * FROM '.$this->table." WHERE $bindnames";
        //return var_dump($sql);
        $sth = $this->dbh->prepare($sql);
        $sth->execute($binds);
        return $sth->fetchAll(PDO::FETCH_CLASS);
    }
    
    /**
     *
     *
     */
    public function getByJsonSQL($json)
    {
        return false;
        $decoded = base64_decode($json);
        $json = json_decode($decoded,true);
        //return var_dump($json);
        return false;
        $binds = array();
        $bindnames = array();
        foreach (array_keys($values) as $key) {
            $key = $this->clean($key);
            $binds[":$key"] = $values[$key];
            $bindnames[] = "$key=:$key";
        }
        $bindnames = join($bindnames, ' AND ');
        $sql = 'SELECT * FROM '.$this->table." WHERE $bindnames";
        //return var_dump($sql);
        $sth = $this->dbh->prepare($sql);
        $sth->execute($binds);
        return $sth->fetchAll(PDO::FETCH_CLASS);
    }
    
    public function getByRawSQL($sql)
    {
        return false;
        $sql = $this->clean($sql);
        //return var_dump($sql);
        $sth = $this->dbh->prepare($sql);
        $sth->execute($binds);
        return $sth->fetchAll(PDO::FETCH_CLASS);
    }
    
    public function delete($id)
    {
        $sql = 'DELETE FROM '.$this->table.' WHERE id=:id';
        $sth = $this->dbh->prepare($sql);
        return $sth->execute(array(':id' => $id));
    }
    
    public function update($id,$values)
    {
        $binds = array(':id' => $id);
        $bindnames = array();
        foreach (array_keys($values) as $key) {
            $key = $this->clean($key);
            $binds[":$key"] = $values[$key];
            $bindnames[] = "$key=:$key";
        }
        $bindnames = join($bindnames, ',');
        if ($this->table === 'Brokers') {
            $sql = 'UPDATE '.$this->table." SET $bindnames WHERE userid=:id";
        } else {
            $sql = 'UPDATE '.$this->table." SET $bindnames WHERE id=:id";
        }
        $sth = $this->dbh->prepare($sql);
        return $sth->execute($binds);
    }
    
    public function insert($values)
    {
        $keys = array();
        $binds = array();
        $bindnames = array();
        foreach (array_keys($values) as $key) {
            $key = $this->clean($key);
            $keys[] = $key;
            $binds[":$key"] = $values[$key];
            $bindnames[] = ":$key";
        }
        $keys = join($keys, ',');
        $bindnames = join($bindnames, ',');
        $sql = 'INSERT INTO '.$this->table." ($keys) VALUES ($bindnames)";
        $sth = $this->dbh->prepare($sql);
        $sth->execute($binds);
        return $this->dbh->lastInsertId();
    }
    
    private function clean($key)
    {
        return preg_replace('[^A-Za-z0-9_]', '', $key);
    }
    
}

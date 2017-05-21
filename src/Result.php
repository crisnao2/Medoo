<?php
namespace Medoo;

/**
 * Class Result to Medoo database framework
 *
 * @package Medoo
 * @author Cristiano Soares <crisnao2@yahoo.com.br>
 * @link http://medoo.in
 * @version 1.2.2
 * @copyright 2017, package root by Angel Lai
 * @license Released under the MIT license
 */

use PDO;
use \Medoo\Medoo;

class Result Extends Medoo
{
	private $statement = null;

	public function __construct($options = null)
    {
		parent::__construct($options);

		$this->pdo->exec("SET NAMES 'utf8'");
		$this->pdo->exec("SET CHARACTER SET utf8");
		$this->pdo->exec("SET CHARACTER_SET_CONNECTION=utf8");
		$this->pdo->exec("SET SQL_MODE = ''");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}	

    /**
    * Overwrite the query method in order to implement a query result already
    *
    * @param string $sql
    * @param array $map
    *
    * @return boolean or object
    */
	public function query($sql, $map = [])
    {
        if (!preg_match('#^(select|show) #i', $sql)) {
            return parent::query($sql, $map);
        }

		$this->statement = $this->pdo->prepare($sql);
        $result = new \stdClass();
        $result->row = array();
        $result->rows = array();
        $result->num_rows = 0;

		if ($this->statement->execute()) {
			$data = array();
			$i = 0;

			while ($row = $this->statement->fetch(PDO::FETCH_ASSOC)) {
				$data[] = $row;

				$i++;
			}

			$result = new \stdClass();
			$result->row = (isset($data[0]) ? $data[0] : array());
			$result->rows = $data;
			$result->num_rows = $i;

			unset($data);
		}

		return $result;
	}

    /**
    * Returns the number of records affected by the last query
    *
    * @return integer
    */
	public function countAffected()
    {
		if ($this->statement) {
			return $this->statement->rowCount();
		} else {
			return 0;
		}
	}

    /**
    * Returns the id of the last record entered
    *
    * @return integer
    */
    public function getLastId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
    * Destroy PDO reference
    *
    * @return void
    */
	public function __destruct()
    {
		$this->pdo = null;
	}	
}

?>
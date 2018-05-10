<?php
//include "Database.php";
class Source {
	protected $name;
	protected $id;

	// need a constructor that sets the info with a given name
	// there is probably a clever OO way that I am supposed to do this
	// with a name or an id!
	function __construct($name=null) {
/*		if ($name) {
			$db = new Database();
			$db->select('sources', 'id', null, $where = "name like '%$name%'");
			$res = $db->getResult();
			$this->id = $res[0]['id'];
			$this->name = $name;
			// need to make this add a row if the source isn't found and set it.
		}
	*/
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function findId($db) {
		$db->select('sources', 'id', $where = "name = '$name'");
	}
	public function getId() {
		return $this->id;
	}
}
?>
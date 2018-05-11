<?php
class Document {
	protected $title;
	protected $location;
	protected $source;
	protected $identifier;
	protected $created;
	protected $modified;

	function setTitle($title) {
		$this->title = $title;
	}
}
?>
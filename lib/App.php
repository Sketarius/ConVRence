<?php
	require_once('Login.php');
	require_once('Cgi.php');
	require_once('Html.php');

	class App
	{
		private $cgi;
		private $html;
		private $login;

		public function __construct() {
			$this->cgi = new Cgi();
			$this->html = new HTML();
			$this->login = new Login();
		}

		public function getCgi() {
			return $this->cgi;
		}

		public function getHTML() {
			return $this->html;
		}

		public function getLogin() {
			return $this->login;
		}

		public function __sleep() {
  		  return array_keys( get_object_vars( $this ) );
		}

	}

?>
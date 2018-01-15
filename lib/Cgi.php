<?php
	/* 
		Cgi.php
		Written By: Shane Freeman
		Handles all methods of passing information through post and get.
	*/
	class Cgi
	{
		private $debug_mode = false;

		public function getValue($value) {
			if(isset($_POST[$value])) {
				return $_POST[$value];
			}
			else if(isset($_GET[$value])) {
				return $_GET[$value];
			}
			else {
				return NULL;
			}
		}

		public function setValue($key, $value, $method) {
			
			if(strtolower($method) == "post" || strtolower($method) == 'p') {
				$_POST[$key] = $value;
				return true;
			}
			else if(strtolower($method) == "get" || strtolower($method) == 'g') {
				$_GET[$key] = $value;
				return true;
			}
			else {
				return false;
			}
		}

		private function printDebugInfo() {
			if(sizeof($_GET) > 0) {
				echo "GET Variables: <br />";
				echo "=============== <br />";
				foreach ($_GET as $key => $value){
	        		echo "$key => $value <br />"; 
	    		} 
	    	}

	    	if(sizeof($_POST) > 0) {
	    		echo "<br /><br />POST Variables: <br />";
	    		echo "=============== <br />";
				foreach ($_POST as $key => $value){
	        		echo "$key => $value <br />"; 
	    		}
	    	}
		}

		public function setDebugModeOn() {
			$this->debug_mode = true;
			$this->printDebugInfo();
		}

		public function setDebugModeOff() {
			$this->debug_mode = false;
		}

		public function debugMode() {
			return $this->debug_mode;
		}

	}
?>
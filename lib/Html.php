<?php
	class HTML
	{
		private $pageTitle;
		private $progName;

		private $scripts = array();
		private $css = array();

		public function __construct() {
			
		}

		public function setPageTitle($pageTitle) {
			$this->pageTitle = $pageTitle;
		}

		public function addScript($script_path) {
			array_push($this->scripts, $script_path);
		}

		public function addCSS($css_path) {
			array_push($this->css, $css_path);
		}

		public function displayHeader() {
			echo "<!DOCTYPE html>\n";
			echo "<html>\n";
			echo "<head> <title>" . $this->pageTitle . "</title>\n";

			if(sizeof($this->css) > 0) {
				for($i = 0; $i < sizeof($this->css); $i++) {
					echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->css[$i] ."\">\n";
				}
			}

			if(sizeof($this->scripts) > 0) {
				for($i = 0; $i < sizeof($this->scripts); $i++) {
					echo "<script src=\"" . $this->scripts[$i] . "\"></script>\n";
				}
			}

			echo "</head>\n";
			echo "<body>\n";
		}

		public function displayFooter() {
			echo "</body>\n";
			echo "</html>\n";
		}

		public static function getProgName($full_name) {
			$ret_name = "";
			for($i = strlen($full_name) - 1; $full_name[$i] != '/'; $i--) {
				$ret_name = $full_name[$i] . $ret_name;
			}

			return $ret_name;
		}

		public function beginForm($method, $action) {
			echo "<form action=\"" . $action . "\" method=\"" . $method . "\">\n";
		}

		public function endForm() {
			echo "</form>\n";
		}

		public function inputItem($id, $input_type, $value) {
			echo "<input name = \"" . $id . "\" id = \"" . $id . "\" type=\"" . $input_type . "\" value=\"" . $value . "\">\n";
		}

	}
?>
<?php

class Login
{
	private $mysqli;
	private $debug=false;
	private $loggedIn=false;
	private $username;
	
	private function connectToDatabase() {
		$this->mysqli = new mysqli('localhost', 'convrence', 'oculus321', 'convrence');
		
		if($this->mysqli->connect_errno > 0) {
			die("Unable to connect to database[" . $this->mysqli . "]");		
		}
	}

	public function doLogin($username, $password) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT user_id FROM users WHERE username='$username' AND  password='$password';");
		if($result->num_rows > 0) {
			$this->loggedIn = true;
			$this->username = $username;
			$this->closeConnection();
			return 1;
		} else {
			$this->loggedIn = false;
			$this->closeConnection();
			return 0;
		}
		
	}

	public function createLogin($username, $password, $email, $fname, $lname) {
		$this->connectToDatabase();
		if($this->mysqli->query("INSERT INTO users(username, password, email, fname, lname) " . 
					" VALUES('$username', '$password', '$email', '$fname', '$lname');")) {
			$this->closeConnection();
			return 1;
		} else {
			if($debug) {
				echo $this->mysqli->error;
			}
			$this->closeConnection();
			return 0;
		}
	}

	public function createRoom($roomName, $userName) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT user_id FROM users WHERE username='$userName';");
		if($result->num_rows > 0) {
			// Fetch the user_id
			$row = $result->fetch_row();
			$user_id = $row[0];

			// Need to get the next auto_increment conf_id
			$result = $this->mysqli->query("SHOW TABLE STATUS LIKE 'conferences'");
			$row = $result->fetch_array();
			$nextId = $row['Auto_increment'];

			// Insert new instance of conference into database
			if($this->mysqli->query("INSERT INTO conferences(user_id, room_name, date_created) " . 
									"VALUES($user_id, '$roomName', NOW());")) {
				$this->closeConnection();
				return $nextId;
			}

		} else {
			$this->closeConnection();
			return -1;
		}
		
	}

	public function getUserSlideCount($userName) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT user_id FROM users WHERE username='$userName';");
		if($result->num_rows > 0) {
			// Get user_id
			$row = $result->fetch_row();
			$user_id = $row[0];

			$result = $this->mysqli->query("SELECT COUNT(*) FROM user_slides WHERE user_id=$user_id AND active=1;");
			$row = $result->fetch_row();
			$this->closeConnection();
			return $row[0]; // Count of slides owned by this user that are active
		} else {
			$this->closeConnection();
			return 0;
		}
	}

	public function getUserSlides($username) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT user_id FROM users WHERE username='$username';");
		if($result->num_rows > 0) {
			// Get user_id
			$row = $result->fetch_row();
			$user_id = $row[0];

			$result = $this->mysqli->query("SELECT slide_id, user_id, slide_url FROM user_slides WHERE user_id=$user_id AND active=1;");
			if($result->num_rows > 0) {
				$xml = new XMLWriter();
				$xml->openURI("php://output");
				$xml->startDocument();
				$xml->setIndent(true);
				$xml->startElement('slides');

				
				while($row = $result->fetch_assoc()) {
					$xml->startElement('slide');
					$xml->writeAttribute('id', $row['slide_id']);
					$xml->writeAttribute('user_id', $row['user_id']);
					$xml->writeAttribute('slide_url', $row['slide_url']);
					$xml->endElement();
				}
				
				$xml->endElement();
				header('Content-type: text/xml');
				$xml->flush();
				return 1;

			} else {
				$this->closeConnection();
				return 0;
			}

		} else {
			$this->closeConnection();
			return 0;
		}
	}

	public function setUserSlides($user_id, $array) {
		$this->connectToDatabase();
		$this->mysqli->query("DELETE FROM user_slides WHERE user_id=$user_id;");

		$i = 0;
		foreach($array as $s) {
			if($i > 1) {
				$full_path = "http://thenightlight.net/convrence/user_slides/" . $this->username . "/" . $s;
				$this->mysqli->query("INSERT INTO user_slides(user_id, slide_url, active) VALUES($user_id, '$full_path', 1);");
				//echo "$s <br />";
			}
			$i++;
		}
		$this->closeConnection();
	}

	public function getUsersSlidesResult($username) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT slide_id, slide_url FROM user_slides us " . 
									   "LEFT JOIN users u ON us.user_id=u.user_id " .
									   "WHERE u.username = '$username' AND active=1;");
		$this->closeConnection();
		return $result;
	}

	public function getCustomAvatar($username) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT body_part, item_name FROM avatars av " .
									   "LEFT JOIN users u ON av.user_id=u.user_id " . 
									   "WHERE u.username = '$username';");
		if($result->num_rows > 0) {
			$xml = new XMLWriter();
			$xml->openURI("php://output");
			$xml->startDocument();
			$xml->setIndent(true);
			$xml->startElement('avatar');

			while($row = $result->fetch_assoc()) {
				// create head_item tag
				if($row['body_part'] == "head") {
					$xml->startElement('head');
					$xml->writeAttribute('item', $row['item_name']);
					$xml->endElement();
				}

				if($row['body_part'] == "body") {
					$xml->startElement('body');
					$xml->writeAttribute('item', $row['item_name']);
					$xml->endElement();
				}
			}	
			
			$xml->endElement();		
			header('Content-type: text/xml');
			$xml->flush();
			$this->closeConnection();
			return 1;
		} else {
			$this->closeConnection();
			return 0;
		}
	}

	public function getUsername() {
		if(isset($this->username)) {
			return $this->username;
		} else {
			return null;
		}
	}

	public function getUserID($username) {
		$this->connectToDatabase();
		$result = $this->mysqli->query("SELECT user_id FROM users WHERE username='$username';");
		if($result->num_rows > 0) {
			$return = $result->fetch_row();
			$this->closeConnection();
			return $return[0];
		} else {
			$this->closeConnection();
			return -1;
		}
		
	}

	public function isLoggedIn() {
		return $this->loggedIn;
	}

	private function closeConnection() {
		$this->mysqli->close();
	}

}

?>

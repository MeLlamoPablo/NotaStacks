<?php

/**
 * User class
 * 
 * This class manages users for NotAStacks.
 *
 * @author Pablo Rodríguez <pabloviolin8@gmail.com>
 */
class User{
	/**
	 * @var int $steamid The user's SteamID64
	 */
	public $steamid;

	/**
	 * @var int $id The user's ID on the database
	 */
	public $id;

	/**
	 * @var string $name The user's profile name on Steam
	 */
	public $name;

	/**
	 * @var string $avatar An URL to the user's Steam full avatar (128px)
	 */
	public $avatar;

	/**
	 * @var DateTime $lastRefresh The last time the user refreshed his data from Steam
	 */
	public $lastRefresh;

	/**
	 * Creates the user object.
	 *
	 * This method creates the user object. If it's provieded with the source "db",
	 * it will get the content from the database. If it's provided with the source
	 * "steam", it will get the information from Steam. If no source it's provided,
	 * it will assume "db" by default. If no user it's provided, the method will get
	 * content for the logged in user.
	 *
	 * @param string $source The source from where the method will get the data. It's either "db" or "steam". Optional.
	 * @param int $getForUser The ID of the user we want to get the information for. Optional.
	 * @return void
	 */
	public function __construct($source = 'db', $getForUser = 0){
		switch($source){
			case 'db':
				global $mysqli;
				if(!$getForUser){
					$r = $mysqli->query("SELECT * FROM users WHERE steamid = ".$mysqli->real_escape_string($_SESSION['steamid']));
				}else{
					$r = $mysqli->query("SELECT * FROM users WHERE id = ".$mysqli->real_escape_string($getForUser));
				}

				$r = $r->fetch_assoc();

				$this->steamid = $r['steamid'];
				$this->id = $r['id'];
				$this->name = $r['name'];
				$this->avatar = $r['avatar'];
				$this->lastRefresh = $r['lastRefresh'];

				return;

			case 'steam':
				//TODO implement this
				return;
			
			default:
				//Neither db nor steam are passed. Return false.
				return;
		}
	}

	/**
	 * Refreshes the user info.
	 *
	 * This method fetches the information (username and avatar) from Steam for the user.
	 * Note that the ID won't be fetched again because it souldn't change.
	 *
	 * @return void
	 */
	public function refresh(){
		$_SESSION['steam_uptodate'] = FALSE;
		$r = getInfo();
		$this->name = $r['personaname'];
		$this->avatar = $r['avatarfull'];
		$this->lastRefresh = time();

		$this->saveChanges();
	}

	/**
	 * Returns the time since the last refresh occured, in an Unix timestamp.
	 *
	 * @return int
	 */
	public function timeSinceLastRefresh(){
		return time() - $this->lastRefresh;
	}

	/**
	 * Stores the changes into the database. Requires a mysqli connection.
	 *
	 * @return void
	 */
	public function saveChanges(){
		global $mysqli;

		$mysqli->query("UPDATE users SET `name` = '".$mysqli->real_escape_string($this->name)."', `avatar` = '".$mysqli->real_escape_string($this->avatar)."', `lastRefresh` = '".$mysqli->real_escape_string($this->lastRefresh)."' WHERE id = ".$mysqli->real_escape_string($this->id).";");
	}

	/**
	 * Creates a permalink to the user's Steam profile
	 *
	 * @return string The user's profile's URL
	 */
	public function toURL(){
		return 'http://steamcommunity.com/profiles/'.$this->steamid;
	}
}

/**
 * Stack Class
 * 
 * This class manages Stacks for NotAStacks. Stacks are groups of people
 * who want to play a concrete gamemode ofDota together, at a concrete time.
 *
 * @author Pablo Rodríguez <pabloviolin8@gmail.com>
 */

class Stack{
	/**
	 * @var int $id The stack's ID
	 */
	public $id;

	/**
	 * @var array $players The players ($user class) that belong to the stack. The index should be integers beginning in 0.
	 */
	public $players;

	/**
	 * @var string $gamemode The game mode that the stack will be playing 
	 */
	public $gamemode;

	/**
	 * @var int $time The time when the Stack will be playing, in unix timestamp format.
	 */
	public $time;

	/**
	 * @var int $ownerid The ID of the user who created the stack
	 */
	public $ownerid;

	/**
	 * Creates the Stack object.
	 *
	 * This method creates the Stack object. If the parameter $source is
	 * an integer, it will build the object with the information from the
	 * database, using that integer as the ID. If the parameter $source is
	 * the string "provided", it will listen to the next parameter. In that
	 * case, it will also send the created object to the database.
	 *
	 * The structure for the $data array is the following:
	 * $arrayName = array('players' => $players, 'gamemode' => $gamemode, 'time' => $time, 'ownerid' => $ownerid);
	 *
	 * @param string $source The source from where the method will get the data. It's either the row ID or "provided".
	 * @param int $data An array containing the data. Optional. Needed if the first parameter is "provided".
	 * @return void
	 */
	public function __construct($source, $data = array()){
		if($source === "provided"){
			//The information is provided
			if(!isset($data['players']) OR !isset($data['gamemode']) OR !isset($data['time']) OR !isset($data['ownerid'])) return;

			//Check if the provided $players is in the right format (an array with User objects)
			if(!is_array($data['players'])) return;
			for($i=0; isset($data['players'][$i]); $i++){
				if(!is_a($data['players'][$i], 'User')) return;
			}

			//Store the data into the database
			global $mysqli;
			$mysqli->query("INSERT INTO stacks (`gamemode`, `time`, `ownerid`) VALUES ('".$mysqli->real_escape_string($data['gamemode'])."', '".$mysqli->real_escape_string($data['time'])."', '".$mysqli->real_escape_string($data['ownerid'])."');");

			//Create the object
			$this->id = $mysqli->insert_id;
			$this->players = $data['players'];
			$this->gamemode = $data['gamemode'];
			$this->time = $data['time'];
			$this->ownerid = $data['ownerid'];

			//Store the relations between this stack and its player in stacks_players
			for ($i=0; isset($this->players[$i]); $i++) { 
				$mysqli->query("INSERT INTO stacks_players (`stack`, `player`) VALUES ('".$mysqli->real_escape_string($this->id)."', '".$mysqli->real_escape_string($this->players[$i]->id)."');");

			}
			return;
		}else{
			//TODO implement this
			return;
		}
	}
}	
?>
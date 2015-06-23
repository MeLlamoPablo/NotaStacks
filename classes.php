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
	 * @var int $lastRefresh The last time the user refreshed his data from Steam, in Unix timestamp
	 */
	public $lastRefresh;

	/**
	 * @var string $ban If the user is banned, the time when the ban will expire in Unix timestamp (converted to string), or string "permanent" for a permaban. If the user isn't banned, int "0".
	 */
	public $ban;

	/**
	 * @var string $lastMessage The last message displayed to the user. This is handled by displayMessage()
	 */
	private $lastMessage;

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
				$this->ban = $r['ban'];
				$this->lastMessage = $r['lastmessage'];

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
	public function getURL(){
		return 'http://steamcommunity.com/profiles/'.$this->steamid;
	}

	/**
	 * Displays one of the messages from messages.php to the user. Updates the DB so that it isn't displayed again.
	 */
	public function displayMessage(){
		global $GLOBAL_CONFIG;
		if($this->lastMessage == $GLOBAL_CONFIG['version']) return FALSE;
		require_once 'messages.php';
		$message = getMessage($this->lastMessage);
		if($message === FALSE) return FALSE;
		if(!isset($message['closeButton'])) $message['closeButton'] = 'Close';

		$modal = new Modal('messageModal', $message['title'], $message['content'], $message['closeButton']);
		echo $modal->getModal(TRUE);

		global $mysqli;
		$mysqli->query("UPDATE users SET lastmessage = '".$GLOBAL_CONFIG['version']."' WHERE id = ".$this->id);
		return TRUE;

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
	 * @var string $server The servers where the Stack will play, separated by dashes ("-")
	 */
	public $server;

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
	 * $arrayName = array('players' => $players, 'gamemode' => $gamemode, 'time' => $time, 'ownerid' => $ownerid, 'server' => $server);
	 *
	 * @param int|string $source The source from where the method will get the data. It's either the row ID or "provided".
	 * @param int $data An array containing the data. Optional. Needed if the first parameter is "provided".
	 * @return void
	 */
	public function __construct($source, $data = array()){
		global $mysqli;
		if($source === "provided"){
			//The information is provided
			if(!isset($data['players']) OR !isset($data['gamemode']) OR !isset($data['time']) OR !isset($data['ownerid']) OR !isset($data['server'])) return;

			//Check if the provided $players is in the right format (an array with User objects)
			if(!is_array($data['players'])) return;
			for($i=0; isset($data['players'][$i]); $i++){
				if(!is_a($data['players'][$i], 'User')) return;
			}

			//Store the data into the database
			$mysqli->query("INSERT INTO stacks (`gamemode`, `time`, `ownerid`,`server`) VALUES ('".$mysqli->real_escape_string($data['gamemode'])."', '".$mysqli->real_escape_string($data['time'])."', '".$mysqli->real_escape_string($data['ownerid'])."', '".$mysqli->real_escape_string($data['server'])."');");

			//Create the object
			$this->id = $mysqli->insert_id;
			$this->players = $data['players'];
			$this->gamemode = $data['gamemode'];
			$this->time = $data['time'];
			$this->ownerid = $data['ownerid'];
			$this->server = $data['server'];

			//Store the relations between this stack and its player in stacks_players
			for ($i=0; isset($this->players[$i]); $i++) { 
				$mysqli->query("INSERT INTO stacks_players (`stack`, `player`) VALUES ('".$mysqli->real_escape_string($this->id)."', '".$mysqli->real_escape_string($this->players[$i]->id)."');");

			}
			return;
		}else{
			$r = $mysqli->query("SELECT * FROM stacks WHERE id = ".$source);
			$r = $r->fetch_assoc();

			$r2 = $mysqli->query("SELECT * FROM stacks_players WHERE stack = ".$source);
			for($i=0; $r3 = $r2->fetch_assoc(); $i++){
				$players[$i] = new User('db', $r3['player']);
			}

			$this->id = $r['id'];
			$this->players = $players;
			$this->gamemode = $r['gamemode'];
			$this->time = $r['time'];
			$this->ownerid = $r['ownerid'];
			$this->server = $r['server'];
			return;
		}
	}

	/**
	 * Adds a player to the stack, and updates the stack on the database
	 *
	 * @param User $player The user object that will be added to the stack.
	 * @return boolean TRUE if the player is added successfully, FALSE if not.
	 */
	public function addPlayer($player){
		global $mysqli;
		if(!isset($player->id)) return FALSE;
		$mysqli->query("INSERT INTO stacks_players (`stack`, `player`) VALUES ('".$mysqli->real_escape_string($this->id)."', '".$mysqli->real_escape_string($player->id)."');");
		return TRUE;

	}

	/**
	 * Removes a player from the stack, and updates the stack on the database
	 *
	 * @param User $player The user object that will be removed from the stack. Needles to say, it must belong to it.
	 * @return boolean TRUE if the player is added successfully, FALSE if not.
	 */
	public function removePlayer($player){
		global $mysqli;
		if(!isset($player->id)) return FALSE;
		//Check if the player is already in the stack
		if(!in_array($player, $this->players)) return FALSE;
		$mysqli->query("DELETE FROM stacks_players WHERE player = ".$mysqli->real_escape_string($player->id)." AND stack = ".$mysqli->real_escape_string($this->id));
		return TRUE;

	}
}

/**
 * Modal Class
 *
 * Ths class manages Bootstrap modals to make their creation much simpler.
 */
class Modal{
	private $id;
	private $title;
	private $content;
	private $closeButton;
	private $saveButton;
	private $callButton;

	/**
	 *	Creates the modal object.
	 *
	 * @param string $id The modal's id. There can't be two modals with the same id on the same page.
	 * @param string $title The modal's title. Can use HTML but shouldn't
	 * @param string $content The modal's content. Can use HTML
	 * @param string $closeButton The text for the close button. Optional; default is "Close".
	 * @param string $saveButton The HTML code for the save button, if there's any. Optional
	 * @param array $callbutton An array with two elements: $callbutton['content'] and $callbutton['attributes']. The content will be place inside the button and the attributes will be place inside the <button> tag. The attributes are optional, the content isn't. The entire parameter is optional, but the modal will need to be set in "autocall" to be displayed.
	 */
	public function __construct($id, $title, $content, $closeButton = "Close", $saveButton = "Undefined", $callButton = "Undefined"){
		$this->id = $id;
		$this->title = $title;
		$this->content = $content;
		$this->closeButton = $closeButton;
		$this->saveButton = ($saveButton === "Undefined") ? NULL : $saveButton;
		//If the callButton is set and has at least its content, set the property to the given object, otherwise, set it to NULL
		$this->callButton = ($callButton === "Undefined") ? NULL : ((isset($callButton['content'])) ? $callButton : NULL);
	}

	/**
	 * Generates the modal's HTML
	 *
	 * @param boolean $autoCall If is set to TRUE, generates also a script for the modal to be shown instantly, without needing a button to be pressed.
	 */
	public function getModal($autoCall = FALSE){
		return '<div class="modal fade" id="'.$this->id.'" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">'.$this->title.'</h4>
						</div>
						<div class="modal-body">'.$this->content.'</div>
						<div class="modal-footer">
							'.(is_null($this->saveButton) ? '' : $this->saveButton).'
							<button type="button" class="btn btn-default" data-dismiss="modal">'.$this->closeButton.'</button>
						</div>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->'.

			($autoCall ? '<script type="text/javascript">
				$( document ).ready(function(){
					$(\'#'.$this->id.'\').modal();
				});
			</script>' : '');
	}
}
?>
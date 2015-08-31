<?php
define('ACTIVE_PAGE', 'profile');

//We get the contents from the logic file.
require_once '../logic/profile.php';
$data = $output;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo $data['userProfile']->name.((substr($data['userProfile']->name, -1) === 's') ? '\'' : '\'s') ?> profile - <?php echo $GLOBAL_CONFIG['site_name'] ?></title>
    <?php require_once 'head.php' ?>
</head>
<body>
	<div class="container" id="wrap">
		<?php include 'menu.php';
		echo

		'<div class="jumbotron" style="margin-top: -15px">';
			if(isset($data['alert'])) echo $data['alert'];
			//Buttons
			if($loggedUser !== NULL){
				echo '<div class="row well well-sm container btn-group">';
				if($loggedUser->id === $data['userProfile']->id){
					echo '<button data-toggle="modal" data-target="#editProfileModal" id="editProfileButton" class="btn '.($hasSetProfile ? 'btn-default">Edit profile</button>' : 'btn-success">Create profile</button>').
					'<button '.(($loggedUser->timeSinceLastRefresh() > $GLOBAL_CONFIG['refreshWaitTime']) ? 'onclick="window.location.replace(\'/notastacks/profiles/me/refresh\')" ' : '').'id="refreshButton" class="btn btn-primary'.(($loggedUser->timeSinceLastRefresh() < $GLOBAL_CONFIG['refreshWaitTime']) ? ' disabled' : '' ).'" data-show="tooltip" data-placement="bottom" title="Doing this will download your new avatar and name from Steam to the server. For performance reasons, you can only do this every 24 hours.">Refresh data from Steam</button>';
					$modalContent = '

					'.(!$hasSetProfile ? '<div>Creating a '.$GLOBAL_CONFIG['site_name'].' profile allows you to give information about your playstile, your likes, your availability, etc. You do not have to fill every field, but it\'s encouraged to give as much information as possible to find the best match.</div>' : '').'

					<h4>Favourite position</h4>
					<div class="btn-toolbar"><div class="btn-group" data-toggle="buttons">
						<label class="position btn btn-default" data-value="position_carry"><input type="radio" id="position_carry">Safelane Carry</label>
						<label class="position btn btn-default" data-value="position_mid"><input type="radio" id="position_mid">Midlaner</label>
						<label class="position btn btn-default" data-value="position_off"><input type="radio" id="position_off">Offlaner</label>
						<label class="position btn btn-default" data-value="position_supp"><input type="radio" id="position_supp">Support</label>
						<label class="position btn btn-default" data-value="position_hsupp"><input type="radio" id="position_hsupp">Hard Support</label>
						<input type="hidden" id="favPosition" name="favPosition">
					</div></div>
					<script type="text/javascript">
					$(".position").click(function(){
						$("#favPosition").val($(this).attr("data-value"));
					});
					</script>

					<h4>Adjective</h4>
					<div>Adjectives are unlocked by leveling up. Be nice and friendly to your teammates, and they may commend you. You can chose to display an adjective before your position.</div>
					<select name="adjective" class="chosen-select" data-placeholder="Chose an adjective" style="width:350px;">
						<option value="null"></option>';
						for($i=0; isset($GLOBAL_CONFIG['adjectives'][$i]); $i++){ 
							if($GLOBAL_CONFIG['adjectives'][$i]['level'] <= $data['userLevel']->getCurrentLevel())
								$modalContent .= '<option value="adj_'.$i.'">'.$GLOBAL_CONFIG['adjectives'][$i]['adjective'].'</option>';
						}
					$modalContent .= '</select>

					<h4>Servers</h4>
					<div>Select the servers on wich you are available to play</div>';
					for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
                        $modalContent .= '<label class="checkbox-inline">';
                            $modalContent .= '<input type="checkbox" id="'.$GLOBAL_CONFIG['servers'][$i].'check" name="server_'.$GLOBAL_CONFIG['servers'][$i].'" value="server_'.$GLOBAL_CONFIG['servers'][$i].'"> '.$GLOBAL_CONFIG['servers'][$i];
                        $modalContent .= '</label>';
                    }
                    $modalContent .= '<div>Select your preferred server</div>
                    <div class="btn-toolbar"><div class="btn-group" data-toggle="buttons">';
                    for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
                         $modalContent .= '<label class="server btn btn-default" data-value="'.$GLOBAL_CONFIG['servers'][$i].'"><input type="radio" id="prefserver_'.$GLOBAL_CONFIG['servers'][$i].'">'.$GLOBAL_CONFIG['servers'][$i].'</label>';
                    }
                    $modalContent .= '</div></div>
                    <input type="hidden" id="favServer" name="favServer">
                    <script type="text/javascript">
					$(".server").click(function(){
						$("#favServer").val($(this).attr("data-value"));
					});
					</script>
					';
					$modalButtons = '<button type="button" class="btn btn-danger" data-dismiss="modal">Discard changes</button>
					<button type="submit" name="editProfileSubmit" class="btn btn-'.($hasSetProfile ? 'primary' : 'success').'">Save changes</button>';
					$editProfileModal = new Modal('editProfileModal', $hasSetProfile ? 'Edit my profile' : 'Create a '.$GLOBAL_CONFIG['site_name'].' profile', $modalContent, $modalButtons, NULL, 'method="post" action="http://'.$_SERVER['HTTP_HOST'].'/notastacks/profiles/me/"');
					echo $editProfileModal->getModal();
				}else{
					//Commend
					echo '<button id="commendButton" data-toggle="modal" data-target="#commendModal" class="btn btn-success" data-show="tooltip" data-placement="bottom" title="Commend this player"><span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span></button>';
					$commendModalContent = '

					<div class="alert alert-warning" role="alert">Please, do not fake-commend. Only commend a player if you had a good time with him, if he was helpful, etc. Commending a player for no reason, or even worse, commending a player because he commended you, may result in a ban.</div>

					<textarea required id="commendMessage" name="commendMessage" class="form-control custom-control" rows="2" style="resize:none" placeholder="Tell the player why you\'re commending him. Your message cannot make use of HTML tags."></textarea>
					<input type="hidden" id="toUser" name="toUser" value="'.$data['userProfile']->id.'">

					';
					$commendModalButtons = '<button type="button" class="btn btn-danger" data-dismiss="modal">Discard</button>
					<button type="submit" name="commendSubmit" class="btn btn-default">Commend</button>';
					$commendModal = new Modal('commendModal', 'Commend '.$data['userProfile']->name, $commendModalContent, $commendModalButtons, NULL, 'method="post" action="/notastacks/profiles/'.$data['userProfile']->id.'"/');
					echo $commendModal->getModal();

					//Report
					echo '<button id="reportButton" data-toggle="modal" data-target="#reportModal" class="btn btn-danger" data-show="tooltip" data-placement="bottom" title="Report this player"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></button>';
					$reportModalContent = '

					<div>The report system is not done yet. Please, if you have any problems with this player, speak to a NotA mod.</div>

					';
					$reportModal = new Modal('reportModal', 'Report '.$data['userProfile']->name, $reportModalContent);
					echo $reportModal->getModal();
				}
				echo '</div>';
			}
			//Profile
			echo '<div class="row">';
				//Level system
				echo '<div class="progress"><div class="progress-bar progress-bar-success" aria-valuemin="'.$data['userLevel']->getNeededExpForCurrentLevel().'" aria-valuemax="'.$data['userLevel']->getNeededExpForNextLevel().'" data-transitiongoal="'.$data['userLevel']->exp.'">	
				</div></div>
				<script type="text/javascript">
					$(".progress .progress-bar").progressbar({display_text: "center", use_percentage: false, amount_format: function(p, t) {return "Level '.$data['userLevel']->getCurrentLevel().': "+ '.($data['userLevel']->getRemainingExpForNextLevel()/100).' +" commend'.((($data['userLevel']->getRemainingExpForNextLevel()/100) != 1) ? 's' : '').' to go.";}});
				</script>';
				echo '<div class="col-sm-3" style="text-align: right;">
					<img src="'.$data['userProfile']->avatar.'" class="img-responsive img-thumbnail" />
				</div>
				<div clas="col-sm-9">
					<h1>'.$data['userProfile']->name.((substr($data['userProfile']->name, -1) === 's') ? '\'' : '\'s').' profile</h1>';
					if($hasSetProfile){
						echo '<h3>'.(isset($data['profile']['adjective']) ? $data['profile']['adjective'] : 'Level '.$data['userLevel']->getCurrentLevel()).(isset($data['profile']['position']) ? ' '.$data['profile']['position'] : '').'</h3>';
						echo '<ul>';
							if(isset($data['profile']['servers'])){
								echo '<li>This user can play on ';
								$servers = explode('-', $data['profile']['servers']);
								$totalServers = count($servers);
								foreach($servers as $key => $value){
									echo $value;
									//If there's more than one server, we'll need to add commas (",") and "or"
                                    if($totalServers > 1){
                                        //Add a comma on every server but the last one
                                        if(($key+1) !== $totalServers) echo ', ';
                                        //Add "or" before the last one
                                        if(($key+2) === $totalServers) echo 'and ';

                                    }
								}
								echo '.</li>';
							}
							if(isset($data['profile']['pref_server'])){
								echo '<li>He prefers to play on '.$data['profile']['pref_server'].'.</li>';
							}
								echo '<li>Here\'s a <a href="'.$data['userProfile']->getUrl().'" target="_blank">link to his Steam profile</a>.</li>';
						echo '</ul>';
					}else{
						if(($loggedUser !== NULL) AND ($loggedUser->id === $data['userProfile']->id)){
							echo '<p>You haven\'t configured your '.$GLOBAL_CONFIG['site_name'].' profile yet.<br>
							Click the button above to do it.';
						}else{
							echo '<p>This user hasn\'t configured his '.$GLOBAL_CONFIG['site_name'].' profile yet.<br>
							Click <a href="'.$data['userProfile']->getUrl().'" target="_blank">here</a> to go to his Steam profile.';
						}
					}
				echo '</div>
			</div>
			<div class="row">
				<br>
				<div class="well well-lg">
					<h2>Commends</h2>';
					$r = $mysqli->query("SELECT * FROM `commends` WHERE `to` = ".$data['userProfile']->id);
					echo '<table class="table">';
					while($commends = $r->fetch_assoc()){
						$from = new User('db', $commends['from']);
						echo '<tr>';
							echo '<td style="width: 10%"><a href="profile.php?id='.$from->id.'"><img width="64" height="64" class="img-responsive" src="'.$from->avatar.'" /></a></td>';
							echo '<td style="width: 90%"><b><a href="profile.php?id='.$from->id.'">'.$from->name.'</a> says</b>: '.$commends['message'].'</td>';
						echo '</tr>';
					}
					echo '</table>';
				echo '</div>
			</div>
		</div>'

		; ?>
		<div id="footer" class="navbar navbar-default navbar-fixed-bottom">
            <div class="container">
                <p class="navbar-text">Created by <?php echo $GLOBAL_CONFIG['owner'] ?> for <a href="http://reddit.com/r/noobsoftheancient" target="_blank">/r/NoobsOfTheAncient</a>. Version <?php echo $GLOBAL_CONFIG['version'] ?>. <a href="https://github.com/MeLlamoPablo/NotaStacks" target="_blank">Source code</a>.</p>
            </div>
        </div>
	</div>
	<script type="text/javascript">
	$(document).ready(function(){
		//Tooltip opt-in
        $(function () {
            $('[data-show="tooltip"]').tooltip()
            //We use "data-show" instead of "data-toggle" so a button can have a tooltip and trigger a modal at once
        });

        //Chosen
        $(".chosen-select").chosen({});
	});
	</script>
</body>
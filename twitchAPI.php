<?php

/* Twitch API
 * Auteur: Hadrien Boyer
 * URL: http://hadri.info
 */

class Twitch_API {
	
	public function getAPI_URI($type){
		
		$API_Base_URI = "https://api.twitch.tv/kraken/";
		
			$twitchAPI_Sections = array(
				"teams" 	=> $API_Base_URI."teams/",
				"channels" 	=> $API_Base_URI."channels/",
				"games" 	=> $API_Base_URI."games/",
				"users" 	=> $API_Base_URI."users/",
				"streams" 	=> $API_Base_URI."streams/",
				"search" 	=> $API_Base_URI."search/"
			);
	
		return $twitchAPI_Sections[$type];

	}
	
	
	// Liste Teams
	public function getAPI_ListTeams($limit, $offset) {
		// Récuperation de l'url avec les paramêtres "limit" et "offset"
		$url = file_get_contents($this->getAPI_URI("teams")."?limit=$limit&offset=$offset");
		// On décode la chaîne JSON
		$decodeFlux = json_decode($url, true);
		// On indique que l'on se place sur le parent nommé "teams"
		$API_Teams = $decodeFlux["teams"];
		
		$tableauItems = array();
			
			foreach($API_Teams as $team) {
	
				// On push le tableau
				array_push($tableauItems, array(
					"name" 			=>	$team["name"],
					"info"		 	=>	$team["info"],
					"display_name" 	=>	$team["display_name"],
					"created_at" 	=>	$team["created_at"],
					"updated_at" 	=>	$team["updated_at"],
					"logo" 			=>	$team["logo"],
					"banner" 		=>	$team["banner"],
					"background" 	=>	$team["background"],
					));
		
			}
			return $tableauItems;
		}
		
	
	// Recherche Team
	public function getAPI_SearchTeam($name) {
		$url = file_get_contents($this->getAPI_URI("teams").urlencode($name));
		
		$team = json_decode($url, true);

			if($team["_id"] == ""){
					echo "Il n'y a pas de team comportant ce nom !";	
			}else{
		
					// On push le tableau
					$tableauItems = array(
						"name" 			=>	$team["name"],
						"info"		 	=>	$team["info"],
						"display_name" 	=>	$team["display_name"],
						"created_at" 	=>	$team["created_at"],
						"updated_at" 	=>	$team["updated_at"],
						"logo" 			=>	$team["logo"],
						"banner" 		=>	$team["banner"],
						"background" 	=>	$team["background"],
						);
			
				
				return $tableauItems;	
				}
		}
		
		
	// Get API Channel
	public function getAPI_Channel($name) {
		$url = file_get_contents($this->getAPI_URI("channels").urlencode($name));
		
		$channel = json_decode($url, true);
		
			if($channel["_id"] == ""){
					//Utiliser dans list.php pour insertion dans tableau
					//echo "Il n'y a pas de channel comportant ce nom !";	
			}else{
					// On push le tableau
					$tableauItems = array(
					
						"status"		=>	$channel["status"],
						"display_name" 	=>	$channel["display_name"],
						"created_at" 	=>	$channel["created_at"],
						"_id" 			=>	$channel["_id"],
						"name" 			=>	$channel["name"],
						"updated_at" 	=>	$channel["updated_at"],
						"logo" 			=>	$channel["logo"],
						"banner" 		=>	$channel["banner"],
						"video_banner" 	=>	$channel["video_banner"],
						"background" 	=>	$channel["background"],
						"profile_banner" 	=>	$channel["profile_banner"],
						"profile_banner_background_color" 	=>	$channel["profile_banner_background_color"],
						"url" 			=>	$channel["url"],
						"views" 		=>	$channel["views"],
						"followers" 	=>	$channel["followers"],
				);
				return $tableauItems;
			}
			
		}


	// Liste Jeux
	public function getAPI_TopGames($limit, $offset) {

		$url = file_get_contents($this->getAPI_URI("games")."top?limit=$limit&offset=$offset");
		
		// On décode la chaîne JSON
		$decodeFlux = json_decode($url, true);
		// On indique que l'on se place sur le parent nommé "teams"
		$API_TopGames = $decodeFlux["top"];
		
		$tableauItems = array();
			
			foreach($API_TopGames as $game) {
	
				// On push le tableau
				array_push($tableauItems, array(
					"name" 			=>	$game["game"]["name"],
					"_id"		 	=>	$game["game"]["_id"],
					"small_box" 	=>	$game["game"]["box"]["small"],
					"medium_box" 	=>	$game["game"]["box"]["medium"],
					"large_box" 	=>	$game["game"]["box"]["large"],
					));
		
			}
			return $tableauItems;
		}
	


	// Liste Teams
	public function getAPI_User($name) {

		$url = file_get_contents($this->getAPI_URI("users").urlencode($name));
		
		$user = json_decode($url, true);

			if($user["_id"] == ""){
					echo "Il n'y a pas d'utilisateur avec ce nom !";	
			}else{
		
					// On push le tableau
					$tableauItems = array(
						"display_name" 	=>	$user["display_name"],
						"_id" 			=>	$user["_id"],
						"name" 			=>	$user["name"],
						"type"		 	=>	$user["type"],
						"bio" 			=>	$user["bio"],
						"created_at" 	=>	$user["created_at"],
						"updated_at" 	=>	$user["updated_at"],
						"logo" 			=>	$user["logo"],
						);
			
				
				return $tableauItems;	
				}
		}
	

	
	// Liste des Streams en vedette
	public function getAPI_FeaturedStreams($limit, $offset) {
		// Récupération du contenu JSON avec les paramètres "limit" et "offset"
		$url = file_get_contents($this->getAPI_URI("streams")."?limit=$limit&offset=$offset");
		// On décode la chaîne JSON
		$decodeFlux = json_decode($url, true);

		$API_Streams = $decodeFlux["streams"];			
				
		$tableauItems = array();
		
		foreach($API_Streams as $stream) {
			
			// Contenu mature ou non ?
			$isMature = ($stream["channel"]["mature"] == true) ? 'CONTENU MATURE!' : 'JEU TOUT PUBLIC';
		
			// Array_Push sur $tableauItems
			array_push($tableauItems, array(
				// Channel
				"mature" 			=> 	$isMature,
				"status" 			=>	$stream["channel"]["status"],
				"display_name" 		=>	$stream["channel"]["display_name"],
				"name" 				=>	$stream["channel"]["name"],
				"game" 				=> 	$stream["channel"]["game"],
				"created_at" 		=>	$stream["channel"]["created_at"],
				"updated_at" 		=>	$stream["channel"]["updated_at"],
				"logo" 				=>	$stream["channel"]["logo"],
				"banner" 			=>	$stream["channel"]["banner"],
				"video_banner" 		=>	$stream["channel"]["video_banner"],
				"background" 		=>	$stream["channel"]["background"],
				"profile_banner" 	=>	$stream["channel"]["profile_banner"],
				"profile_banner_background_color" => $stream["channel"]["profile_banner_background_color"],
				"url" 				=>	$stream["channel"]["url"],
				"views" 			=>	$stream["channel"]["views"],
				"followers" 		=>	$stream["channel"]["followers"],
				// Featured
				"text" 				=> 	$stream["text"],
				// Previews
				"preview_small" 	=>	$stream["preview"]["small"],
				"preview_medium" 	=>	$stream["preview"]["medium"],
				"preview_large" 	=>	$stream["preview"]["large"],
				"preview_template" 	=>	$stream["preview"]["template"],
				// Current Stream
				"_id" 				=>	$stream["_id"],
				"stream_game" 		=>	$stream["game"],
				"stream_viewers" 	=>	$stream["viewers"],	
				// Videos, Char, Stream Key…
				"videos" 			=>	$stream["channel"]["_links"]["videos"],
					
					));
	
			}
			return $tableauItems;
		}


	// Rechercher des Streams par nom
	public function getAPI_SearchStreams($name, $limit, $offset) {
		// Récupération du contenu JSON avec les paramètres "limit" et "offset"
		$url = file_get_contents($this->getAPI_URI("search")."streams?q=".$name."&limit=$limit&offset=$offset");
		// On décode la chaîne JSON
		$decodeFlux = json_decode($url, true);

		$API_Streams = $decodeFlux["streams"];			
				
		$tableauItems = array();
		
		foreach($API_Streams as $stream) {
			
			// Contenu mature ou non ?
			$isMature = ($stream["channel"]["mature"] == true) ? 'CONTENU MATURE!' : 'JEU TOUT PUBLIC';
			
			// Array_Push sur $tableauItems
			array_push($tableauItems, array(
				// Channel
				"mature" 			=> 	$isMature,
				"status" 			=>	$stream["channel"]["status"],
				"display_name" 		=>	$stream["channel"]["display_name"],
				"name" 				=>	$stream["channel"]["name"],
				"game" 				=> 	$stream["channel"]["game"],
				"created_at" 		=>	$stream["channel"]["created_at"],
				"updated_at" 		=>	$stream["channel"]["updated_at"],
				"logo" 				=>	$stream["channel"]["logo"],
				"banner" 			=>	$stream["channel"]["banner"],
				"video_banner" 		=>	$stream["channel"]["video_banner"],
				"background" 		=>	$stream["channel"]["background"],
				"profile_banner" 	=>	$stream["channel"]["profile_banner"],
				"profile_banner_background_color" => $stream["channel"]["profile_banner_background_color"],
				"url" 				=>	$stream["channel"]["url"],
				"views" 			=>	$stream["channel"]["views"],
				"followers" 		=>	$stream["channel"]["followers"],
				// Featured
				"text" 				=> 	$stream["text"],
				// Previews
				"preview_small" 	=>	$stream["preview"]["small"],
				"preview_medium" 	=>	$stream["preview"]["medium"],
				"preview_large" 	=>	$stream["preview"]["large"],
				"preview_template" 	=>	$stream["preview"]["template"],
				// Current Stream
				"_id" 				=>	$stream["_id"],
				"stream_game" 		=>	$stream["game"],
				"stream_viewers" 	=>	$stream["viewers"],	
				// Videos, Char, Stream Key…
				"videos" 			=>	$stream["channel"]["_links"]["videos"],
					
					));
	
			}
			return $tableauItems;
		}
		
		//Get Viewers
		public function getAPI_viewers($name){
		// Rechercher des Streams par nom
		// Récupération du contenu JSON avec les paramètres "limit" et "offset"
		$url = file_get_contents($this->getAPI_URI("search")."streams?q=".$name);
		// On décode la chaîne JSON
		$decodeFlux = json_decode($url, true);

		$API_Streams = $decodeFlux["streams"];			
				
		$tableauItems = array();
		
		foreach($API_Streams as $stream) {
			
			// Contenu mature ou non ?
			$isMature = ($stream["channel"]["mature"] == true) ? 'CONTENU MATURE!' : 'JEU TOUT PUBLIC';
			
			// Array_Push sur $tableauItems
			array_push($tableauItems, array(
				// Channel
				"mature" 			=> 	$isMature,
				"status" 			=>	$stream["channel"]["status"],
				"display_name" 		=>	$stream["channel"]["display_name"],
				"name" 				=>	$stream["channel"]["name"],
				"game" 				=> 	$stream["channel"]["game"],
				"created_at" 		=>	$stream["channel"]["created_at"],
				"updated_at" 		=>	$stream["channel"]["updated_at"],
				"logo" 				=>	$stream["channel"]["logo"],
				"banner" 			=>	$stream["channel"]["banner"],
				"video_banner" 		=>	$stream["channel"]["video_banner"],
				"background" 		=>	$stream["channel"]["background"],
				"profile_banner" 	=>	$stream["channel"]["profile_banner"],
				"profile_banner_background_color" => $stream["channel"]["profile_banner_background_color"],
				"url" 				=>	$stream["channel"]["url"],
				"views" 			=>	$stream["channel"]["views"],
				"followers" 		=>	$stream["channel"]["followers"],
				// Featured
				"text" 				=> 	$stream["text"],
				// Previews
				"preview_small" 	=>	$stream["preview"]["small"],
				"preview_medium" 	=>	$stream["preview"]["medium"],
				"preview_large" 	=>	$stream["preview"]["large"],
				"preview_template" 	=>	$stream["preview"]["template"],
				// Current Stream
				"_id" 				=>	$stream["_id"],
				"stream_game" 		=>	$stream["game"],
				"stream_viewers" 	=>	$stream["viewers"],	
				// Videos, Char, Stream Key…
				"videos" 			=>	$stream["channel"]["_links"]["videos"],
					
					));
	
			}
			return $tableauItems;
		}



	
}



?>
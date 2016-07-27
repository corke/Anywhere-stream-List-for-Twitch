<?php
//require_once('twitchAPI.php');
class tlc_List{
    
	public function __construct(){
		add_shortcode('tlc_list', array($this, 'streamListTwitchWidget_html'));
		add_shortcode('tlc_list_all', array($this, 'streamListTwitchAll_html'));
		add_action('wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		}
	
	public function register_plugin_styles() {
		wp_register_style( 'streamList', plugin_dir_url( __FILE__ ).'/css/tlc_style.css'  );
		wp_enqueue_style( 'streamList' );
	}

	//DEBUT WidgetListStream
	public function streamListTwitchWidget_html($atts, $content){
		global $wpdb;
		//Récupération des options
		$OfflineSetting = get_option( 'tlc_show_offline' );
		$nbStream = 10;
		if(get_option( 'tlc_nb_stream' )){
			$nbStream = get_option( 'tlc_nb_stream' );
		}
		$redirect = get_option( 'tlc_redirection_stream' );
		$allStreamPage=get_option('tlc_page_allstream');
		// Nouvelle instance de Twitch_API
		//$twitchInit = new Twitch_API();
		//$username = array("momanus", "corke420", "gamersoriginow");
		$streamsOn=array();
		$streamsOff=array();
		$sql = "SELECT * FROM {$wpdb->prefix}tlc_streamer_list";
		$streamers = $wpdb->get_results($sql);
		//Pour chaque streamer de la liste : on va récupérer les infos des streams grâce à l'api twitch.
		foreach($streamers as $user) {
			//Récupération des informations (fixe : nom) du stream.
			//$json_file = @file_get_contents("https://api.twitch.tv/kraken/channels/{$user->streamerName}", 0, null, null);
			//$channel = json_decode($json_file, true);
			$channel = json_decode($user->chan_JSON_Data, true);
			
			//Récupération des informations (variable : viewers, on/off) du stream.
			//$json_file = @file_get_contents("https://api.twitch.tv/kraken/streams/{$channel[name]}", 0, null, null);
			//$json_array = json_decode($json_file, true);
			$json_array = json_decode($user->stream_JSON_Data, true);
			
			//Si le stream n'est pas en ligne
			if ($json_array['stream'] == null){
				//Si le stream n'existe pas
				if($channel["_id"] == ""){
					$dataStreamOff='<tr><td>'.'<div class="offlineButton"></div>'.'</td><td>'.$user->streamerName." : Il n'y a pas de channel comportant ce nom ! \n</td></tr>";
					$streamsOff[]=$dataStreamOff;
				//Si non si il existe
				}else{
					//Si on redirige sur le site = 1 si non on redirige sur twitch
					if(isset($redirect[checkbox_redirection]))  { 
					$dataStreamOff='<tr><td>'.'<div class="offlineButton"></div>'.'</td><td><a href="../'.$channel[name].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel[display_name]."\n".'</td></tr>';
					}else{
						$dataStreamOff='<tr><td>'.'<div class="offlineButton"></div>'.'</td><td><a href="http://twitch.tv/'.$channel[name].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel[display_name]."\n".'</td></tr>';	
					}
					$streamsOff[]=$dataStreamOff;
				}
			//Si le stream est en ligne
			} else {
				//Si on redirige sur le site = 1 si non on redirige sur twitch
				if(isset($redirect[checkbox_redirection])) { 
				$dataStreamOn='<tr><td>'.'<div class="onlineButton"></div>'.'</td><td><a href="../'.$channel[name].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel[display_name]." - ".$json_array['stream'][viewers]." viewers".'</a></td></tr>';
				}else{
					$dataStreamOn='<tr><td>'.'<div class="onlineButton"></div>'.'</td><td><a href="http://twitch.tv/'.$channel[name].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel[display_name]." - ".$json_array['stream'][viewers]." viewers".'</a></td></tr>';
				}
				$streamsOn[]=$dataStreamOn;
			}
		}
		//Création du tableau qui stock nos streams.
		$twitchList='<table class="twitchList_table">';
		$twitchList.= '<tr><th colspan="2">Streams</th></tr>';
		if(isset($OfflineSetting[checkbox_offline])) { 
			if(count($streamsOn)<$nbStream){
				for($i=0; $i<count($streamsOn); $i++){
					$twitchList.= $streamsOn[$i];
				}
				if(count($streamsOff)<$nbStream-count($streamsOn)){
					for($n=0; $n<count($streamsOff); $n++){
						$twitchList.= $streamsOff[$n];
					}
				}else{
					for($n=0; $n<$nbStream-count($streamsOn); $n++){
						$twitchList.= $streamsOff[$n];
					}
				}			
			}else{
				for($i=0; $i<$nbStream; $i++){
					$twitchList.= $streamsOn[$i];
				}
			}
		}else { 
			if(count($streamsOn)<$nbStream){
				for($i=0; $i<count($streamsOn); $i++){
					$twitchList.= $streamsOn[$i];
				}
			}else{
				for($i=0; $i<$nbStream; $i++){
					$twitchList.= $streamsOn[$i];
				}
			}
		}
	//Renvois vers la page qui liste tous les streams. $allStreamPage = page choisie dans les options.
	$twitchList.= '<tr><td colspan="2"><a href="../'.$allStreamPage.'" style="cursor:pointer;display:block;width:100%;height:100%;">Show all streams</a></td></tr>';
	$twitchList.= '</table>';
	//Fin tableau
	return $twitchList;
	}
	//Fin WidgetListStream
	
	//DEBUT PageListAllStream
	public function streamListTwitchAll_html($atts, $content){	
		global $wpdb;
		$redirect = get_option( 'tlc_redirection_stream' );
		// Nouvelle instance de Twitch_API
		//$twitchInit = new Twitch_API();
		$streamsOn=array();
		$streamsOff=array();
		$sql = "SELECT * FROM {$wpdb->prefix}tlc_streamer_list";
		$streamers = $wpdb->get_results($sql);
		foreach($streamers as $user) {
			$channel = json_decode($user->chan_JSON_Data, true);
			$json_array = json_decode($user->stream_JSON_Data, true);
			if ($json_array['stream'] == null){
				if($channel["_id"] == ""){
					$dataStreamOff='<tr><td>'.'<div class="offlineButton"></div>'.'</td><td>'.$user->streamerName." : Il n'y a pas de channel comportant ce nom ! \n</td></tr>";
					$streamsOff[]=$dataStreamOff;
				}else{
					if( isset($redirect['checkbox_redirection']) ) { 
					$dataStreamOff='<tr><td>'.'<div class="offlineButton"></div>'.'</td><td><a href="../'.$channel['name'].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel['display_name']."\n".'</td></tr>';
					}else{
						$dataStreamOff='<tr><td>'.'<div class="offlineButton"></div>'.'</td><td><a href="http://twitch.tv/'.$channel[name].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel[display_name]."\n".'</td></tr>';	
					}
					$streamsOff[]=$dataStreamOff;
				}
			} else {
				if( isset($redirect['checkbox_redirection'])) { 
				$dataStreamOn='<tr><td>'.'<div class="onlineButton"></div>'.'</td><td><a href="../'.$channel['name'].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel['display_name']." - ".$json_array['stream']['viewers']." viewers".'</a></td></tr>';
				}else{
					$dataStreamOn='<tr><td>'.'<div class="onlineButton"></div>'.'</td><td><a href="http://twitch.tv/'.$channel[name].'" style="cursor:pointer;display:block;width:100%;height:100%;">'.$channel[display_name]." - ".$json_array['stream'][viewers]." viewers".'</a></td></tr>';
				}
				$streamsOn[]=$dataStreamOn;
			}

		}
		$twitchList= '<table class="twitchList_table_all">';
		$twitchList.= '<tr><th colspan="2">Streams</th></tr>';


				for($i=0; $i<count($streamsOn); $i++){
					$twitchList.= $streamsOn[$i];
				}

				for($n=0; $n<count($streamsOff); $n++){
					$twitchList.= $streamsOff[$n];
				}
	$twitchList.= '</table>';	
	return $twitchList;
	}	
}
?>

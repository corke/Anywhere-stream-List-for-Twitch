<?php

/*
Plugin Name: AnywhereStreamList_forTwitch
Description: Plugin allow you to make list of your stream Twitch TV you can put everywhere with shortcode.
Author: Corke420
Author URI: http://themeforest.net/user/corke77
Version: 1.0
*/
class tlc_Plugin{
	public function __construct(){
		include_once plugin_dir_path( __FILE__ ).'/pagetitle.php';
        new tlc_Page_Title();
		include_once plugin_dir_path( __FILE__ ).'/list.php';
		new tlc_List();
		
		register_activation_hook(__FILE__, array($this, 'install'));
		
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_menu', array($this, 'add_admin_menu'));
		
		add_action('init ', array($this, 'cronstarter_activation'));
		register_deactivation_hook (__FILE__, array($this,'cronstarter_deactivate'));
		add_action ('cron_twitch', array($this,'__do_cron_script')); 
		add_filter( 'cron_schedules',  array($this,'cron_add_minute') );
		
		add_action( 'admin_init', array($this,'__admin_schedule_cron') );
	}
	// create a scheduled event (if it does not exist already)
	function __admin_schedule_cron() {
    if ( ! wp_next_scheduled( 'cron_twitch' ) ) {
	  wp_schedule_event(time(), 'everyminutes', 'cron_twitch');
	}
	}
	function __do_cron_script() {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}tlc_streamer_list";
		$streamers = $wpdb->get_results($sql);
		foreach($streamers as $user) {
			//Récupération des informations (fixe : nom) du stream.
			$chanInfo = @file_get_contents("https://api.twitch.tv/kraken/channels/{$user->streamerName}", 0, null, null);
			//JSONDECODE pour récupérer le nom du channel
			$chanInfo_decode = json_decode($chanInfo, true);
			//Récupération des informations (variable : viewers, on/off) du stream.
			$streamInfo = @file_get_contents("https://api.twitch.tv/kraken/streams/{$chanInfo_decode[name]}", 0, null, null);
			$wpdb->update($wpdb->prefix.'tlc_streamer_list', array( 'chan_JSON_Data' => $chanInfo, 'stream_JSON_Data' => $streamInfo ), array( 'streamerName' => $user->streamerName));
		}
	} 
	// unschedule event upon plugin deactivation
	 function cronstarter_deactivate() {	
		// Get the timestamp for the next event.
		$timestamp = wp_next_scheduled( 'cron_twitch' );
		wp_unschedule_event( $timestamp, 'cron_twitch' );
		// If you previously added for example
		// wp_schedule_single_event( time(), 'cron_twitch' );

		wp_clear_scheduled_hook( 'cron_twitch' );
	} 

	// add custom interval
	 function cron_add_minute( $schedules ) {
		// Adds once every minute to the existing schedules.
		$schedules['everyminutes'] = array(
	    'interval' => 60,
	    'display' => __( 'Once Every  Minutes' )
		);
		return $schedules;
	}
	//DEBUT
	//CREATION MENU ET INSTALLATION
	//||||||||||||||||||||||||||||||||||||||||||
	function add_admin_menu(){
		add_menu_page('TwitchList Customize', 'StreamList', 'manage_options', 'tlc', array($this, 'menu_html'));
		add_submenu_page('tlc', 'Réglages', 'Réglages', 'manage_options', 'tlc_reglages', array($this, 'submenu_html'));
	}

	function install(){
		global $wpdb;
		$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tlc_streamer_list (id INT AUTO_INCREMENT PRIMARY KEY, streamerName VARCHAR(255) NOT NULL, chan_JSON_Data TEXT, stream_JSON_Data TEXT);");
	}
	//FIN
	//CREATION MENU ET INSTALLATION
	//||||||||||||||||||||||||||||||||||||||||||
	//DEBUT
	//OPTION
	//||||||||||||||||||||||||||||||||||||||||||
	public function register_settings(){
		add_settings_section('tlc_streamlist_section', 'TwitchList Customize settings', array($this, 'section_settings_html'), 'tlc_streamlist_settings');
		register_setting('tlc_streamlist_settings', 'tlc_show_offline');
		add_settings_field('tlc_show_offline', 'Enable Offline streams', array($this, 'offline_html'), 'tlc_streamlist_settings', 'tlc_streamlist_section');
		register_setting('tlc_streamlist_settings', 'tlc_nb_stream');
		add_settings_field('tlc_nb_stream', 'Number of stream to be displayed', array($this, 'nbStream_html'), 'tlc_streamlist_settings', 'tlc_streamlist_section');
		register_setting('tlc_streamlist_settings', 'tlc_redirection_stream');
		add_settings_field('tlc_redirection_stream', 'Streams\'s redirect page', array($this, 'redirection_stream_html'), 'tlc_streamlist_settings', 'tlc_streamlist_section');
		register_setting('tlc_streamlist_settings', 'tlc_page_allstream');
		add_settings_field('tlc_page_allstream', 'View all streams page', array($this, 'page_allstream_html'), 'tlc_streamlist_settings', 'tlc_streamlist_section');

		add_settings_section('tlc_streamlist_section2', 'Managements of your streams', array($this, 'section_mgr_html'), 'tlc_streamlist_add');
		add_settings_field('tlc_add_streamer', 'Add Streamer', array($this, 'addStreamer_html'), 'tlc_streamlist_add', 'tlc_streamlist_section2');
	}
	//FIN
	//OPTION
	//||||||||||||||||||||||||||||||||||||||||||
	
	//DEBUT
	//FONCTION STREAMLISTTWITCH
	//||||||||||||||||||||||||||||||||||||||||||
	
	//DEBUTOPTIONS
	//Option permettant d'afficher les streams offline
	function offline_html() {
		$options = get_option( 'tlc_show_offline' );
		$html = '<input type="checkbox" id="checkbox_offline" name="tlc_show_offline[checkbox_offline]" value="1"' . checked(1 ,isset($options['checkbox_offline']), false ) . '/>';
		$html .= '<label for="checkbox_offline">Check to enable Offline streams</label>';
		echo $html;
		
	}
	//Option permettant de choisir le nombre de streams à afficher
	public function nbStream_html(){
		if(get_option('tlc_nb_stream')){
		$html='<input type="number" name="tlc_nb_stream" value="'.get_option('tlc_nb_stream').'"/>';		
		}else{			
		$html='<input type="number" name="tlc_nb_stream" value="10"/>';		
		}
		echo $html;
	}
	//Option permettant de choisir la page sur la quelle on désir afficher tous les streams
	public function page_allstream_html(){		
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix."posts where post_type='page'";
		$listPages = $wpdb->get_results($sql);
		$html='<select name="tlc_page_allstream" id="test" value="'.get_option('tlc_page_allstream').'">';
		foreach($listPages as $page){
			$html .= '<option>'.$page->post_title.'</option>';
		}
		$html .= '</select> ';
		$html .= '<label for="test">Select the display page of all your streams . (Remember to insert the shortcode [ tlc_list_all ] in it)</label>';
		echo $html;
	}
	//Option permettant de choisir si l'on redirige les streams sur notre site ou sur twitch
	public function redirection_stream_html(){
		$options = get_option( 'tlc_redirection_stream' );
		$html = '<input type="checkbox" id="checkbox_redirection" name="tlc_redirection_stream[checkbox_redirection]" value="1"' . checked(1 ,isset($options['checkbox_redirection']), false ) . '/>';
		$html .= '<label for="checkbox_redirection">Check to enable the redirection locally ( www.votresite.com/nomdustream )</label>';
		echo $html;
		
	}
	//FINOPTIONS
	
	//Fonction permettant l'ajout de streamer dans notre liste
	public function addStreamer_html(){
		$html='<input type="text" name="tlc_add_streamer"/>';
		echo $html;
		if (isset($_POST['tlc_add_streamer'])&!empty($_POST['tlc_add_streamer'])) {
			$this->add_streamer();
		}
		if (isset($_POST['tlc_add_streamer'])& empty($_POST['tlc_add_streamer'])) {
			echo" Veuillez renseigner le nom d'un stream.";
		}	
	}
	public function add_streamer(){
 		global $wpdb;
		$user=$_POST["tlc_add_streamer"];
		//Récupération des informations (fixe : nom) du stream.
			$chanInfo = @file_get_contents("https://api.twitch.tv/kraken/channels/{$user}", 0, null, null);
			//JSONDECODE pour récupérer le nom du channel
			$chanInfo_decode = json_decode($chanInfo, true);
			//Récupération des informations (variable : viewers, on/off) du stream.
			$streamInfo = @file_get_contents("https://api.twitch.tv/kraken/streams/{$chanInfo_decode[name]}", 0, null, null);
			//$wpdb->update($wpdb->prefix.'tlc_streamer_list', array( 'chan_JSON_Data' => $chanInfo, 'stream_JSON_Data' => $streamInfo ), array( 'streamerName' => $user->streamerName));
		$wpdb->insert($wpdb->prefix.'tlc_streamer_list',array('id' => '','streamerName' => $user, 'chan_JSON_Data' => $chanInfo, 'stream_JSON_Data' => $streamInfo),array('%d','%s','%s','%s'));
	}
	//Fonction permettant de récupérer la liste des streamers.
	public function show_streamers(){
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'tlc_streamer_list';
		$streamers = $wpdb->get_results($sql);
		return $streamers;
	}
	//Fonction permettant de suprimmer un streamer.
	public function delete_streamer($streamerName){
		global $wpdb;	
		$wpdb->delete($wpdb->prefix.'tlc_streamer_list', array('streamerName' => $streamerName));	
		unset($wpdb);
	}
	//DEBUTSECTION
	public function section_settings_html(){
		echo 'Choose the settings for your plugin AnywhereStreamList for Twitch</br>';
	}	
	public function section_mgr_html(){
		echo 'Insert or delete Twitch streams : </br> You have to insert the name of the stream visible in the adress bar </br> Ex : "http://www.twitch.tv/corke420" you should pick "corke420"</br>';
	}	
	//FINSECTION
	
	//FIN
	//FONCTION STREAMLISTTWITCH
	//||||||||||||||||||||||||||||||||||||||||||
	
	//DEBUT
	//Menu Page Principale
	//||||||||||||||||||||||||||||||||||||||||||
	public function menu_html(){
		echo '<h1>'.get_admin_page_title().'</h1>';
		echo '<h3>AnywhereStreamList_for Twitch : Dashboard</h3>';
		echo '<p>ShortCode :<br> [tlc_list] Displays your personalized streams list<br>[tlc_list_all]Displays all streams</p>';
		
		//Formulaire d'ajout de streamer.
		?><form method="post" action="">
		<?php settings_fields('tlc_streamlist_add') ?>
		<?php do_settings_sections('tlc_streamlist_add') ?>
		<?php submit_button(Add); ?>
		</form><?php
		//Récuperer la liste des streamers et les affiches
		$tab_streamers=$this->show_streamers();
		echo '<table class="wp-list-table widefat fixed striped users" width="100px" border="1">';
		echo '<tr><th colspan="2">Vos Streamers</th></tr>';
		for($i=0; $i<count($tab_streamers); $i++){
			echo'<tr><td>';
			print $tab_streamers[$i]->streamerName;
			echo'</td><td>';
			?><form method="post" action="">
			<input type="hidden" name="tlc_delete" value="delete"/>
			<input type="hidden" name="streamerName" value="<?php echo $tab_streamers[$i]->streamerName;?>"/>
			<?php submit_button(Delete); ?>
			</form><?php
			echo'</td></tr>';
		}
		echo '</table>';
		//Vérifie si il y a eu demande de suppression de stream.
		if (isset($_POST['tlc_delete'])) {
			$this->delete_streamer($_POST['streamerName']);
		}
	}
	//FIN
	//Menu Page Principale
	//||||||||||||||||||||||||||||||||||||||||||
	
	//DEBUT
	//Menu Page Réglages
	//||||||||||||||||||||||||||||||||||||||||||
	public function submenu_html(){
		echo '<h1>'.get_admin_page_title().'</h1>';

    ?><form method="post" action="options.php">
	<?php settings_fields('tlc_streamlist_settings') ?>
	<?php do_settings_sections('tlc_streamlist_settings') ?>

	<?php submit_button(); ?>
	</form><?php
    }
	//FIN
	//Menu Page Réglages
	//||||||||||||||||||||||||||||||||||||||||||
}
$tlc=new tlc_Plugin();


?>

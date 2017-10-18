<?php
/**
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe BuddyPres WooCommerce Settings
 * @version     1.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_BP_Woo_Settings{

    public static $instance;
    public static function init(){
    if ( is_null( self::$instance ) )
        self::$instance = new Vibe_BP_Woo_Settings();

        return self::$instance;
    }

    public function __construct(){
    	$this->settings = $this->get();
    	add_action('admin_menu',array($this,'add_vibe_buddypress_woocommerce_option'));
    }

    function get(){
    	return get_option('vibe_bp_woo_sync_settings');
    }

    function put($value){
    	update_option('vibe_bp_woo_sync_settings',$value);
    }

    function add_vibe_buddypress_woocommerce_option(){
    	add_options_page(__('Vibe Bp Woo Sync settings','vbc'),__('Vibe Bp Woo Sync','vbc'),'manage_options','vibe-bp-woo-sync',array($this,'add_settings'));
    }

    function add_settings(){
    	echo '<h3>'.__('Vibe Buddypress Woocommerce Settings','vbc').'</h3>';
		$settings = array(
				
				array(
					'label' => __('Map Fields','vbc'),
					'name' => 'bp_woo_fields_map',
					'type' => 'bp_woo_map',
					'desc' => __('Map WooCommerce and BuddyPress fields','vbc')
				),
			);
		$settings = apply_filters('vibe_bp_woo_sync_fields',$settings);
		if( isset($_POST['save_settings']) ){
			$this->save_form_fields();
		}
		$this->generate_form($settings);
    }

    function generate_form( $settings=array() ){
    	print_r($this->settings);
		echo '<form method="post">
				<table class="form-table">';
		wp_nonce_field('save_settings','_wpnonce');   
		echo '<ul class="save-settings">';

		foreach($settings as $setting ){
			echo '<tr valign="top">';
			global $wpdb,$bp;
			switch($setting['type']){
				case 'textarea': 
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><textarea name="'.$setting['name'].'">'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'</textarea>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'select':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'" class="chzn-select">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($this->settings[$setting['name']])?selected($key,$this->settings[$setting['name']]):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'checkbox':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($this->settings[$setting['name']])?'CHECKED':'').' />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'number':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'hidden':
					echo '<input type="hidden" name="'.$setting['name'].'" value="1"/>';
				break;
				case 'bp_woo_map':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><a class="add_new_map button">'.__('Add BuddyPress profile field map with WooCommerce profile fields','vbc').'</a>';

					global $bp,$wpdb;;
					$table =  $bp->profile->table_name_fields;
					$bp_fields = $wpdb->get_results("SELECT DISTINCT name FROM {$table}");

					
					$woo_fields = array(
						'first_name' => __('First Name','vbc'),
						'last_name' => __('Last Name','vbc'),
						'country' => __('Country','vbc'),
						'address' => __('Address','vbc'),
						'city' => __('Town / City','vbc'),
						'state' => __('State / Country','vbc'),
						'zip' => __('Postcode / Zip','vbc'),
						'phone' => __('Phone','vbc'),
						'email' => __('Email address','vbc'),

						);

					echo '<ul class="woo_bp_fields">';

					if(is_array($this->settings[$setting['name']]['field']) && count($this->settings[$setting['name']]['field'])){
						foreach($this->settings[$setting['name']]['field'] as $key => $field){
							echo '<li><label><select name="'.$setting['name'].'[woofield][]">';
							foreach($woo_fields as $k=>$v){
								echo '<option value="'.$k.'" '.(($field == $k)?'selected=selected':'').'>'.$v.'</option>';
							}
							echo '</select></label><select name="'.$setting['name'].'[bpfield][]">';
							foreach($bp_fields as $f){
								echo '<option value="'.$f->name.'" '.(($this->settings[$setting['name']]['bpfield'][$key] == $f->name)?'selected=selected':'').'>'.$f->name.'</option>';
							}
							echo '</select><span class="dashicons dashicons-no remove_field_map"></span></li>';
						}
					}
					echo '<li class="hide">';
					echo '<label><select rel-name="'.$setting['name'].'[woofield][]">';
					foreach($woo_fields as $k=>$v){
						echo '<option value="'.$k.'">'.$v.'</option>';
					}
					echo '</select></label>';
					echo '<select rel-name="'.$setting['name'].'[bpfield][]">';
					
					foreach($bp_fields as $f){
						echo '<option value="'.$f->name.'">'.$f->name.'</option>';
					}
					echo '</select>';
					echo '<span class="dashicons dashicons-no remove_field_map"></span></li>';
					echo '</ul></td>';
				break;
				default:
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
			}
			
			echo '</tr>';
		}
		echo '</tbody>
		</table>';
		echo '<input type="submit" name="save_settings" value="'.__('Save Settings','vbc').'" class="button button-primary" /></form>';
	}

	function save_form_fields(){

		if ( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'save_settings') ){
		    _e('Security check Failed. Contact Administrator.','vbc');
		    die();
		}
		unset($_POST['_wpnonce']);
		unset($_POST['_wp_http_referer']);
		unset($_POST['save_settings']);
		foreach($_POST as $key => $value){
			$this->settings[$key]=$value;
		}

		$this->put($this->settings);
	}

}


Vibe_BP_Woo_Settings::init();
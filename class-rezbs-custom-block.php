<?php
if (!class_exists('Rezbs_custom_block')) {
	
	class Rezbs_custom_block{

     	function __construct(){
     		add_action( 'admin_enqueue_scripts',[$this,'rezbs_enqueue_script']);
     		add_action( 'init',[$this,'rezbs_register_custom_block']);

			 add_action('wp_ajax_custom_block_render', [$this,'cdash_bus_directory_block_callback'] );
			 add_action('wp_ajax_nopriv_custom_block_render', [$this,'cdash_bus_directory_block_callback'] );
     	}
     	function rezbs_enqueue_script(){
     		if (!function_exists('register_block_type')) {
				return;
			}
     		wp_enqueue_script( 'rezbs-custom-block-js',plugins_url('/rezbs-connect/build/index.js'), array('wp-blocks','wp-editor','wp-components','wp-i18n'),'1.0.0', true);
     		wp_enqueue_script( 'rezbs-custom-icons-js',plugins_url('/rezbs-connect/js/icons.js'), array(),'1.0.0', true);
     		wp_localize_script( 'rezbs-custom-block-js', 'rezbs_custom_obj', 
			array(
				'all_trips' => $this->rezbs_retrun_api_data_html(),
				'ajaxurl' =>  admin_url( 'admin-ajax.php' ) 
			));
     	
     	}
     	function rezbs_register_custom_block(){
     		if (!function_exists('register_block_type')) {
				return;
			}
     		 register_block_type(
      			'rezbs-cus-block/custom-rezbs', [
	  			'editor_script' => 'rezbs-custom-block-js',
	          	'render_callback' => [$this,'cdash_bus_directory_block_callback'],
	          	'attributes'  => array(
		              'id'  => array(
		                  'type'  => 'integer',
		                  'default'=> 0
		              ),
		             'class'  => array(
		                  'type'  => 'string',
		                  'default'=> 'rezbs_button'
		              ),
		             'label'  => array(
		                  'type'  => 'string',
		                  'default'=> 'Book Now!'
		              ),
					  'url'  => array(
						'type'  => 'string',
						'default'=> ''
					  ),
	              
	              
	          	),
      			]
  			);
     	}

     	public function rezbs_retrun_api_data_html(){

     		$html=[];
     		$return='';
     		$api=new rezbs_API_Endpoint();
     		$options = get_option( 'rezbs_options' );
     		$options=(array)$api->rezbs_get_trips( $options['rezbs_api_key'] );
     		//$html.='<select className="all_trips" name="all_trips">';

     		if (!empty($options)) {
     			$html[]=['label'=>'Select a trip','value'=>'0', 'url' => ''];

				
				
	     		foreach ($options['alltrips'] as $key => $value) {
	     				$html[] = ['label' => $value->name, 'value' => $value->id, 'url' => $value->url];
	     			 	
	     		}
	     		
	     		$return=array_values($html);
	     		
     		}
			return json_encode($return);
     		
     	}

   		 // Hook server side rendering into render callback
		function cdash_bus_directory_block_callback($attributes){
			
			$atr=[];
			 if(!isset($attributes) || empty($attributes)){
			      $attributes=$atr;
			 }else{
			 	$atr['id']=intval($attributes['id']);
			 	$atr['class']=sanitize_text_field( $attributes['class'] );
			 	$atr['label']=sanitize_text_field( $attributes['label'] );
			 	$attributes=$atr;
			 }

		   return  rezbs_button_html($attributes);

		}
	}
new Rezbs_custom_block();
}
?>
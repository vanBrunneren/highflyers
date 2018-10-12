<?php

function check_compatibility() {
	global $wp_version;

	if ( ! version_compare( $wp_version, '5.0', '>=' ) and ! is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
		return false;
	}else{
		return true;
	}
}

add_action('admin_init','sfsi_plus_block_init');

function sfsi_plus_block_init(){
    if(check_compatibility()){
        add_action( 'enqueue_block_editor_assets', 'sfsi_plus_share_block_editor_assets' );
        add_action('enqueue_block_assets', 'sfsi_plus_share_block_assets');
        // add_action( 'plugins_loaded', 'sfsi_plus_register_block' ); 
    }
}

function sfsi_plus_share_block_editor_assets() {
    wp_enqueue_script(
        'sfsi-plus-share-block',
        plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
        array( 'wp-blocks', 'wp-i18n', 'wp-element' , 'jquery','wp-api'),
        '1'
        // filemtime( plugin_dir_path( 'js/block.js', __FILE__ ) )
    );

    wp_enqueue_style(
        'sfsi-plus-share-block-editor', // Handle.
        plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
        array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
        '1'
        // filemtime( plugin_dir_path( 'css/editor.css', __FILE__ ) )
    );
    wp_localize_script( 'sfsi-plus-share-block', 'plugin_url',plugins_url( 'icons_theme', __FILE__ )  );
}
function sfsi_plus_share_block_assets() {
    wp_enqueue_style(
        'sfsi-plus-share-block-frontend',
        plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
        array( 'wp-blocks' ),
        '1'
        // filemtime( plugin_dir_path( 'css/style.css', __FILE__ ) )
    );

    wp_register_script(
        'sfsi-plus-share-block-front',
        plugins_url( 'js/front.js', __FILE__ ),
        array( 'wp-blocks', 'wp-i18n', 'wp-element','jquery' ),
        '1'
        // filemtime( plugin_dir_path( 'js/front.js', __FILE__ ) )
    );

}

function sfsi_plus_register_icon_route(){
    register_rest_route(SFSI_PLUS_DOMAIN.'/v1','icons',array(
        'methods'=> WP_REST_Server::READABLE,
        'callback' => 'sfsi_plus_render_shortcode',
        'args'=>[
            "share_url"=>[
                "type"=>'string',
                "sanitize_callback" => 'sanitize_text_field'
            ]
        ]
    ));
}

add_action( 'rest_api_init', 'sfsi_plus_register_icon_route');

function sfsi_plus_render_shortcode(){
    ob_start();
    if(isset($_GET['ractangle_icon']) && 1==$_GET['ractangle_icon']){
        $returndata=DISPLAY_ULTIMATE_PLUS(null,null,$_GET['share_url']);
    }else{
        $returndata=DISPLAY_ULTIMATE_PLUS(null,null,$_GET['share_url']);
    }
    ob_clean();
    return rest_ensure_response($returndata);
}

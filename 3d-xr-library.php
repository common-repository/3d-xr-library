<?php

/**
 * Plugin Name:  WordPress 3d Model Viewer
 * Plugin URI: https://viitorcloud.com/
 * Description: Use this shortcode [3dmodelviewer] to display 3D Model.
 * Version:3.0.0
 * Author: VIITORCLOUD
 * Author URI: http://viitorcloud.com/
 * License: GPL2
 *
 * @package WordPress3DModelViewer
 */

define( 'ALLOW_UNFILTERED_UPLOADS', true );
add_action( 'wp_enqueue_scripts', 'XrlibraryScripts', 99 );
add_action( 'admin_enqueue_scripts', 'XrlibrarySelectivelyEnqueueAdminScript' );
/**
 * Load css and js
*/
if ( ! function_exists( 'XrlibraryScripts' ) ) {
	/**
	 * Load enqueue styles and js
	 */
	function XrlibraryScripts() {
		global $wp_query;
		wp_enqueue_style( 'xrd_mainstyle', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '', 'all' );
		wp_enqueue_style( 'xrd_bootstrapmin_css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), '', 'all' );
		wp_enqueue_style( 'xrd_fontawesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), '', 'all' );
		wp_enqueue_script( 'xrd_model-viewer', plugin_dir_url( __FILE__ ) . 'js/model-viewer.min.js', array(), false, true );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'xrd_bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array(), false, true );
		wp_enqueue_script( 'xrd_mycustom', plugin_dir_url( __FILE__ ) . 'js/mycustomjs.js', array(), false, true );
	}
}

if ( ! function_exists( 'XrlibrarySelectivelyEnqueueAdminScript' ) ) {
	/**
	 * Enqueue styles and js for admin
	 */
	function XrlibrarySelectivelyEnqueueAdminScript( $hook ) {

		wp_enqueue_style( 'vc_model_mainstyle', plugin_dir_url( __FILE__ ) . 'admin/css/model.css', array(), rand(), 'all' );
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_script( 'awscript', plugin_dir_url( __FILE__ ) . '/js/vcawscript.js', array( 'jquery' ), null, false );
	}
}

add_filter( 'script_loader_tag', 'XrlibraryAddTypeAttribute', 10, 2 );
if ( ! function_exists( 'XrlibraryAddTypeAttribute' ) ) {
	function XrlibraryAddTypeAttribute( $tag, $handle ) {
		if ( $handle == 'xrd_model-viewer' ) {
			return str_replace( '<script', '<script type="module"', $tag );
		}
		return $tag;
	}
}

add_action( 'init', 'XrlibraryPostTypeNews' );
if ( ! function_exists( 'XrlibraryPostTypeNews' ) ) {
	/**
	 * Create custom post type with name '3d Model'
	 */
	function XrlibraryPostTypeNews() {
		$supports = array(
			'title', // post title.
			'author', // post author.
		);
		$labels   = array(
			'name'           => _x( '3D Model', 'plural' ),
			'singular_name'  => _x( '3D Model', 'singular' ),
			'menu_name'      => _x( '3D Model', 'admin menu' ),
			'name_admin_bar' => _x( '3D Model', 'admin bar' ),
			'add_new'        => _x( 'Add New', 'add new' ),
			'add_new_item'   => __( 'Add New 3D Model' ),
			'new_item'       => __( 'New 3D Model' ),
			'edit_item'      => __( 'Edit 3D Model' ),
			'view_item'      => __( 'View 3D Model' ),
			'all_items'      => __( 'All 3D Model' ),
			'search_items'   => __( 'Search 3D Model' ),
			'not_found'      => __( 'No 3D Model found.' ),
		);
		$args     = array(
			'supports'     => $supports,
			'labels'       => $labels,
			'public'       => true,
			'query_var'    => true,
			'has_archive'  => true,
			'hierarchical' => false,
		);
		register_post_type( 'vc3dmodels', $args );
	}
}

if ( ! function_exists( 'XrlibraryMetaBoxMarkup' ) ) {
	/**
	 * Creating meta box
	 */
	function XrlibraryMetaBoxMarkup( $object ) {
		wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

		?>
	<div class="row model-metabox">
		<div class="form-group">
		<div class="group">
			<label for="meta-box-text">Upload Model (Allowed format .glb, .gltf)</label>
			<a href="#" class="aw_upload_image_button button button-secondary"><?php _e( 'Upload Image' ); ?></a><br />
			<input name="meta-box-modelimg" id="aw_custom_image" type="text" placeholder="Upload Model" value="<?php echo esc_html( get_post_meta( $object->ID, 'meta-box-modelimg', true ) ); ?>">
		</div>
		</div>
		<div class="form-group three-col">
		<div class="group">
			<label for="meta-box-text">Add Name of model</label>
			<input name="meta-box-modelname" type="text" placeholder="Name of model" value="<?php echo esc_html( get_post_meta( $object->ID, 'meta-box-modelname', true ) ); ?>">
		</div>
		<div class="group">
			<label for="meta-box-dropdown">Add Model Type</label>
			<input name="meta-box-modeltype" type="text" placeholder="Name of modeltype" value="<?php echo esc_html( get_post_meta( $object->ID, 'meta-box-modeltype', true ) ); ?>">

		</div>

		</div>
		<div class="form-group">
		<div class="group">
			<label for="meta-box-dropdown" class="textbox-label">Add Model Description</label>
			<textarea name="model-description" rows="4" cols="50"><?php echo esc_html( get_post_meta( $object->ID, 'model-description', true ) ); ?></textarea>
		</div>
		</div>
	</div>
		<?php
	}
}

if ( ! function_exists( 'XrlibraryCustomMetabox' ) ) {
	/**
	 * Save meta box
	 */
	function XrlibraryCustomMetabox( $post_id, $post, $update ) {
		if ( ! isset( $_POST['meta-box-nonce'] ) || ! wp_verify_nonce( $_POST['meta-box-nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$slug = 'vc3dmodels';
		if ( $slug != $post->post_type ) {
			return $post_id;
		}

		$meta_box_modelimg_value      = '';
		$meta_box_modelname_value     = '';
		$meta_box_modeltype_value     = '';
		$meta_box_description_value   = '';
		$meta_box_animatedmodel_value = '';

		if ( isset( $_POST['meta-box-modelimg'] ) ) {
			$meta_box_modelimg_value = sanitize_text_field( $_POST['meta-box-modelimg'] );
		}
		update_post_meta( $post_id, 'meta-box-modelimg', $meta_box_modelimg_value );

		if ( isset( $_POST['meta-box-modelname'] ) ) {
			$meta_box_modelname_value = sanitize_text_field( $_POST['meta-box-modelname'] );
		}
		update_post_meta( $post_id, 'meta-box-modelname', $meta_box_modelname_value );

		if ( isset( $_POST['meta-box-modeltype'] ) ) {
			$meta_box_modeltype_value = sanitize_text_field( $_POST['meta-box-modeltype'] );
		}
		update_post_meta( $post_id, 'meta-box-modeltype', $meta_box_modeltype_value );
		if ( isset( $_POST['model-description'] ) ) {
			$meta_box_description_value = sanitize_textarea_field( $_POST['model-description'] );
		}
		update_post_meta( $post_id, 'model-description', $meta_box_description_value );
		if ( isset( $_POST['meta-box-animatedmodel'] ) ) {
			$meta_box_animatedmodel_value = sanitize_text_field( $_POST['meta-box-animatedmodel'] );
		}
		update_post_meta( $post_id, 'meta-box-animatedmodel', $meta_box_animatedmodel_value );
	}
}

add_action( 'save_post', 'XrlibraryCustomMetabox', 10, 3 );

if ( ! function_exists( 'XrlibraryAddMetabox' ) ) {
	function XrlibraryAddMetabox() {
		add_meta_box( 'demo-meta-box', '3D Model Data', 'XrlibraryMetaBoxMarkup', 'vc3dmodels', 'normal', 'high', null );
	}
}

add_action( 'add_meta_boxes', 'XrlibraryAddMetabox' );

/*
 * Display shortcode in template
 */
if ( ! is_admin() ) {
	add_shortcode( '3dmodelviewer', 'XrlibraryShortcode' );
}
function XrlibraryShortcode( $atts ) {
	ob_start();

	$args        = array(
		'post_type'      => 'vc3dmodels',
		'posts_per_page' => -1,
	);
	$model_posts = new WP_Query( $args );
	global $post;
	if ( $model_posts->have_posts() ) :
		$loop = 1;
		?>

	<div class="container-fluid alignwide">
		<div class="row">
		<?php
		while ( $model_posts->have_posts() ) :
			$model_posts->the_post();
			$model_img  = esc_html( get_post_meta( $post->ID, 'meta-box-modelimg', true ) );
			$model_name = esc_html( get_post_meta( $post->ID, 'meta-box-modelname', true ) );
			$model_type = esc_html( get_post_meta( $post->ID, 'meta-box-modeltype', true ) );
			$model_desc = esc_html( get_post_meta( $post->ID, 'model-description', true ) );
			if ( ! empty( $model_img ) || ! empty( $model_name ) || ! empty( $model_type ) ) {
				?>
			<div class="col-sm-4 mb-4">
				<div class="card">
				<model-viewer class="model-class" ar data-id="<?php echo $loop; ?>" src="<?php echo $model_img; ?>" alt="boat" auto-rotate camera-controls></model-viewer>
					<?php if ( ! empty( $model_name ) || ! empty( $model_type ) ) { ?>
					<div class="model-info">
						<?php if ( ! empty( $model_name ) ) { ?>
						<h5 class="font-color">Name of model : <?php echo $model_name; ?></h5>
					<?php } ?>
						<?php if ( ! empty( $model_type ) ) { ?>
						<h6>Model Type : <?php echo $model_type; ?></h6>
					<?php } ?>
					</div>
				<?php } ?>
				</div>
			</div>
			<?php } ?>
			<?php
			++$loop;
			endwhile;
		wp_reset_query();
		?>
		</div>
	</div>

	<div>
		<div class="model myModel">
		<div class="modal-content">
			<div class="modal-header">
			<h2 class="modal-title" id="exampleModalLabel">3D Models</h2>
			<span class="glyphicon closeIcon">&times;</span>
			</div>
			<div class="modal-body">
			<div class="row">
				<div class="col-md-6">
				<model-viewer id="model-popup" class="change-color" style="width:100%;height: 400px" src="" alt="data" environment-image="neutral" animation-name="Running" autoplay auto-rotate camera-controls shadow-intensity="1"></model-viewer>
				</div>
				<div class="col-md-6 separator">
				<div class="right-content"></div>
				<div class="controls" id="color-controls">
					<span>Apply color : </span>
					<button data-color="1,0,0,1" class="btn btn-default">Red</button>
					<button data-color="0,1,0,1" class="btn btn-default">Green</button>
					<button data-color="0,0,1,1" class="btn btn-default">Blue</button>
				</div>
				<div class="controls" style="display: flex; align-items:center;">
					<p for="neutral">Neutral Lighting: </p>
					<input id="neutral" class="largerCheckbox" type="checkbox" checked="true" />
				</div>
				<div class="controls">
					<button style="font-size:16px" id="download-model" class="btn btn-default">Download <i class="fas fa-download"></i></button>
				</div>
				</div>
			</div>
			</div>

		</div>

		</div>
	</div>
	<?php endif; ?>

	<?php
	$model_posts1 = new WP_Query( $args );
	$loop1        = 1;
	$model_vals   = array();
	?>
	<?php if ( $model_posts1->have_posts() ) : ?>
	<script>
		var modelData = [
		<?php
		while ( $model_posts1->have_posts() ) :
			$model_posts1->the_post();
			$model_img1    = get_post_meta( $post->ID, 'meta-box-modelimg', true );
			$model_name1   = get_post_meta( $post->ID, 'meta-box-modelname', true );
			$model_type1   = get_post_meta( $post->ID, 'meta-box-modeltype', true );
			$model_desc1   = get_post_meta( $post->ID, 'model-description', true );
			$model_animate = get_post_meta( $post->ID, 'meta-box-animatedmodel', true );
			?>
							{
			id: <?php echo $loop1; ?>,
			name: "<?php echo $model_name1; ?>",
			type: "<?php echo $model_type1; ?>",
			description: "<?php echo $model_desc1; ?>",
			animated_model: "yes"
			},
			<?php
			++$loop1;
						endwhile;
		?>
		]
	</script>
	<?php endif; ?>

	<?php
	$myvariable = ob_get_clean();
	return $myvariable;
	ob_end_clean();
}
/**
 * Allow mimes type
*/
if ( ! function_exists( 'XrlibraryCustomMimeTypes' ) ) {
	function XrlibraryCustomMimeTypes( $mimes ) {

		// New allowed mime types.
		$mimes['gltf'] = 'model/gltf+json';
		$mimes['glb']  = 'model/gltf-binary';

		return $mimes;
	}
}
add_filter( 'upload_mimes', 'XrlibraryCustomMimeTypes' );
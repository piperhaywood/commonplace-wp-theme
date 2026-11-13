<?php

// Vite is opt-in and restricted to local/development WordPress environments.
function cp_vite_origin() {
	if ( defined( 'CP_VITE_DEV_SERVER' ) && function_exists( 'wp_get_environment_type' ) &&
		in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
		return untrailingslashit( CP_VITE_DEV_SERVER );
	}
	return false;
}

// Fixed filenames use modification times to invalidate browser caches.
function cp_asset_version( $path ) {
	$file = get_template_directory() . '/' . $path;
	return is_file( $file ) ? filemtime( $file ) : null;
}

// Mark only this theme's handles as ES modules, including on older WordPress.
add_filter( 'script_loader_tag', 'cp_module_script_tag', 10, 3 );
function cp_module_script_tag( $tag, $handle, $src ) {
	if ( strpos( $handle, 'cp-module-' ) !== 0 ) {
		return $tag;
	}
	// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Adds type=module to an already enqueued script; URL and ID are escaped below.
	return '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>' . "\n";
}

add_action( 'wp_enqueue_scripts', 'cp_assets', 10 );
function cp_assets() {
	// Fonts are static theme assets, shared by development and production.
	wp_enqueue_style(
		'cp-fonts',
		get_template_directory_uri() . '/fonts/fonts.css',
		array(),
		cp_asset_version( 'fonts/fonts.css' )
	);

	$origin = cp_vite_origin();
	if ( $origin ) {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite serves live development assets; do not append a version.
		wp_enqueue_script( 'cp-module-client', $origin . '/@vite/client', array(), null, true );
		// Keep the stylesheet before WordPress Additional CSS, just as in production.
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite serves live development assets; do not append a version.
		wp_enqueue_style( 'cp-styles', $origin . '/css/main.scss?direct', array( 'cp-fonts' ), null );
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite serves live development assets; do not append a version.
		wp_enqueue_script( 'cp-module-main', $origin . '/js/main.js', array( 'cp-module-client' ), null, true );
		return;
	}

	wp_enqueue_style(
		'cp-styles',
		get_template_directory_uri() . '/dist/style.css',
		array( 'cp-fonts' ),
		cp_asset_version( 'dist/style.css' )
	);
	wp_enqueue_script(
		'cp-module-main',
		get_template_directory_uri() . '/dist/scripts.js',
		array(),
		cp_asset_version( 'dist/scripts.js' ),
		true
	);
}

add_action( 'admin_notices', 'cp_missing_assets_notice' );
function cp_missing_assets_notice() {
	if ( ! cp_vite_origin() && current_user_can( 'edit_theme_options' ) &&
		( ! is_file( get_template_directory() . '/dist/style.css' ) ||
		! is_file( get_template_directory() . '/dist/scripts.js' ) ||
		! is_file( get_template_directory() . '/dist/editor.css' ) ) ) {
		echo '<div class="notice notice-error"><p>Commonplace assets are missing. Run npm ci and npm run build in the theme repository before packaging the theme.</p></div>';
	}
}

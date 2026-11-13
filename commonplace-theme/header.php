<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="referrer" content="origin-when-cross-origin" />
	<?php wp_head(); ?>
	</head>

	<body <?php body_class( implode( ' ', array( 'is-not-tabbing' ) ) ); ?>>

	<header id="top" class="header">
		<div class="container container--xl">
		<a class="visuallyhidden button" href="#main"><?php esc_html_e( 'Skip to main content', 'commonplace' ); ?></a>
		</div>

		<?php $items = wp_get_nav_menu_items( 'header' ); ?>
		<nav class="nav">
		<?php
		wp_nav_menu(
			array(
				'theme_location'  => 'cp-menu',
				'container_class' => 'header-menu',
				'depth'           => 1,
			)
		);
		?>
		<?php if ( is_active_sidebar( 'header-area-1' ) ) : ?>
			<div class="header__widget cp-widget cp-widget--header-1">
			<?php dynamic_sidebar( 'header-area-1' ); ?>
			</div>
		<?php endif; ?>
		</nav>


		<?php if ( is_archive() ) : ?>
		<h1 class="visuallyhidden">
			<?php the_archive_title(); ?>
		</h1>
		<?php endif; ?>

		<?php $archive_title = cp_archive_str( false ); ?>
		<?php $archive_description = cp_archivedesc( false ); ?>
		<?php if ( $archive_title || $archive_description ) : ?>
		<div class="page-context">
			<?php if ( $archive_title ) : ?>
			<div class="breadcrumb">
				<p>
				<a href="<?php bloginfo( 'url' ); ?>" aria-label="Return home">..</a> / <?php echo wp_kses_post( $archive_title ); ?>
				</p>
			</div>
			<?php endif; ?>

			<?php $archive_description = cp_archivedesc( false ); ?>
			<?php if ( $archive_description ) : ?>
			<div class="page-description prose">
				<?php echo wp_kses_post( $archive_description ); ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</header>

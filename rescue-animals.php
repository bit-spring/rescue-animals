<?php 
/*Plugin Name: Rescue Animals
Description: Provides a custom post type for Rescue Animals.
Version: 1.0
Author: Bit Spring
Author URI: http://bit-spring.com/
License: GPLv2
*/
 
define( 'RESCUE_ANIMALS__PLUGIN_DIR', plugin_dir_url( __FILE__ ) );


// CUSTOM POST TYPE

add_action('init', 'rescue_animals_register');
function rescue_animals_register() {   
	
	$labels = array( 
		'name' => _x('Rescue Animals', 'post type general name'), 
		'singular_name' => _x('Rescue Animal', 'post type singular name'), 
		'add_new' => _x('Add New', 'Rescue Animal'), 
		'add_new_item' => __('Add New Rescue Animal'), 
		'edit_item' => __('Edit Rescue Animal'), 
		'new_item' => __('New Rescue Animal'), 
		'view_item' => __('View Rescue Animal'), 
		'search_items' => __('Search Rescue Animals'), 
		'not_found' => __('Nothing found'), 
		'not_found_in_trash' => __('Nothing found in Trash'), 
		'parent_item_colon' => '' 
	);   
		
	$args = array( 
		'labels' => $labels, 
		'public' => true, 
		'publicly_queryable' => true, 
		'exclude_from_search' => false,
		'show_ui' => true, 
		'query_var' => true, 
		'menu_icon' => RESCUE_ANIMALS__PLUGIN_DIR . 'assets/img/icon.png', 
		'rewrite' => true, 
		'capability_type' => 'post', 
		'hierarchical' => false, 
		'menu_position' => null,
		'can_export'    => true,
        'has_archive'   => true,
		'taxonmies' => array('animal_tags', 'post_tag'),
		'supports' => array('title', 'excerpt', 'editor', 'thumbnail', 'revisions')
	);   
	
	register_post_type( 'rescue-animals' , $args ); 
}



// THUMBNAIL IMAGES

if ( function_exists( 'add_image_size' ) ) { 
	add_image_size( 'animal-thumb', 300, 300, true ); //(cropped)
}


// TAXONOMIES

// hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_rescued_animal_taxonomies', 0 );

// create taxonomies for the post type "book"
function create_rescued_animal_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Gender', 'taxonomy general name' ),
		'singular_name'     => _x( 'Gender', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Genders' ),
		'all_items'         => __( 'All Genders' ),
		'parent_item'       => __( 'Parent Gender' ),
		'parent_item_colon' => __( 'Parent Gender:' ),
		'edit_item'         => __( 'Edit Gender' ),
		'update_item'       => __( 'Update Gender' ),
		'add_new_item'      => __( 'Add New Gender' ),
		'new_item_name'     => __( 'New Gender Name' ),
		'menu_name'         => __( 'Gender' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'Gender' ),
	);

	//register_taxonomy( 'gender', array( 'rescue-animals' ), $args );
	
	
	
	// Add new taxonomy, make it non-hierarchical (like post)
	$labels = array(
		'name'              => _x( 'Attributes', 'taxonomy general name' ),
		'singular_name'     => _x( 'Attribute', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Attributes' ),
		'all_items'         => __( 'All Attributes' ),
		'parent_item'       => __( 'Parent Attribute' ),
		'parent_item_colon' => __( 'Parent Attribute:' ),
		'edit_item'         => __( 'Edit Attribute' ),
		'update_item'       => __( 'Update Attribute' ),
		'add_new_item'      => __( 'Add New Attribute' ),
		'new_item_name'     => __( 'New Attribute Name' ),
		'menu_name'         => __( 'Attributes' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => false
	);

	register_taxonomy( 'animal_tags', array( 'rescue-animals' ), $args );
	
	
}



/*-------------------------------------------------------------------------------
	Custom Columns
-------------------------------------------------------------------------------*/

function rescue_columns($columns)
{
	$columns = array(
		'cb'	 	=> '<input type="checkbox" />',
		'thumbnail'	=> 'Thumbnail',
		'status'	=> 'Status',
		'title' 	=> 'Title',
		'featured' 	=> 'Featured',
		'author'	=> 'Author',
		'date'		=> 'Date',
	);
	return $columns;
}

function my_custom_columns( $column ) {
    global $post;

    if ( $column === 'thumbnail' ) {
        echo get_the_post_thumbnail( $post->ID, [100, 100] );
        return;
    }

    if ( $column === 'featured' ) {
        echo get_field( 'featured', $post->ID ) ? 'Yes' : 'No';
        return;
    }

    if ( $column === 'status' ) {
        echo get_field( 'status', $post->ID );
        return;
    }
}


add_action("manage_posts_custom_column", "my_custom_columns");
add_filter("manage_edit-rescue-animals_columns", "rescue_columns");










// SHORT CODES

// [random_testimonial]
function testimonial_random( $atts ){
	
	$output = '<span class="drop-quote">"</span>';
			
			$args = array( 'post_type' => 'testimonial', 'posts_per_page' => 1, 'orderby' => 'rand' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post(); 
				$output .= get_the_excerpt();
			endwhile;
	$output .= '" <br>';
	$output .= '- ' . get_field('author');
	
	return $output;
}

add_shortcode( 'random_testimonial', 'testimonial_random' );









// [animal_list status="Available" happy_tail=0 per_page=100]
function animal_list_func( $atts ){
	
	$output = '<div class="animal_list">';
	$output .= '<div class="flex-row">';
	
	$atts = shortcode_atts( array(
		'status' => 'Available',
		'happy_tail' => '0',
		'per_page' => '100'
	), $atts, 'animal_list' );
			
	$args = array( 
		'post_type' => 'rescue-animals', 
		'posts_per_page' => $atts['per_page'], 
		'orderby' => 'date',
		'order' => 'DESC',
		
		
		'meta_query'	=> array(
			'relation'		=> 'AND',
			array(
				'key'		=> 'status',
				'value'		=> $atts['status'],
				'compare'	=> '='
			),
			array(
				'key'		=> 'happy_tail',
				'value'		=> $atts['happy_tail'],
				'compare'	=> '='
			),
		)
	);
	
	$counter = 0;
	
	$loop = new WP_Query( $args );
	while ( $loop->have_posts() ) : $loop->the_post(); 
		
		if ($counter == 3) {
			$output .= '</div><div class="flex-row">';
			$counter = 0;
		}
		
		$counter++;
		
		$image = get_field('main_image');
		$image = get_the_post_thumbnail( $post->ID, 'thumbnail');
		
		$classes = "animal";
		
		if ($atts['happy_tail'] == 0) {
			$classes .= " happy-tail";
		}
		
		$output .= '<a href="' . get_the_permalink() . '" title="Adopt ' . get_the_title() . '" class="' . $classes . '">';
		$output .= '<div class="animal_content">';
		if ( has_post_thumbnail() ) {
		    $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
		    if ( ! empty( $large_image_url[0] ) ) {
		        $output .=  get_the_post_thumbnail( $post->ID, 'animal-thumb' ); 
		    }
		}
		$output .= '<h2>' . get_the_title() . '</h2>';
		
		if ($atts['happy_tail'] == 0) {
			$output .= '<p>' . get_field('breed') . '</p>';
		}
		
		$output .= '</div><!-- .animal_content -->';
		$output .= '</a><!-- .animal -->';
	endwhile;
		
	$output .= '</div><!-- .row -->';
	$output .= '</div><!-- .animal_list -->';
	
	return $output;
}

add_shortcode( 'animal_list', 'animal_list_func' );





/**
 * Animal Grid Shortcode
 *  [animal_list status="Available" happy_tail=0 per_page=100]
 */

add_shortcode( 'animal_grid', 'animal_grid_shortcode' );

function animal_grid_shortcode( $atts ) {
    return animal_grid($atts);
}

function animal_grid( $atts ) {

    $atts = shortcode_atts([
        'status'      => '',
        'excluded'    => '',
        'happy_tail'  => '0',
        'per_page'    => '100',
    ], $atts, 'animal_grid');

    // Unique key for this parameter set
    $cache_key = 'animal_grid_' . md5( serialize( $atts ) );

    // Attempt to load cache
    $cached = get_transient( $cache_key );
    if ( $cached !== false ) {
        return $cached;
    }

    // Build meta_query
    $meta_query = ['relation' => 'AND'];

    if ( $atts['status'] !== '' ) {
        $meta_query[] = [
            'key'     => 'status',
            'value'   => $atts['status'],
            'compare' => '='
        ];
    }

    if ( $atts['happy_tail'] !== '' ) {
        $meta_query[] = [
            'key'     => 'happy_tail',
            'value'   => $atts['happy_tail'],
            'compare' => '='
        ];
    }

    if ( $atts['excluded'] !== '' ) {
        $meta_query[] = [
            'key'     => 'status',
            'value'   => explode(',', $atts['excluded']),
            'compare' => 'NOT IN'
        ];
    }

    $query = new WP_Query([
        'post_type'               => 'rescue-animals',
        'posts_per_page'          => (int) $atts['per_page'],
        'orderby'                 => 'date',
        'order'                   => 'DESC',
        'meta_query'              => $meta_query,
        'no_found_rows'           => true,
        'update_post_meta_cache'  => false,
        'update_post_term_cache'  => false,
    ]);

    $output = '<div class="animal-grid flex-row">';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $output .= \App\template('partials.content-animal');
        }
    }

    wp_reset_postdata();

    $output .= '</div>';

    // Cache for 12 hours
    set_transient( $cache_key, $output, 12 * HOUR_IN_SECONDS );

    return $output;
}

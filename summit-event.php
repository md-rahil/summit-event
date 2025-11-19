<?php

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'se_register_summits' );
function se_register_summits() {
    register_post_type( 'summit', [
        'labels' => ['name' => 'Summits', 'singular_name' => 'Summit'],
        'public' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'events'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('industry', 'summit', [
        'label' => 'Target Industries',
        'hierarchical' => true,
        'show_in_rest' => true
    ]);
}

add_action( 'add_meta_boxes', 'se_add_meta_boxes' );
function se_add_meta_boxes() {
    add_meta_box( 'summit_data', 'Event Configuration', 'se_render_metabox', 'summit', 'normal', 'high' );
}

function se_render_metabox( $post ) {
    wp_nonce_field( 'save_summit_meta', 'se_nonce' );

    $meta = get_post_meta( $post->ID );
    $date = isset($meta['_se_date'][0]) ? $meta['_se_date'][0] : '';
    $loc = isset($meta['_se_loc'][0]) ? $meta['_se_loc'][0] : '';
    $highlights = isset($meta['_se_highlights'][0]) ? $meta['_se_highlights'][0] : '';
    $why_attend = isset($meta['_se_why'][0]) ? $meta['_se_why'][0] : '';

    ?>
    <style>
        .se-field { margin-bottom: 15px; }
        .se-field label { display: block; font-weight: 600; margin-bottom: 5px; }
        .se-field input, .se-field textarea { width: 100%; padding: 8px; border:1px solid #ccc; border-radius:4px; }
    </style>

    <div class="se-box">
        <div class="se-field">
            <label>Event Date</label>
            <input type="text" name="se_date" value="<?php echo esc_attr($date); ?>" placeholder="e.g. Sept 11-12, 2023">
        </div>
        <div class="se-field">
            <label>Location</label>
            <input type="text" name="se_loc" value="<?php echo esc_attr($loc); ?>" placeholder="e.g. Riyadh Marriott Hotel">
        </div>
        <div class="se-field">
            <label>Topic Highlights (One per line)</label>
            <textarea name="se_highlights" rows="5"><?php echo esc_textarea($highlights); ?></textarea>
        </div>
        <div class="se-field">
            <label>Why Attend?</label>
            <?php wp_editor( $why_attend, 'se_why', ['media_buttons' => false, 'textarea_rows' => 6] ); ?>
        </div>
    </div>
    <?php
}

add_action( 'save_post', 'se_save_summit' );
function se_save_summit( $post_id ) {
    if ( ! isset( $_POST['se_nonce'] ) || ! wp_verify_nonce( $_POST['se_nonce'], 'save_summit_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    
    if ( isset($_POST['se_date']) ) update_post_meta( $post_id, '_se_date', sanitize_text_field($_POST['se_date']) );
    if ( isset($_POST['se_loc']) ) update_post_meta( $post_id, '_se_loc', sanitize_text_field($_POST['se_loc']) );
    if ( isset($_POST['se_highlights']) ) update_post_meta( $post_id, '_se_highlights', sanitize_textarea_field($_POST['se_highlights']) );
    if ( isset($_POST['se_why']) ) update_post_meta( $post_id, '_se_why', wp_kses_post($_POST['se_why']) );
}

add_filter( 'the_content', 'se_display_summit_layout' );
function se_display_summit_layout( $content ) {
    
    if ( ! is_singular('summit') || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $post_id = get_the_ID();
    $date = get_post_meta( $post_id, '_se_date', true );
    $loc = get_post_meta( $post_id, '_se_loc', true );
    $topics_raw = get_post_meta( $post_id, '_se_highlights', true );
    $why_attend = get_post_meta( $post_id, '_se_why', true );
    $bg_img = get_the_post_thumbnail_url( $post_id, 'full' );

    $css = "
        <style>
            .summit-hero { 
                background: #1a2b49 url('$bg_img') center/cover no-repeat; 
                background-blend-mode: multiply;
                color: #fff; padding: 80px 20px; text-align: center; border-radius: 8px; margin-bottom: 40px;
            }
            .summit-hero h1 { color: white; margin-bottom: 10px; font-size: 2.5rem; line-height: 1.2; }
            .summit-meta { font-size: 1.1rem; opacity: 0.9; margin-bottom: 25px; font-weight: bold; color: #4db5ff; }
            .cta-btn { background: #e91e63; color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: bold; display: inline-block; transition: 0.3s; }
            .cta-btn:hover { background: #c2185b; color: white; transform: translateY(-2px); }
            .section-head { border-left: 4px solid #0073aa; padding-left: 15px; margin: 40px 0 20px; color: #333; }
            .topic-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
            .topic-card { background: #f4f4f4; padding: 15px; border-radius: 5px; border-left: 3px solid #e91e63; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
            .attend-box { background: #eef6fc; padding: 25px; border-radius: 8px; }
        </style>
    ";

    $html = '<div class="summit-wrapper">';
    
    $html .= "<div class='summit-hero'>";
    $html .= "<h1>" . get_the_title() . "</h1>";
    if($date || $loc) {
        $html .= "<div class='summit-meta'>$date &nbsp;|&nbsp; $loc</div>";
    }
    $html .= "<a href='#' class='cta-btn'>Register Now</a>";
    $html .= "</div>";

    $html .= "<h3 class='section-head'>Event Overview</h3>";
    $html .= $content;

    if ( ! empty($topics_raw) ) {
        $html .= "<h3 class='section-head'>Topic Highlights</h3><div class='topic-grid'>";
        $topics = explode("\n", $topics_raw);
        foreach ($topics as $t) {
            if(trim($t)) $html .= "<div class='topic-card'>" . esc_html($t) . "</div>";
        }
        $html .= "</div>";
    }

    if ( $why_attend ) {
        $html .= "<h3 class='section-head'>Why Attend?</h3>";
        $html .= "<div class='attend-box'>" . wp_kses_post($why_attend) . "</div>";
    }

    $html .= '</div>';

    return $css . $html;
}
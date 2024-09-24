<?php

/**
 * Plugin Name: A Products
 * Description: A simple custom product list with pagination.
 * Version: 1.0
 * Author: Adrian Salguet
 */

class Custom_Product_Plugin
{

    public function __construct()
    {
        add_action('init', array($this, 'register_custom_post_type'));
        add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
        add_action('save_post', array($this, 'save_custom_meta'));
        add_shortcode('display_products', array($this, 'display_products_shortcode'));
    }

    public function register_custom_post_type()
    {
        $args = array(
            'public' => true,
            'label'  => 'Static Products',
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-cart',
        );
        register_post_type('static_product', $args);
    }

    public function add_custom_meta_box()
    {
        add_meta_box(
            'product_meta_box',
            'Product Details',
            array($this, 'render_meta_box'),
            'static_product',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post)
    {
        $price = get_post_meta($post->ID, '_product_price', true);
        $description = get_post_meta($post->ID, '_product_description', true);
        $link = get_post_meta($post->ID, '_vlink', true);
        $display_title = get_post_meta($post->ID, '_display_title', true);

        echo '<div style="display: flex;flex-direction: column;">';

        echo '<div style="margin-bottom: 20px;">';
        echo '<label for="display_title">Display Title on Grid:</label>';
        echo '<input type="checkbox" id="display_title" name="display_title" ' . checked($display_title, 'true', false) . ' />';
        echo '</div>';

        echo '<div style="margin-bottom:10px;">';
        echo '<label for="product_price">Product Price:</label>';
        echo '<input type="text" id="product_price" name="product_price" value="' . esc_attr($price) . '" style="width:100%;" />';
        echo '</div>';

        echo '<div style="margin-bottom:10px;">';
        echo '<label for="product_description">Product Description:</label>';
        echo '<textarea id="product_description" name="product_description" style="width:100%;" rows="4">' . esc_textarea($description) . '</textarea>';
        echo '</div>';

        echo '<div style="margin-bottom:10px;">';
        echo '<label for="product_description">Product Link:</label>';
        echo '<input type="url" id="product_description" name="vlink" value="' . $link . '" style="width:100%;" required>';
        echo '</div>';

        echo '</div>';
    }

    public function save_custom_meta($post_id)
    {
        if (isset($_POST['product_price'])) {
            update_post_meta($post_id, '_product_price', sanitize_text_field($_POST['product_price']));
        }

        if (isset($_POST['product_description'])) {
            update_post_meta($post_id, '_product_description', sanitize_textarea_field($_POST['product_description']));
        }

        if (isset($_POST['vlink'])) {
            update_post_meta($post_id, '_vlink', sanitize_textarea_field($_POST['vlink']));
        }
        $display_title = isset($_POST['display_title']) ? 'true' : 'false';
        update_post_meta($post_id, '_display_title', $display_title);
    }

    public function display_products_shortcode()
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $args = array(
            'post_type' => 'static_product',
            'posts_per_page' => 16,
            'paged' => $paged,
        );

        $products = new WP_Query($args);

        if ($products->have_posts()) {
            echo '<div class="v-product-list">';
            while ($products->have_posts()) {
                $products->the_post();

                $price = get_post_meta(get_the_ID(), '_product_price', true);
                $description = get_post_meta(get_the_ID(), '_product_description', true);
                $display_title = get_post_meta(get_the_ID(), '_display_title', true);
                $vlink = get_post_meta(get_the_ID(), '_vlink', true);

                echo '<div class="v-product-item">';
                if (has_post_thumbnail()) {
                    echo '<div class="v-product-image"><a href="' . $vlink . '" target="_blank">' . get_the_post_thumbnail(get_the_ID(), 'thumbnail') . '</a></div>';
                }
                echo '<div class="v-product-content">';
                if ($display_title == 'true') {
                    echo '<h2 class="v-title">' . get_the_title() . '</h2>';
                }
                $limitedText = substr($description, 0, 50);

                if (strlen($description) > 50) {
                    $limitedText .= '...';
                }
                echo '<p class="v-description">' . esc_html($limitedText) . '</p>';
                echo '<p class="v-price">' . esc_html($price) . '</p>';
                echo '<a class="v-btn" href="' . $vlink . '" target="_blank">View</a>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            echo '<div class="v-pagination">' . paginate_links(array('total' => $products->max_num_pages)) . '</div>';
?>
            <style>
                .v-product-list {
                    display: flex;
                    justify-content: start;
                    column-gap: 2%;
                    flex-wrap: wrap;
                }

                .v-product-item {
                    width: 100%;
                    max-width: 23%;
                    text-wrap: wrap;
                    margin-bottom: 40px;
                    background: #efefef;
                }

                .v-product-item .v-product-content {
                    padding: 15px 10px;
                }

                .v-product-item .v-title {
                    font-size: 18px;
                    font-weight: 700;
                }
                .v-product-item .v-description {
                    font-size: 16px;
                }

                .v-product-item .v-price {
                    font-size: 18px;
                    font-weight: 500;
                }

                .v-product-item a.v-btn {
                    background: #000000;
                    color: #ffffff;
                    border-radius: 30px;
                    text-decoration: none;
                    display: inline-block;
                    height: 30px;
                    width: 100px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .v-product-item .v-product-image {
                    position: relative;
                }

                .v-product-item img {
                    width: 100%;
                    height: 300px;
                    object-fit: cover;
                }

                /* pagination */
                .v-pagination {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    column-gap: 15px;
                }

                .v-pagination a {
                    text-decoration: none;
                }

                .v-pagination span.page-numbers.current {
                    background: #000000;
                    border-radius: 50%;
                    height: 30px;
                    width: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #ffffff;
                }

                /* media query */
                @media screen and (max-width: 1024px) {
                    .v-product-item {
                        max-width: 48%;
                    }
                }

                @media screen and (max-width: 768px) {
                    .v-product-item {
                        max-width: 100%;
                    }
                }
            </style>
<?php
            wp_reset_postdata();
        } else {
            echo 'No products found.';
        }
    }
}

$custom_product_plugin = new Custom_Product_Plugin();

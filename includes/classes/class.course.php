<?php
if ( !defined('ABSPATH') )
    exit; // Exit if accessed directly

if ( !class_exists('Course') ) {

    class Course {

        var $id = '';
        var $output = 'OBJECT';
        var $course = array();
        var $details;
        var $data = array();

        function __construct( $id = '', $output = 'OBJECT' ) {
            $this->id = $id;
            $this->output = $output;
            $this->details = get_post($this->id, $this->output);
        }

        function Course( $id = '', $output = 'OBJECT' ) {
            $this->__construct($id, $output);
        }

        function get_course() {

            $course = get_post($this->id, $this->output);

            if ( !empty($course) ) {

                if ( !isset($course->post_title) || $course->post_title == '' ) {
                    $course->post_title = __('Untitled', 'cp');
                }
                if ( $course->post_status == 'private' || $course->post_status == 'draft' ) {
                    $course->post_status = __('unpublished', 'cp');
                }

                $course->allow_course_discussion = get_post_meta($this->id, 'allow_course_discussion', true);
                $course->class_size = get_post_meta($this->id, 'class_size', true);

                return $course;
            } else {
                return new stdClass();
            }
        }

        function course_structure_front( $try_title = '', $show_try = true, $hide_title = false, $echo = true ) {
            $show_unit = $this->details->show_unit_boxes;
            $preview_unit = $this->details->preview_unit_boxes;

            $show_page = $this->details->show_page_boxes;
            $preview_page = $this->details->preview_page_boxes;

            $units = $this->get_units();
			
			$content = '';
			
			if ( ! $echo ) {
				ob_start();
			}
			
			
            echo $hide_title ? '' : '<label>' . $this->details->post_title . '</label>';
			
            ?>
			
            <ul class="tree">
                <li>
                    <ul>
                        <?php
                        $module = new Unit_Module();

                        foreach ( $units as $unit ) {
                            $unit_class = new Unit($unit->ID);
                            $unit_pages = $unit_class->get_number_of_unit_pages();

                            $modules = $module->get_modules($unit->ID);

                            if ( isset($show_unit[$unit->ID]) && $show_unit[$unit->ID] == 'on' && $unit->post_status == 'publish' ) {
                                ?>

                                <li>

                                    <label for="unit_<?php echo $unit->ID; ?>" class="course_structure_unit_label">
                                        <div class="tree-unit-left"><?php echo $unit->post_title; ?></div>
                                        <div class="tree-unit-right">

                                            <?php if ( $this->details->course_structure_time_display == 'on' ) { ?>
                                                <span><?php echo $unit_class->get_unit_time_estimation($unit->ID); ?></span>
                                            <?php } ?>

                                            <?php
                                            if ( isset($preview_unit[$unit->ID]) && $preview_unit[$unit->ID] == 'on' ) {
                                                ?>
                                                <a href="<?php echo $unit_class->get_permalink(); ?>?try" class="preview_option"><?php
                                                    if ( $try_title == '' ) {
                                                        _e('Try Now', 'tc');
                                                    } else {
                                                        echo $try_title;
                                                    };
                                                    ?></a>
                    <?php } ?>
                                        </div>
                                    </label>

                                    <ul>
                                        <?php
                                        for ( $i = 1; $i <= $unit_pages; $i++ ) {
                                            if ( isset($show_page[$unit->ID . '_' . $i]) && $show_page[$unit->ID . '_' . $i] == 'on' ) {
                                                ?>

                                                <li class="course_structure_page_li">
                                                    <?php
                                                    $pages_num = 1;
                                                    $page_title = $unit_class->get_unit_page_name($i);
                                                    ?>

                                                    <label for="page_<?php echo $unit->ID . '_' . $i; ?>">
                                                        <div class="tree-page-left">
                            <?php echo (isset($page_title) && $page_title !== '' ? $page_title : __('Untitled Page', 'cp')); ?>
                                                        </div>
                                                        <div class="tree-page-right">

                                                            <?php if ( $this->details->course_structure_time_display == 'on' ) { ?>
                                                                <span><?php echo $unit_class->get_unit_page_time_estimation($unit->ID, $i); ?></span>
                                                            <?php } ?>

                                                            <?php
                                                            if ( isset($preview_page[$unit->ID . '_' . $i]) && $preview_page[$unit->ID . '_' . $i] == 'on' ) {
                                                                ?>
                                                                <a href="<?php echo $unit_class->get_permalink(); ?>page/<?php echo $i; ?>?try" class="preview_option"><?php
                                                                    if ( $try_title == '' ) {
                                                                        _e('Try Now', 'tc');
                                                                    } else {
                                                                        echo $try_title;
                                                                    };
                                                                    ?></a>
                                                    <?php } ?>

                                                        </div>
                                                    </label>

                                                <?php
                                                ?>
                                                </li>
                            <?php
                        }
                    }//page visible 
                    ?>

                                    </ul>
                                </li>
                    <?php
                }//unit visible
				
				if ( ! $echo ) {
					trim(ob_get_clean());
				}
				
            }
            ?>

                    </ul>
                    <?php
                }

                function is_open_ended() {
                    
                }

                static function get_course_featured_url( $course_id = false ) {
                    if ( !$course_id ) {
                        return false;
                    }

                    $course = new Course($course_id);

                    if ( $course->details->featured_url !== '' ) {
                        return $course->details->featured_url;
                    } else {
                        return false;
                    }

                    unset($course);
                }

                static function get_course_thumbnail( $course_id = false ) {
                    if ( !$course_id ) {
                        return false;
                    }

                    $thumb = get_post_thumbnail_id($course_id);
                    if ( $thumb !== '' ) {
                        return $thumb;
                    } else {
                        self::get_course_featured_url($course_id);
                    }
                }

                static function get_course_by_marketpress_product_id( $marketpress_product_id ) {

                    $args = array(
                        'post_type' => 'course',
                        'post_status' => 'any',
                        'meta_key' => 'marketpress_product',
                        'meta_value' => $marketpress_product_id,
                        'posts_per_page' => 1
                    );

                    $post = get_posts($args);

                    if ( $post ) {
                        return $post[0];
                    } else {
                        return false;
                    }
                }

                static function get_course_id_by_name( $slug ) {

                    $args = array(
                        'name' => $slug,
                        'post_type' => 'course',
                        'post_status' => 'any',
                        'posts_per_page' => 1
                    );

                    $post = get_posts($args);

                    if ( $post ) {
                        return $post[0]->ID;
                    } else {
                        return false;
                    }
                }

                function mp_product_id() {
                    $mp_product_id = get_post_meta($this->id, 'mp_product_id', true);
                    return $mp_product_id;
                }

                function update_mp_product( $course_id = false ) {
                    $course_id = $course_id ? $course_id : $this->id;
                    $automatic_sku_number = 'CP-' . $course_id;

                    $mp_product_id = $this->mp_product_id();

                    $post = array(
                        'post_status' => 'publish',
                        'post_title' => $this->details->post_title,
                        'post_type' => 'product',
                        'post_parent' => $course_id
                    );

                    if ( $mp_product_id ) {
                        $post['ID'] = $mp_product_id; //If ID is set, wp_insert_post will do the UPDATE instead of insert
                        $post_id = wp_update_post($post);
                    } else {
                        $post_id = wp_insert_post($post);
                    }

                    $automatic_sku = $_POST['meta_auto_sku'];

                    if ( $automatic_sku == 'on' ) {
                        $sku = $automatic_sku_number;
                    } else {
                        $sku = $_POST['mp_sku'];
                    }

                    update_post_meta($this->id, 'mp_product_id', $post_id);
                    update_post_meta($this->id, 'marketpress_product', $post_id);

                    update_post_meta($post_id, 'mp_sku', $sku);
                    update_post_meta($post_id, 'mp_price', $_POST['mp_price']);
                    update_post_meta($post_id, 'mp_sale_price', $_POST['mp_sale_price']);
                    update_post_meta($post_id, 'mp_is_sale', $_POST['mp_is_sale']);
                }

                function update_course() {
                    global $user_id, $wpdb;

                    $course = get_post($this->id, $this->output);

                    $post_status = empty($this->data['status']) ? 'publish' : $this->data['status'];

                    if ( $_POST['course_name'] != '' && $_POST['course_name'] != __('Untitled', 'cp') ) {
                        if ( !empty($course->post_status) && $course->post_status != 'publish' ) {
                            $post_status = 'private';
                        }
                    } else {
                        $post_status = 'draft';
                    }

                    $post = array(
                        'post_author' => $user_id,
                        // 'post_excerpt' => $_POST['course_excerpt'],
                        // 'post_content' => $_POST['course_description'],
                        'post_status' => $post_status,
                        // 'post_title' => $_POST['course_name'],
                        'post_type' => 'course',
                    );

                    // If the course already exsists, avoid accidentally wiping out important fields.
                    if ( $course ) {
                        $post['post_excerpt'] = empty($_POST['course_excerpt']) ? $course->post_excerpt : $_POST['course_excerpt'];
                        $post['post_content'] = empty($_POST['course_description']) ? $course->post_content : $_POST['course_description'];
                        $post['post_title'] = empty($_POST['course_name']) ? $course->post_title : $_POST['course_name'];
                    } else {
                        $post['post_excerpt'] = $_POST['course_excerpt'];
                        if ( isset($_POST['course_description']) ) {
                            $post['post_content'] = $_POST['course_description'];
                        }
                        $post['post_title'] = $_POST['course_name'];
                    }

                    if ( isset($_POST['course_id']) ) {
                        $post['ID'] = $_POST['course_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
                    }

                    $post_id = wp_insert_post($post);

                    //Update post meta
                    if ( $post_id != 0 ) {
                        foreach ( $_POST as $key => $value ) {
                            if ( preg_match("/meta_/i", $key) ) {//every field name with prefix "meta_" will be saved as post meta automatically
                                update_post_meta($post_id, str_replace('meta_', '', $key), $value);
                            }
                            if ( preg_match("/mp_/i", $key) ) {
                                update_post_meta($post_id, $key, $value);
                            }

                            if ( isset($_POST['meta_course_category']) ) {
                                if ( is_array($_POST['meta_course_category']) ) {
                                    $sanitized_array = array();
                                    foreach ( $_POST['meta_course_category'] as $cat_id ) {
                                        $sanitized_array = ( int ) $cat_id;
                                    }
                                    wp_set_post_categories($post_id, $sanitized_array);
                                } else {
                                    $cat = array( ( int ) $_POST['meta_course_category'] );
                                    if ( $cat ) {
                                        wp_set_post_categories($post_id, $cat);
                                    }
                                }
                            } // meta_course_category
                            //Add featured image
                            if ( isset($_POST['_thumbnail_id']) && is_numeric($_POST['_thumbnail_id']) && isset($_POST['meta_featured_url']) && $_POST['meta_featured_url'] !== '' ) {

                                $course_image_width = get_option('course_image_width', 235);
                                $course_image_height = get_option('course_image_height', 225);

                                $upload_dir_info = wp_upload_dir();
                                $fl = trailingslashit($upload_dir_info['path']) . basename($_POST['meta_featured_url']);

                                $image = wp_get_image_editor($fl); // Return an implementation that extends <tt>WP_Image_Editor</tt>

                                if ( !is_wp_error($image) ) {

                                    $image_size = $image->get_size();

                                    if ( ( $image_size['width'] < $course_image_width || $image_size['height'] < $course_image_height ) || ( $image_size['width'] == $course_image_width && $image_size['height'] == $course_image_height ) ) {
                                        update_post_meta($post_id, '_thumbnail_id', $_POST['meta_featured_url']);
                                    } else {
                                        $ext = pathinfo($fl, PATHINFO_EXTENSION);
                                        $new_file_name = str_replace('.' . $ext, '-' . $course_image_width . 'x' . $course_image_height . '.' . $ext, basename($_POST['meta_featured_url']));
                                        $new_file_path = str_replace(basename($_POST['meta_featured_url']), $new_file_name, $_POST['meta_featured_url']);
                                        update_post_meta($post_id, '_thumbnail_id', $new_file_path);
                                    }
                                } else {
                                    update_post_meta($post_id, '_thumbnail_id', $_POST['meta_featured_url']);
                                }
                            } else {
                                if ( isset($_POST['meta_featured_url']) && $_POST['meta_featured_url'] == '' ) {
                                    update_post_meta($post_id, '_thumbnail_id', '');
                                }
                            }

                            //Add instructors
                            if ( isset($_POST['instructor']) ) {

                                //Get last instructor ID array in order to compare with posted one
                                $old_post_meta = get_post_meta($post_id, 'instructors', false);

                                if ( serialize(array( $_POST['instructor'] )) !== serialize($old_post_meta) || 0 == $_POST['instructor'] ) {//If instructors IDs don't match
                                    delete_post_meta($post_id, 'instructors');
                                    delete_user_meta_by_key('course_' . $post_id);
                                }

                                if ( 0 != $_POST['instructor'] ) {

                                    update_post_meta($post_id, 'instructors', $_POST['instructor']); //Save instructors for the Course


                                    foreach ( $_POST['instructor'] as $instructor_id ) {
                                        update_user_meta($instructor_id, 'course_' . $post_id, $post_id); //Link courses and instructors ( in order to avoid custom tables ) for easy MySql queries ( get instructor stats, his courses, etc. )
                                    }
                                } // only add meta if array is sent	
                            }
                        }

                        if ( isset($_POST['meta_paid_course']) ) {
                            $this->update_mp_product($post_id);
                        }

                        return $post_id;
                    }
                }

                function delete_course( $force_delete = true ) {

                    $wpdb;

                    wp_delete_post($this->id, $force_delete); //Whether to bypass trash and force deletion

                    /* Delete all usermeta associated to the course */
                    delete_user_meta_by_key('course_' . $this->id);
                    delete_user_meta_by_key('enrolled_course_date_' . $this->id);
                    delete_user_meta_by_key('enrolled_course_class_' . $this->id);
                    delete_user_meta_by_key('enrolled_course_group_' . $this->id);

                    //delete all course units

                    $args = array(
                        'posts_per_page' => -1,
                        'meta_key' => 'course_id',
                        'meta_value' => $this->id,
                        'post_status' => 'any',
                        'post_type' => 'unit' );

                    $course_units = get_posts($args);

                    foreach ( $course_units as $course_unit ) {
                        $unit = new Unit($course_unit->ID);
                        $unit->delete_unit(true);
                    }
                }

                function can_show_permalink() {
                    $course = $this->get_course();
                    if ( $course->post_status !== 'draft' ) {
                        return true;
                    } else {
                        return false;
                    }
                }

                static function get_course_instructors( $course_id = false ) {

                    if ( !$course_id ) {
                        return false;
                    }

                    // Get instructor ID's to return
                    $instructors = get_post_meta($course_id, 'instructors', true);

                    $args = array(
                        'blog_id' => $GLOBALS['blog_id'],
                        'meta_key' => 'course_' . $course_id,
                        'meta_value' => $course_id,
                        'meta_compare' => '',
                        'meta_query' => array(),
                        // Only include instructors, not students				
                        'include' => $instructors,
                        'orderby' => 'display_name',
                        'order' => 'ASC',
                        'offset' => '',
                        'search' => '',
                        'number' => '',
                        'count_total' => false,
                    );

                    return get_users($args);
                }

                static function get_categories( $course_id = false ) {

                    if ( !$course_id ) {
                        return false;
                    }

                    $course_category = get_post_meta($course_id, 'course_category', true);

                    if ( !is_array($course_category) ) {
                        $course_category = array( $course_category );
                    }

                    $args = array(
                        'type' => 'link_category',
                        'hide_empty' => 0,
                        'include' => $course_category,
                        'taxonomy' => array( 'course_category' ),
                    );

                    return get_categories($args);
                }

                function change_status( $post_status ) {
                    $post = array(
                        'ID' => $this->id,
                        'post_status' => $post_status,
                    );
                    // Update the post status
                    wp_update_post($post);
                }

                function get_units( $course_id = '', $status = 'any', $count = false ) {

                    if ( $course_id == '' ) {
                        $course_id = $this->id;
                    }

                    $args = array(
                        'category' => '',
                        'order' => 'ASC',
                        'post_type' => 'unit',
                        'post_mime_type' => '',
                        'post_parent' => '',
                        'post_status' => $status,
                        'meta_key' => 'unit_order',
                        'orderby' => 'meta_value_num',
                        'posts_per_page' => '-1',
                        'meta_query' => array(
                            array(
                                'key' => 'course_id',
                                'value' => $course_id
                            ),
                        )
                    );

                    $units = get_posts($args);
                    if ( $count ) {
                        return count($units);
                    } else {
                        return $units;
                    }
                }

                function get_permalink( $course_id = '' ) {
                    if ( $course_id == '' ) {
                        $course_id = $this->id;
                    }
                    return get_permalink($course_id);
                }

                function get_permalink_to_do( $course_id = '' ) {
                    global $course_slug;
                    global $units_slug;

                    if ( $course_id == '' ) {
                        $course_id = get_post_meta($post_id, 'course_id', true);
                    }

                    $course = new Course($course_id);
                    $course = $course->get_course();

                    $unit_permalink = site_url() . '/' . $course_slug . '/' . $course->post_name . '/' . $units_slug . '/' . $this->details->post_name . '/';
                    return $unit_permalink;
                }

                function get_number_of_students( $course_id = '' ) {
                    if ( $course_id == '' ) {
                        $course_id = $this->id;
                    }

                    $args = array(
                        /* 'role' => 'student', */
                        'meta_key' => 'enrolled_course_class_' . $course_id,
                    );

                    $wp_user_search = new WP_User_Query($args);
                    return count($wp_user_search->get_results());
                }

                function is_populated( $course_id = '' ) {
                    if ( $course_id == '' ) {
                        $course_id = $this->id;
                    }

                    $class_size = $this->get_course()->class_size;

                    $number_of_enrolled_students = $this->get_number_of_students($course_id);

                    if ( $class_size == 0 ) {
                        return false;
                    } else {
                        if ( $class_size > $number_of_enrolled_students ) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }

                function show_purchase_form( $product_id ) {
                    echo do_shortcode('[mp_product product_id="' . $product_id . '" title="true" content="full"]');
                    //echo do_shortcode( '[mp_product_meta product_id="' . $product_id . '"]' );
                }

                function is_user_purchased_course( $product_id, $user_id ) {
                    global $mp;

                    $args = array(
                        'author' => $user_id,
                        'post_type' => 'mp_order',
                        'post_status' => 'order_paid',
                        'posts_per_page' => '-1'
                    );

                    $purchases = get_posts($args);

                    foreach ( $purchases as $purchase ) {

                        $purchase_records = $mp->get_order($purchase->ID);

                        if ( array_key_exists($product_id, $purchase_records->mp_cart_info) ) {
                            return true;
                        }

                        return false;
                    }
                }

            }

        }
        ?>
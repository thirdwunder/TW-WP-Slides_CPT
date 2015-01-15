<?php
class twSlidesWidget extends WP_Widget{
  private $enable_cat;
  private $enable_tag;
  function twSlidesWidget(){
    parent::WP_Widget(false, 'TW Slides', array('description'=>''));
    $this->enable_cat = get_option('wpt_tw_slide_category')=='on' ? true : false;
    $this->enable_tag = get_option('wpt_tw_slide_tag')=='on' ? true : false;
  }

  function form($instance){
    $instance = wp_parse_args( (array) $instance, array( 'number' => '', 'order'=>'', 'tag' => '' ) );

		if($instance['number']){
  		$number = esc_attr($instance['number']);
		}else{$number = '';}

		if($instance['order']){
  		$order = esc_attr($instance['order']);
		}else{$order = '';}



    if($this->enable_cat){
      if($instance['category']){
    		$category = esc_attr($instance['category']);
  		}else{$category = '';}

      $slide_cats = get_terms('tw_slide_category',
                               array(
                               	'orderby'    => 'count',
                               	'hide_empty' => 0,
                               )
                              );
    }

    if($this->enable_tag){
      if($instance['tag']){
    		$tag = esc_attr($instance['tag']);
  		}else{$tag = '';}
    }
?>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Slides','tw'); ?> </label>
		    <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
    </p>

    <p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order by','tw'); ?> </label>
      <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
           <option value="date"       <?php selected( $order, 'date' ); ?>><?php echo __('Date','tw'); ?></option>
           <option value="name"       <?php selected( $order, 'name' ); ?>><?php echo __('Name','tw'); ?></option>
           <option value="menu_order" <?php selected( $order, 'menu_order' ); ?>><?php echo __('Assigned Order','tw'); ?></option>
      </select>
    </p>

    <?php if($this->enable_cat): ?>
    <p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category','tw'); ?> </label>
      <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
        <option value=""  <?php selected( $category, '' ); ?>><?php echo __('','tw'); ?></option>

        <?php foreach($slide_cats as $scat): ?>
          <option value="<?php echo $scat->slug; ?>"  <?php selected( $category, $scat->slug ); ?>><?php echo $scat->name; ?></option>
        <?php endforeach; ?>

      </select>
    </p>
    <?php endif;?>

    <?php if($this->enable_tag): ?>
		<p><label for="<?php echo $this->get_field_id('tag'); ?>"><?php _e('Tags to feature','tw'); ?> </label>
		    <input class="widefat" id="<?php echo $this->get_field_id('tag'); ?>" name="<?php echo $this->get_field_name('tag'); ?>" type="text" value="<?php echo $tag; ?>" />
    </p>
    <?php endif; ?>


<?php
  }


  function update($new_instance, $old_instance){
    $instance = $old_instance;

    $instance['number']    = $new_instance['number'];
    $instance['order']     = $new_instance['order'];
    if($this->enable_cat){
      $instance['category'] = $new_instance['category'];
    }
    if($this->enable_tag){
      $instance['tag']      = $new_instance['tag'];
    }

    return $instance;
  }

  function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    // outputs the content of the widget
    $args['number']  = empty($instance['number']) ? '' : $instance['number'];
    $args['order']   = empty($instance['order'])  ? '' : $instance['order'];
    if($this->enable_cat){
      $args['category'] = empty($instance['category']) ? '' : $instance['category'];
    }

    if($this->enable_tag){
      $args['tag']     = empty($instance['tag'])    ? '' : $instance['tag'];
    }

    $args['enable_cat'] = $this->enable_cat;
    $args['enable_tag'] = $this->enable_tag;
    tw_slides_widget($args);
  }

}

function tw_slides_widget($args){
  $expiry_enabled = (get_option('wpt_tw_slide_enable_expiration') && get_option('wpt_tw_slide_enable_expiration')=='on') ? true : false;
  $video_enabled = (get_option('wpt_tw_slide_enable_video') && get_option('wpt_tw_slide_enable_video')=='on') ? true : false;

  $num = isset($args['number']) ? intval(trim($args['number'])) : 5 ;
  $orderby = isset($args['order']) ? trim($args['order']) : 'date';
  $order = 'desc';
  switch ($orderby) {
    case 'date':
      $order = 'desc';
      break;
    case 'name':
      $order = 'asc';
      break;
    case 'menu_order':
      $order = 'asc';
      break;
    default:
      $order = 'desc';
  }

  $query_args= array(
  	'post_type' => 'tw_slide',
  	'posts_per_page' => $num,
  	'order' => $order,
  	'orderby' => $orderby,
  );
  $relationship = false;
  if($args['enable_cat'] && $args['enable_tag']){
    $relationship = true;
  }


  if($args['enable_cat']){
    $category = trim($args['category']);
    if($category!==''){
      $tax_query[] = array(
  			'taxonomy' => 'tw_slide_category',
  			'field'    => 'slug',
  			'terms'    => $category,
  		);
    }else{
      $relationship = false;
    }

  }

  if($args['enable_tag']){
    $tag = trim($args['tag']);
    if(!empty($tag) && $tag!==""){
      $tag =  explode(',', $tag);
      $tax_query[] = array(
  			'taxonomy' => 'tw_slide_tag',
  			'field'    => 'slug',
  			'terms'    => $tag,
  			'operator' => 'IN',
  		);

    }else{
      $relationship = false;
    }
  }

  if($relationship){
    $tax_query['relation'] = 'AND';
  }


  if($expiry_enabled){
    $meta_query = array();
    //$meta_query['relation'] = 'OR';
    $meta_query[] = array(
			'key'       => 'tw_slide_expiry_date',
			'value'     => current_time( 'Y-m-d' ),
			'compare'   => '>=',
			'type'      => 'DATE',
		);

    $query_args['meta_query'] = $meta_query;
  }

  $query_args['tax_query'] = $tax_query;
  $slides = new WP_Query( $query_args );

  if(function_exists('tw_get_slider_style')){
    $slider_transition = tw_get_slider_style()=='fade' ? 'carousel-fade' : '';
  }

  if ( $slides->have_posts() ) :
    echo $args['before_widget'];
    $count = 0;
    $slide_count = 0;
    ?>
    <div id="<?php echo $args['widget_id'];?>-carousel" class="homepage-carousel carousel <?php echo $slider_transition;?> slide" data-ride="carousel">
      <!-- Wrapper for slides -->
      <div class="carousel-inner" role="listbox">
      <?php

        while($slides->have_posts()): $slides->the_post();
          $is_expired = false;
          $has_video = false;
          if($video_enabled){
            $has_video = (get_post_meta(get_the_id(), 'tw_slide_video_enable', true) && trim(get_post_meta(get_the_id(), 'tw_slide_video_enable', true))=='on' ) ? true : false;
            $video_url = trim(get_post_meta(get_the_id(), 'tw_slide_video_url', true))!=='' ? trim(get_post_meta(get_the_id(), 'tw_slide_video_url', true)) : false;
            $video_poster = is_array(get_post_meta(get_the_id(), 'tw_slide_video_poster', true)) ? get_post_meta(get_the_id(), 'tw_slide_video_poster', true) : false;

            if(is_array($video_poster) && isset($video_poster['id'])){
              $video_poster_id = $video_poster['id'];
            }else{
              $video_poster_id = wp_get_attachment_image(get_the_id(), 'medium');
              $video_poster_id = $video_poster_id[0];
            }

            if(function_exists('tw_get_image_src')){
              $image_sizes = array('4x3-small','4x3-small','4x3-small');
              $video_poster_src = tw_get_image_src($video_poster_id, $image_sizes);
              $video_bg = "background: url('$video_poster_src');";
            }else{
              $video_poster_src = wp_get_attachment_image_src($video_poster_id, 'medium');
              $video_bg = "background: url('$video_poster_src[0]');";
            }

          }

          if($expiry_enabled){
            $today = strtotime(current_time( 'Y-m-d H:m'));
            $e_date = get_post_meta( get_the_id(), 'tw_slide_expiry_date', true);
            $e_time = get_post_meta( get_the_id(), 'tw_slide_expiry_time', true);
            $expiry_dt = $e_date.' '.$e_time;
            $e_ts = strtotime($expiry_dt);
            $is_expired = ($e_ts<$today) ? true : false;
          }

          $button  = trim(get_post_meta( get_the_id(), 'tw_slide_cta_title', true));
          $url     = trim(get_post_meta( get_the_id(), 'tw_slide_cta_url', true));

          if(has_post_thumbnail() && !$is_expired): $slide_count++; ?>
          <div class="item <?php if($count==0){ echo('active'); }?>" >
            <?php
              if(function_exists('tw_the_post_thumbnail')){
                $image_sizes = array('4x3-small','16x6-medium','16x6-large');
                echo tw_the_post_thumbnail($image_sizes, array('itemprop'=>'image'));
              }else{
                echo get_the_post_thumbnail(get_the_id(), 'full');
              }
            ?>

            <?php if($video_enabled && $has_video && $video_url!=''): ?>
              <div class="carousel-caption with-video">
                <h3><?php the_title(); ?></h3>
                <div id="section-video-<?php the_id(); ?>" class="section-video" style="<?php echo $video_bg; ?>">
                   <div id="video-<?php the_id(); ?>" class="video embed-responsive embed-responsive-16by9" >
                     <!-- <?php echo html_entity_decode(tw_videoURL_to_embedCode($video_url, true)); ?> -->
                   </div>
                 </div>

                <?php if($button!="" && $url!=""): ?>
                  <div>
                  <a class="btn btn-primary pull-right" href="<?php echo $url;?>" title="<?php echo $button; ?>"><?php echo $button; ?></a>
                    <div class="clearfix"></div>
                  </div>
                <?php endif; ?>
              </div>
            <?php elseif(get_the_content()!==''): ?>
              <div class="carousel-caption">
                <h3><?php the_title(); ?></h3>
                <?php the_content(); ?>
                <?php if($button!="" && $url!=""): ?>
                  <div>
                  <a class="btn btn-primary pull-right" href="<?php echo $url;?>" title="<?php echo $button; ?>"><?php echo $button; ?></a>
                    <div class="clearfix"></div>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

          </div><!-- item -->
        <?php
          $count++;
          endif;
        endwhile; $count = 0;?>
      </div><!-- carousel-inner -->


      <!-- Indicators -->
      <ol class="carousel-indicators">
        <?php for($i=0;$i<$slide_count;$i++): ?>
        <li data-target="<?php echo $args['widget_id'];?>-carousel" data-slide-to="<?php echo $i ;?>" class="<?php if($i==0){ echo('active'); }?>"></li>
        <?php endfor; ?>
      </ol>

      <?php //if(count($slides)>1): ?>
      <!-- Controls -->
      <a class="left carousel-control" href="#<?php echo $args['widget_id'];?>-carousel" data-slide="prev" role="button">
        <i class="fa fa-angle-left"></i>
      </a>
      <a class="right carousel-control" href="#<?php echo $args['widget_id'];?>-carousel" data-slide="next" role="button">
        <i class="fa fa-angle-right"></i>
      </a>
      <?php //endif; ?>

    </div><!-- homepage-carousel -->


    <?php
    echo $args['after_widget'];

  endif;
}

add_action( 'widgets_init', create_function('', 'return register_widget("twSlidesWidget");') );
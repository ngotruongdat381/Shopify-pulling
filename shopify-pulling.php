<?php
/**
 * Plugin Name: SHOPIFY PULLING
 * Plugin URI: http://
 * Description: Pull data from Shopify
 * Version: 1.0 
 * Author: MOB
 * Author URI: 
 * License: GPLv2
 */

define('SHOPIFY_LINK',get_option('shopify_link'));
define('DEFAULT_QUERYSTRING', get_option('default_querystring'));
//Set action for quickview link
function quickview_action_shopify() {
  ?>
    <script type="text/javascript">
      var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>

    <div id="quickViewOverlay"></div>
    <div id="quickViewResponseData">
      <div class="close-quickview">
        <img src="<?php echo plugins_url( 'img/icon-close.png', __FILE__ );?>"/>
      </div>
     <div id="QuickViewContent">

		<div class="qprod">
			<div id="QuickViewImage" class="ProductThumbImage">
				<a href="">
					<img src="">
				</a>
			</div>
		</div>

		<div id="QuickViewProductDetails">
				<div id="ProductDetails" class="ProductDetailsGrid productDetails ProductAddToCart">			
					
					<!-- Begin Product Details Table -->			
					<h1 class="product-name" ></h1>

					<div class="DetailRow PriceRow p-price" style="">
						<div class="Value">
							<span class="ProductPrice VariationProductPrice"></span>
						</div>
					</div>

					<div class="DetailRow" style="display: ">
						<div class="productAttributeLabel QuantityInput" style="display: ">
							Quantity:
						</div>
						<div class="productAttributeValue">
							<span style="display: ;">
								<select id="qty" name="qty[]" class="Field45 quantityInput" style="">
									<option selected="selected" value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
									<option value="13">13</option>
									<option value="14">14</option>
									<option value="15">15</option>
									<option value="16">16</option>
									<option value="17">17</option>
									<option value="18">18</option>
									<option value="19">19</option>
									<option value="20">20</option>
									<option value="21">21</option>
									<option value="22">22</option>
									<option value="23">23</option>
									<option value="24">24</option>
									<option value="25">25</option>
									<option value="26">26</option>
									<option value="27">27</option>
									<option value="28">28</option>
									<option value="29">29</option>
									<option value="30">30</option>
								</select>
							</span>
						</div>
					</div>
					
					<div class="DetailRow addToCart">
						<a id="cart-link" style="display: initial;" href=""><input class="btn" value="Add to cart." type="submit"></a>
            <span style="display: initial;"> or </span>
            <a id="more-info" style="display: initial;" href="">more info</a>
					</div>

					<div class="DetailRow">
						<p class="description" style="display:none;"></p>    
					</div>
				</div>
		</div>
	</div>
</div>

<script type="text/javascript">
  jQuery(document).ready(function($){
    jQuery('a[href*="getshopifyproduct"]').on('click', function(e){ // filter quick view links
      e.preventDefault();
      jQuery('#quickViewResponseData').css('display', 'flex');
      jQuery('#quickViewOverlay').css('display', 'block');

      jQuery('.close-quickview').on('click', function(){
        jQuery('#quickViewResponseData').hide();
        jQuery('#quickViewOverlay').hide();
      });
      jQuery('#quickViewResponseData #qty').on('change', function(){
        var _href = jQuery('#quickViewResponseData .addToCart #cart-link').attr('href'); 
        _href = _href.replace(/quantity=[^&]+/, 'quantity=' + jQuery('#quickViewResponseData #qty').val());
        jQuery('#quickViewResponseData .addToCart #cart-link').attr("href",_href);
      });

      var data = {
        'action': 'my_action_shopify',
        'productUrl': jQuery(this).attr("href")
      };

      jQuery.post(ajaxurl, data, function(response) {
        $('#quickViewResponseData .product-name').html(JSON.parse(response).title);
        $('#quickViewResponseData .description').html(JSON.parse(response).description);
        $('#quickViewResponseData .price').html('$'+JSON.parse(response).price);
        $('#quickViewResponseData .image img').attr("src",JSON.parse(response).image_url);
        $('#quickViewResponseData .add-to-cart #cart-link').attr("href",'http://store-minetanbodyskin-com.myshopify.com/cart/add?id='+JSON.parse(response).variant_id+'&quantity='+$('#quickViewResponseData #qty').val());
        if (JSON.parse(response).collection != '' && JSON.parse(response).collection != null) {
          $('#quickViewResponseData .add-to-cart #more-info').attr("href",'http://store-minetanbodyskin-com.myshopify.com/collections/'+JSON.parse(response).collection+'/products/'+JSON.parse(response).handle+'?view='+JSON.parse(response).query_string);
        } else {
          $('#quickViewResponseData .add-to-cart #more-info').attr("href",'http://store-minetanbodyskin-com.myshopify.com/products/'+JSON.parse(response).handle+'?view='+JSON.parse(response).query_string);
        }
      });
    });
  });
</script>
  <?php
}

add_action( 'wp_footer', 'quickview_action_shopify' );

function get_product_responsse_shopify() {
  // Create shopify json link
  $parts = parse_url($_POST['productUrl']);
  parse_str($parts['query'], $query);
  $product_id = $query['id'];
  $collection = $query['collection'];
  $query_string = $query['query_string'];
  if ($query_string == '' || $query_string == null) {
    $query_string  = DEFAULT_QUERYSTRING;
  }
  $shopify_url = SHOPIFY_LINK.$product_id.'.json';
  $json = file_get_contents($shopify_url);
  $obj = json_decode($json);

  // Return product object
  $product = new stdClass();
  $product->title = $obj->product->title;
  $product->price = $obj->product->variants[0]->price;
  $product->description = preg_replace('#<br\s*/?>(?:\s*<br\s*/?>)+#i', '<br />', $obj->product->body_html);
  $product->image_url = $obj->product->image->src;
  $product->variant_id = $obj->product->variants[0]->id;
  $product->handle = $obj->product->handle;
  $product->collection = $collection;
  $product->query_string = $query_string;
  echo json_encode($product);

  wp_die();
  return true;
}
add_action( 'wp_ajax_my_action_shopify', 'get_product_responsse_shopify' );
add_action( 'wp_ajax_nopriv_my_action_shopify', 'get_product_responsse_shopify' );

function enqueue_scripts_and_styles()
{
  wp_register_style( 'plugin-styles', plugins_url( '/css/plugin-styles.css', __FILE__ ));
  wp_enqueue_style( 'plugin-styles' ); 
}
add_action( 'wp_enqueue_scripts', 'enqueue_scripts_and_styles' );

add_action('admin_menu', 'shopify_pulling_plugin_menu');

function shopify_pulling_plugin_menu() {
  add_menu_page('Shopify Plugin Settings', 'Shopify Plugin Settings', 'administrator', 'shopify-pulling-plugin-settings', 'shopify_pulling_settings_page', 'dashicons-admin-generic');
}

function shopify_pulling_settings_page() {
  ?>
  <div class="wrap">
  <h2>Set options</h2>

  <form method="post" action="options.php">
      <?php settings_fields( 'shopify-pulling-plugin-settings-group' ); ?>
      <?php do_settings_sections( 'shopify-pulling-plugin-settings-group' ); ?>
      <p>Shopify API link Ex: https://36447a1c8002d4de04f830dbc07906f2:a0fbad43d7ba56aee3041497088896c4@store-minetanbodyskin-com.myshopify.com/admin/products/</p>
      <p>Quickview link Ex: https://store-minetanbodyskin-com.myshopify.com/getshopifyproduct?id=6490947845&collection=mine-self-tan&query_string=mine-retail</p>
      <table class="form-table">
          <tr valign="top">
          <th scope="row">Shopify API Link</th>
          <td><input type="text" name="shopify_link" value="<?php echo esc_attr( get_option('shopify_link') ); ?>" /></td>
          </tr>
           
          <tr valign="top">
          <th scope="row">Default query string</th>
          <td><input type="text" name="default_querystring" value="<?php echo esc_attr( get_option('default_querystring') ); ?>" /></td>
          </tr>
      </table>
      
      <?php submit_button(); ?>

  </form>
  </div>
<?php
}
add_action( 'admin_init', 'shopify_pulling_plugin_settings' );

function shopify_pulling_plugin_settings() {
  register_setting( 'shopify-pulling-plugin-settings-group', 'shopify_link' );
  register_setting( 'shopify-pulling-plugin-settings-group', 'default_querystring' );
}
?>
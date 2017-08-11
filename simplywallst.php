<?php

/*
  Plugin Name: Stock Market Infographics by Simply Wall St
  Plugin URI: https://simplywall.st/documents/stock-market-infographics-wordpress-plugin.zip
  Description: Embed awesome the Simply Wall St stock infographics into your articles to supercharge your reader engagement.
  Version: 1.1.1
  Author: Simply Wall St/ Minh Cung
  Author URI: https://profiles.wordpress.org/simplywallst
  License: GPL2 or later
 */

include_once dirname(__FILE__) . '/plugins.php';
CONST SWS_UNIQUE_SYMBOL_REGEX = '/\A[a-z|A-Z|\d]+:[a-z|A-Z|\d|\.]+\z/';
CONST SWS_SECTIONS_REGEX = '/^[A-Za-z|\-|\_]+$/';
CONST SWS_SLUG_REGEX = '/\w/';
$SWS_SECTIONS = array(
    "intrinsic-value" => "Intrinsic value",
    "past-future-earnings" => "Past and Future Earnings",
    "future-profit" => "Future Revenue and Net Income",
    "future-perf" => "Future Return on Equity",
    "income-statement" => "Income Statement",
    "last-perf" => "Past Revenue and Net Income",
    "net-worth" => "Balance Sheet Net Worth",
    "CEO-details" => "CEO and Management Team",
    "insider_trading" => "Recent Insider Trading",
    "PE-gauge" => "Price Based on past Earnings",
    "PEG-gauge" => "Price Based on Expected Growth",
    "PB-gauge" => "Price Based on Value of Assets"
);

$plugin = new SWSPlugin();

if (SWSPlugin::wp_above_version('4.5')){
  add_action('media_buttons', 'add_sws_media_button', 15);
  add_action('wp_enqueue_media', 'include_sws_media_button_js_file');
  add_filter('media_upload_tabs', 'sws_upload_tab');
  add_action('media_upload_sws_infographics', 'add_sws_form');
  // add_filter('the_content', 'sws_bottom_of_every_post');
  add_action( 'admin_menu', 'sws_plugin_menu' );
  add_action( 'admin_init', 'register_sws_settings' );

  define( 'SWS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
} else {

}

function register_sws_settings() {
  register_setting('sws-referrer-code-settings', 'sws-referrer-code');
}

function sws_plugin_menu(){
    add_submenu_page(
      'options-general.php',
      "Simply Wall Street Settings", 
      "Simply Wall Street", 
      "manage_options",
      "sws_settings",
      "sws_plugin_options"
    );
}

function sws_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
  <h1>Simply Wall Street Infographics Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'sws-referrer-code-settings' ); ?>
    <?php do_settings_sections( 'sws-referrer-code-settings' ); ?>
    
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Referrer code:</th>
      <td><input type="text" name="sws-referrer-code" value="<?php echo esc_html(get_option( 'sws-referrer-code' )); ?>"/></td>
      </tr>
    </table>
    <p>Get your referrer code <a href="https://simplywall.st/user/invite" target="_blank">here</a></p>
    <?php submit_button(); ?>
  </form>

  <?php
}

// function sws_bottom_of_every_post($content) {
//   if (!is_page()){
//     if (preg_match('/simplywall.st/', $content) === 1){
//       return $content . ' <span style="font-size: 11px;">*Infographics powered by <a href="https://simplywall.st/">Simply Wall St</a></span>';
//     }
//     return $content;
//   }
// }

function sws_upload_tab($tabs) {
    $tabs['sws_infographics'] = "SWS Infographics";
    return $tabs;
}

function add_sws_form() {
    wp_iframe('SWS_Iframe');
}

function include_sws_media_button_js_file() {
  wp_enqueue_script( 'jquery' );
	wp_register_style( 'jquery-ui-styles', SWS_PLUGIN_DIR_URL . 'css/jquery-ui.css' );
	wp_enqueue_style( 'jquery-ui-styles' );
   wp_enqueue_script('media_button', SWS_PLUGIN_DIR_URL . 'js/media_button.js', array('jquery'), '1.0', true);
}

function add_sws_media_button() {
    echo '<a href="#" id="insert-sws-media" class="button"><img src="'. SWS_PLUGIN_DIR_URL . 'images/favicon-16x16.png"/>Add SWS Infographics</a>';
}

function sws_form($headerMessage = '') {
	echo '<form action="" method="post" id="image-form" class="media-upload-form type-form">';
	echo '<input type="hidden" name="post_id" value="' . $_REQUEST['post_id'] . '">';
	wp_nonce_field('media-form');
	wp_enqueue_script( 'jquery-ui-autocomplete' );
  if (get_option( 'sws-referrer-code' )){
    echo('<p style="margin-left: 20px">Your referrer code is <b>' . esc_html(get_option( 'sws-referrer-code' )) .'</b></p>');
  } else {
    echo '<p style="margin-left: 20px">You don\'t have a referrer code. You can add it in <b>Settings</b> > <b>Simply Wall Street</b>.</p>';
  }
	echo '<div class="wrap media-embed" style="padding-left: 20px">' . 	$headerMessage .
	'<form>
	<table class="form-table"><tbody>
		<tr class="form-field form-required">
			<th scope="row" class="label">
			<span class="alignleft">Search for Unique Symbol:</span>
			<span class="alignright"><abbr title="required" class="required">*</abbr></span>
			</th>
			<td class="field"><input name="ca_unique_symbol" id="ca_unique_symbol" type="text" style="max-width: 400px;" required value="' . (isset($_POST['ca_unique_symbol']) ? esc_attr($_POST['ca_unique_symbol']) : '') . '"></td>
		</tr>
		<tr class="form-field form-required">
			<th class="label">
			<span class="alignleft">Which Infographics:</span>
			<span class="alignright"><abbr title="required" class="required">*</abbr></span>
			</th>
			<td>
        <select name="section" id="section">
          <option value="intrinsic-value" selected>Intrinsic value</option> 
          <option value="past-future-earnings">Past and Future Earnings</option>
          <option value="future-profit">Future Revenue and Net Income</option>
          <option value="future-perf">Future Return on Equity</option>
          <option value="income-statement">Income Statement</option>
          <option value="last-perf">Past Revenue and Net Income</option>
          <option value="net-worth">Balance Sheet Net Worth</option>
          <option value="CEO-details">CEO and Management team</option>
          <option value="insider_trading">Recent Insider Trading</option>
          <option value="PE-gauge">Price Based on past Earnings</option>
          <option value="PEG-gauge">Price Based on Expected Growth</option>
          <option value="PB-gauge">Price Based on Value of Assets</option>
        </select>
      </td>
		</tr>
    <tr class="form-field form-required">
			<th scope="row" class="label">
			<span class="alignleft">I accept the T&amp;C</span>
			<span class="alignright"><abbr title="required" class="required">*</abbr></span>
			</th>
			<td class="field"><input name="tc" id="tc" type="checkbox" required></td>
		</tr>
    <input name="ca_slug_name" id="ca_slug_name" type="hidden" required/>
	</table>';

	echo <<<HTML
	<br/>
   <p style="color: red">By using our infographics you agree to our <a href="https://simplywall.st/termsandconditions">Term &amp; Conditions</a> and to include a 'Powered by Simply Wall St' on the page.</p>
	<input class="button-primary" type="submit" name="submit" value="Import Infographics" />
  <div id="sws_preview_image" style="margin-top: 2em;"></div>

  <script>
    jQuery(function ($) {
      var previewInfographics = function (e, unique_symbol){
        var SWS_UNIQUE_SYMBOL_REGEX = new RegExp(/^[a-z|A-Z|\d]+:[a-z|A-Z|\d|\.]+$/);
        unique_symbol = unique_symbol || $('#ca_unique_symbol').val();
        var sections = {
          "intrinsic-value" : "Intrinsic value",
          "past-future-earnings" : "Past and Future Earnings",
          "future-profit" : "Future Revenue and Net Income",
          "future-perf" : "Future Return on Equity",
          "income-statement" : "Income Statement",
          "last-perf" : "Past Revenue and Net Income",
          "net-worth" : "Balance Sheet Net Worth",
          "CEO-details" : "CEO and management team",
          "insider_trading" : "Recent Insider Trading",
          "PE-gauge" : "Price Based on past Earnings",
          "PEG-gauge" : "Price Based on Expected Growth",
          "PB-gauge" : "Price Based on Value of Assets"
        };

        if (unique_symbol && (unique_symbol == '' || unique_symbol.match(SWS_UNIQUE_SYMBOL_REGEX) === null)){
          return false;
        }
        var html = '';
        for (var i in sections){
           var link = 'https://simplywall.st/api/section/'+ unique_symbol + '/' + i;
          html += '<h3>'+unique_symbol+' | '+sections[i]+'</h3><button class="button-primary import-this" data-symbol="' + unique_symbol + '" data-section="' + i + '">Import This Infographics</button><img src="' + link + '" style="display:block;    max-width: 684px;"/>';
        }
        $('#sws_preview_image').html(html);
        $('.import-this').click(function (e){
          var jthis = $(this);
          $('#ca_unique_symbol').val(jthis.data('symbol'));
          $('#section').val(jthis.data('section'));
        });
      }
      var itemRenderer = function(ul, item)
        {
          return $( "<li>" )
            .append( "<a><span style='float: right;'>" + item.value + "</span>" + item.label + '</a>')
            .appendTo( ul );
        }
      var input = $('#ca_unique_symbol');
      var inputValue;
      $('#ca_unique_symbol').autocomplete
      ({
        source: function(request, response){
          $.ajax({
            type: "GET",
            crossDomain: true,
            url: "https://simplywall.st/api/search/" + input.val(),
            data: null,
            headers: {          
              Accept: "application/vnd.simplywallst.v2",         
              "Content-Type": "application/json; charset=utf-8"  
            },
            dataType: "json",
            success: function (msg) {
              msg = msg.slice(0, 3)
              response(msg);
            },
            error: function (msg) {
              //
            }
          })
        },
        minLength: 2,
        select: function(event, ui) {
          $('#ca_slug_name').val(ui.item.label);
          previewInfographics(null, ui.item.value);
        },
        change: function (event, ui) {
          
        }
      }).data("ui-autocomplete")._renderItem = itemRenderer;

    });
  </script>
</div>
</form>
HTML;

}

function handleUploadSWSInfographics() {
  GLOBAL $SWS_SECTIONS;
	if (!wp_verify_nonce($_POST['_wpnonce'], 'media-form')) {
		return new WP_Error('sws_infographics', 'Could not verify request nonce');
	}

  if (!isset($_POST['tc'])){
    return new WP_Error('sws_infographics', 'Please accept our term and conditions');
  }

  $unique_symbol = sanitize_text_field($_POST['ca_unique_symbol']);

  if (!preg_match(SWS_UNIQUE_SYMBOL_REGEX, $unique_symbol)){
    return new WP_Error('sws_infographics', 'Invalid Unique Symbol');
  }

  $section = sanitize_text_field($_POST['section']);
  if (!array_key_exists($section, $SWS_SECTIONS) || !preg_match(SWS_SECTIONS_REGEX, $section)){
    return new WP_Error('sws_infographics', 'Invalid Section');
  }

  $slug = sanitize_text_field($_POST['ca_slug_name']);
  if (!preg_match(SWS_SLUG_REGEX, $slug)){
    return new WP_Error('sws_infographics', 'Something went wrong. Please try again!');
  }

	$file = array();
	$file['name'] = $unique_symbol . '-' . $section . '.png';
	$file['tmp_name'] = download_url('https://simplywall.st/api/section/' . $unique_symbol . '/' . $section);

	if (is_wp_error($file['tmp_name'])) {
		@unlink($file['tmp_name']);
		return new WP_Error('sws_infographics', 'Could not download image from Simply Wall Street');
	}

	$attachmentId = media_handle_sideload($file, $_POST['post_id'], $file['name']);
	$attach_data = wp_generate_attachment_metadata( $attachmentId,  get_attached_file($attachmentId));
	wp_update_attachment_metadata( $attachmentId,  $attach_data );
	return $attachmentId;	
}

function SWS_Iframe() {
  GLOBAL $SWS_SECTIONS;
	media_upload_header();
	if (isset($_POST['ca_unique_symbol'])) {
		$attachmentId = handleUploadSWSInfographics();
		if (is_wp_error($attachmentId)) {
			sws_form('<div class="error form-invalid">' . $attachmentId->get_error_message(). '</div>');
		}
		else {
      echo "<style>h3, #plupload-upload-ui,.max-upload-size { display: none }</style>";
       
			media_upload_type_form("image", null, $attachmentId);
      $unique_symbol = sanitize_text_field($_POST['ca_unique_symbol']);
      $section = sanitize_text_field($_POST['section']);
      $ca_name = sanitize_text_field($_POST['ca_slug_name']);
      // replace non letter or digits by -
      $slug = preg_replace('~[^\pL\d]+~u', '-', $ca_name);

      // transliterate
      $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);

      // remove unwanted characters
      $slug = preg_replace('~[^-\w]+~', '', $slug);

      // trim
      $slug = trim($slug, '-');

      // remove duplicate -
      $slug = preg_replace('~-+~', '-', $slug);

      // lowercase
      $slug = strtolower($slug);

      $link = "https://simplywall.st/". $unique_symbol . "/" . $slug . (get_option( 'sws-referrer-code' ) ? "?ref=". esc_html(get_option( 'sws-referrer-code' )) : "") . "#" . $section;
      echo "
      <script>
        jQuery(function ($) {
            $('[name$=\"[image_alt]\"]').val('". $unique_symbol  . " " . $ca_name . " " . $SWS_SECTIONS[$section] ." by Simply Wall St'); 
            $('[name$=\"[post_excerpt]\"]').val('". $unique_symbol . " " . $ca_name . " " . $SWS_SECTIONS[$section] ." by <a href=\"https://simplywall.st/\">Simply Wall St</a>'); 
            $('[name$=\"[post_content]\"]').val('". $unique_symbol . " " . $ca_name . " " . $SWS_SECTIONS[$section] ." by <a href=\"https://simplywall.st/\">Simply Wall St</a>'); 
            $('[name$=\"[url]\"]').val('" . $link . "'); 

            $('[name^=\"send\"]').trigger('click');
        });
      </script>"; 
		}
	}
	else {
		sws_form();
	}
}
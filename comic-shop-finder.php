<?php
/*
Plugin Name: ComicNerd.com Comic Shop Finder
Plugin URI: http://www.comicnerd.com/comicnerd-exclusives/comicnerdcom_comic_shop_finder/
Description: ComicNerd.com Comic Shop Finder provides a widget that shows local comic book stores to the zip code entered.
Version: 0.4
Author: comicnerd, Frasten
Author URI: http://www.comicnerd.com
*/

/* Created by Frasten (email : frasten@gmail.com) under a GPL licence. */

class ComicShopFinder {
	
	function init() {
		if ( !function_exists( 'register_sidebar_widget' ) || !function_exists( 'register_widget_control' ) )
			return;
		
		
		register_sidebar_widget( array( __( 'Comic Shop Finder', 'comic-shop-finder' ), 'widgets' ), array( 'ComicShopFinder', 'print_widget') );
		register_widget_control( array( __( 'Comic Shop Finder', 'comic-shop-finder' ), 'widgets' ), array( 'ComicShopFinder', 'widget_settings' ), 350, 20 );
	}
	
	function print_widget( $args ) {
		extract( $args );
		echo $before_widget;
		echo ComicShopFinder::generate_widget( $before_title, $after_title );
		echo $after_widget;
	}
	
	// 
	function widget_settings() {}
	
	function generate_widget( $before_title, $after_title ) {
		echo $before_title . "Find a comic shop near you" . $after_title . "\n";
		
		ComicShopFinder::printScript();
		echo "<ul class='comic_shop_finder'>\n";
		echo "<form method='post' action='http://csls.diamondcomics.com/default.asp' target='CSLS' id='csls_frm'>\n";
		echo "Your Zip code:<br />\n";
		echo "\t<input type='text' name='zip' maxlength='5' size='7' id='csls_zip'/> \n";
		echo "\t<input type='submit' value='Go!' />\n";
		echo "</form>";
		echo "</ul>\n";
		echo "<div id='csls_loading'></div>\n";
		echo "<div id='csls_output'></div>\n";
		echo "Get this widget at <a href='http://www.comicnerd.com/comicnerd-exclusives/comicnerdcom_comic_shop_finder/'>ComicNerd.com</a>\n";
		
		// todo: manage if length = 0
		
	}
	
	function printScript() {
		?><script type='text/javascript'>
			$j=jQuery.noConflict();		
			$j(document).ready(function(){
				$j("#csls_frm").submit(function() {
					if ($j("#csls_zip").val() == "") {
						$j("#csls_output").html("Please fill in you zip code.");
						return false;
					}
					
					$j("#csls_loading").html("Loading...").show();
					$j("#csls_output").hide();
				<?php
					$this_dir = get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__));
					echo "    \$j.post(\"$this_dir/data_transfer.php\",";
				?>
				 {zip: $j("#csls_zip").val() }, function(data) {
					$j("#csls_output").html(data).show('slow');
					$j("#csls_loading").hide();
					});
					return false;
				});
			});
			
			function CSFopenDetails(id) {
				console.log(id);
				$j("#CSFPassedIDNo").attr( 'value', id );
				$j("#CSFDetailsForm").submit();
			}
		</script>
		<?php
	}
	
	
}

wp_enqueue_script('jquery');
add_action( 'widgets_init', array( 'ComicShopFinder', 'init' ) );
?>

<?php
/*
Plugin Name: BadPass-WP
Plugin URI: http://www.nickbloor.co.uk/badpass/badpass-wp
Description: Helps to promote better password selection by warning users when they are using a common password.
Version: 1.12
Author: Nick Bloor
Author URI: http://www.nickbloor.co.uk/
*/

/**
 * BadPass-WP is designed to promote the use of better passwords by warning the user if their password
 * is one of over 500 commonly used and easy to guess passwords.
 */
//Set up WordPress action handlers
add_action( 'wp_print_styles', 'badpass_wp_add_stylesheet' );
add_action( 'profile_update', 'badpass_wp_profile_updated', 10, 1 );
add_action( 'wp_footer', 'badpass_wp_footer' );

/**
 * Inject the stylesheet for the BadPass warning message.
 */
function badpass_wp_add_stylesheet() {
	//Inject the BadPass warning message stylesheet into the page
	if ( file_exists( WP_PLUGIN_DIR . '/badpass-wp/badpass-wp.css' ) ) {
		wp_register_style('badpass-wp-stylesheet', WP_PLUGIN_URL . '/badpass-wp/badpass-wp.css');
		wp_enqueue_style('badpass-wp-stylesheet');
	}
}

/**
 * Reset the 'using common password' user meta field when a user profile is updated.
 * 
 * @param unknown_type $user_id the ID of the user profile that was updated
 */
function badpass_wp_profile_updated($user_id) {
	//Clear the 'using common password' user meta field in case the user's password changed
	update_user_meta( $user_id, 'badpass_wp_using_common_password', '' );
}

/**
 * Outputs a warning if a user is logged in and using a common password.
 */
function badpass_wp_footer() {
	//A user must be logged in before their password can be checked
	if ( is_user_logged_in() ) {
		//Retrieve the logged in user details
		$current_user = wp_get_current_user();
		
		//Retrieve the 'using common password' user meta field for the logged in user
		$using_common_password = get_user_meta( $current_user->ID, 'badpass_wp_using_common_password', true );
		
		//Test the user's password and set the 'using common password' user meta field if necessary
		if ( '' == $using_common_password ) {
			//Store the result of the password test to use in deciding whether to output a warning
			$using_common_password = ( badpass_wp_test_password( $current_user ) ? 'true' : 'false' );
		} 
		
		//Output a warning message if the user is using a common password
		if ( 'true' == $using_common_password ) {
			badpass_wp_output_warning();
		}
	}
}

/**
 * Tests whether the current logged in user is using one of over 500 common passwords by running each
 * password through the wp_check_password function along with the user's ID and password hash.
 */
function badpass_wp_test_password() {
	//An array of common passwords
	$common_passwords = array('1111', '11111', '111111', '11111111', '112233', '1212', '121212', '123123', '1234', '12345', '123456', '1234567', '12345678', '1313', '131313', '2000', '2112', '2222', '232323', '3333', '4128', '4321', '4444', '5150', '5555', '654321', '6666', '666666', '6969', '696969', '7777', '777777', '7777777', '8675309', '987654', 'aaaa', 'aaaaaa', 'abc123', 'abgrtyu', 'access', 'access14', 'action', 'albert', 'alex', 'alexis', 'amanda', 'amateur', 'andrea', 'andrew', 'angel', 'angela', 'angels', 'animal', 'anthony', 'apollo', 'apple', 'apples', 'arsenal', 'arthur', 'asdf', 'asdfgh', 'ashley', 'asshole', 'august', 'austin', 'baby', 'badboy', 'bailey', 'banana', 'barney', 'baseball', 'batman', 'beach', 'bear', 'beaver', 'beavis', 'beer', 'bigcock', 'bigdaddy', 'bigdick', 'bigdog', 'bigtits', 'bill', 'billy', 'birdie', 'bitch', 'bitches', 'biteme', 'black', 'blazer', 'blonde', 'blondes', 'blowjob', 'blowme', 'blue', 'bond007', 'bonnie', 'booboo', 'boobs', 'booger', 'boomer', 'booty', 'boston', 'brandon', 'brandy', 'braves', 'brazil', 'brian', 'bronco', 'broncos', 'bubba', 'buddy', 'bulldog', 'buster', 'butter', 'butthead', 'calvin', 'camaro', 'cameron', 'canada', 'captain', 'carlos', 'carter', 'casper', 'charles', 'charlie', 'cheese', 'chelsea', 'chester', 'chevy', 'chicago', 'chicken', 'chris', 'cocacola', 'cock', 'coffee', 'college', 'compaq', 'computer', 'cookie', 'cool', 'cooper', 'corvette', 'cowboy', 'cowboys', 'cream', 'crystal', 'cumming', 'cumshot', 'cunt', 'dakota', 'dallas', 'daniel', 'danielle', 'dave', 'david', 'debbie', 'dennis', 'diablo', 'diamond', 'dick', 'dirty', 'doctor', 'doggie', 'dolphin', 'dolphins', 'donald', 'dragon', 'dreams', 'driver', 'eagle', 'eagle1', 'eagles', 'edward', 'einstein', 'enjoy', 'enter', 'eric', 'erotic', 'extreme', 'falcon', 'fender', 'ferrari', 'fire', 'firebird', 'fish', 'fishing', 'florida', 'flower', 'flyers', 'football', 'ford', 'forever', 'frank', 'fred', 'freddy', 'freedom', 'fuck', 'fucked', 'fucker', 'fucking', 'fuckme', 'fuckyou', 'gandalf', 'gateway', 'gators', 'gemini', 'george', 'giants', 'ginger', 'girl', 'girls', 'golden', 'golf', 'golfer', 'gordon', 'great', 'green', 'gregory', 'guitar', 'gunner', 'hammer', 'hannah', 'happy', 'hardcore', 'harley', 'heather', 'hello', 'helpme', 'hentai', 'hockey', 'hooters', 'horney', 'horny', 'hotdog', 'house', 'hunter', 'hunting', 'iceman', 'iloveyou', 'internet', 'iwantu', 'jack', 'jackie', 'jackson', 'jaguar', 'jake', 'james', 'japan', 'jasmine', 'jason', 'jasper', 'jennifer', 'jeremy', 'jessica', 'john', 'johnny', 'johnson', 'jordan', 'joseph', 'joshua', 'juice', 'junior', 'justin', 'kelly', 'kevin', 'killer', 'king', 'kitty', 'knight', 'ladies', 'lakers', 'lauren', 'leather', 'legend', 'letmein', 'little', 'london', 'love', 'lover', 'lovers', 'lucky', 'maddog', 'madison', 'maggie', 'magic', 'magnum', 'marine', 'mark', 'marlboro', 'martin', 'marvin', 'master', 'matrix', 'matt', 'matthew', 'maverick', 'maxwell', 'melissa', 'member', 'mercedes', 'merlin', 'michael', 'michelle', 'mickey', 'midnight', 'mike', 'miller', 'mine', 'mistress', 'money', 'monica', 'monkey', 'monster', 'morgan', 'mother', 'mountain', 'movie', 'muffin', 'murphy', 'music', 'mustang', 'naked', 'nascar', 'nathan', 'naughty', 'ncc1701', 'newyork', 'nicholas', 'nicole', 'nipple', 'nipples', 'oliver', 'orange', 'ou812', 'packers', 'panther', 'panties', 'paris', 'parker', 'pass', 'password', 'patrick', 'paul', 'peaches', 'peanut', 'penis', 'pepper', 'peter', 'phantom', 'phoenix', 'player', 'please', 'pookie', 'porn', 'porno', 'porsche', 'power', 'prince', 'princess', 'private', 'purple', 'pussies', 'pussy', 'qazwsx', 'qwert', 'qwerty', 'qwertyui', 'rabbit', 'rachel', 'racing', 'raiders', 'rainbow', 'ranger', 'rangers', 'rebecca', 'redskins', 'redsox', 'redwings', 'richard', 'robert', 'rock', 'rocket', 'rosebud', 'runner', 'rush2112', 'russia', 'samantha', 'sammy', 'samson', 'sandra', 'saturn', 'scooby', 'scooter', 'scorpio', 'scorpion', 'scott', 'secret', 'sexsex', 'sexy', 'shadow', 'shannon', 'shaved', 'shit', 'sierra', 'silver', 'skippy', 'slayer', 'slut', 'smith', 'smokey', 'snoopy', 'soccer', 'sophie', 'spanky', 'sparky', 'spider', 'squirt', 'srinivas', 'star', 'stars', 'startrek', 'starwars', 'steelers', 'steve', 'steven', 'sticky', 'stupid', 'success', 'suckit', 'summer', 'sunshine', 'super', 'superman', 'surfer', 'swimming', 'sydney', 'taylor', 'teens', 'tennis', 'teresa', 'test', 'tester', 'testing', 'theman', 'thomas', 'thunder', 'thx1138', 'tiffany', 'tiger', 'tigers', 'tigger', 'time', 'tits', 'tomcat', 'topgun', 'toyota', 'travis', 'trouble', 'trustno1', 'tucker', 'turtle', 'united', 'vagina', 'victor', 'victoria', 'video', 'viking', 'viper', 'voodoo', 'voyager', 'walter', 'warrior', 'welcome', 'whatever', 'white', 'william', 'willie', 'wilson', 'winner', 'winston', 'winter', 'wizard', 'wolf', 'women', 'xavier', 'xxxx', 'xxxxx', 'xxxxxx', 'xxxxxxxx', 'yamaha', 'yankee', 'yankees', 'yellow', 'young', 'zxcvbn', 'zxcvbnm', 'zzzzzz', '0', '123456789', 'babygirl', 'lovely', 'rockyou');
	
	//A user must be logged in before the test can run
	$using_common_password = false;
	if ( is_user_logged_in() ) {
		//Retrieve the logged in user details
		$current_user = wp_get_current_user();
		
		//Check each of the common passwords against the user details
		foreach ( $common_passwords as $common_password ) {
			if ( wp_check_password( $common_password, $current_user->user_pass, $current_user->ID) ) {
				$using_common_password = true;
				break;
			}
		}
		
		//Store the result in a user meta field
		if ( $using_common_password ) {
			update_user_meta( $current_user->ID, 'badpass_wp_using_common_password', 'true' );
		} else {
			update_user_meta( $current_user->ID, 'badpass_wp_using_common_password', 'false' );
		}
	}
	
	//Return the result
	return $using_common_password;
}

/**
 * Displays a warning message to the user and a link to wp-admin/profile.php so they can update their
 * password.
 */
function badpass_wp_output_warning() {
	//Check if the WordPress 3.1+ admin bar is enabled
	$current_user = wp_get_current_user();
	$show_admin_bar = get_user_meta( $current_user->ID, 'show_admin_bar_front', true );
	
	//Output a warning
	echo '<p id="badpass_wp_warning' .
		( $show_admin_bar == 'true' ? '_withadminbar' : '' ) .
		'" >' .
		__( 'Warning: The password you are using is a commonly used password that can be easily guessed. It is recommended that you change it immediately.', 'badpass_wp' ) .
		'<a href="' . site_url( '/wp-admin/profile.php' ) . '">' .
		__( 'Click here to update your password.', 'badpass_wp' ) . '</a>' .
		'</p>';
}
?>
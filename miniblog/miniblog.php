<?php
error_reporting(E_ALL ^ E_NOTICE);
/*
Plugin Name: Miniblog
Plugin URI: http://blog.fileville.net/?page_id=121
Description: <i>Miniblog-COMPAT - Designed specificaly for Wordpress 2.1 only. Please do not try to enable this version if you have Wordpress 2.0.7 or below. </i> <br />Allows miniature blogs, links, notes, asides, or whatever to be created. The menu, functionality, and documentation can be found in the Write : Miniblog menu once the plugin is activated. Previous developer <a href="http://mediumbagel.org/">Thomas Cort</a>.
Version: 0.16-COMPAT
Author: Joe
Author URI: http://blog.fileville.net/
*/	

/* Copyright 2006 Joe (ttech5593@gmail.com)
 
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.
   
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/* Edit Only If Needed */
define("DREAMHOST", "FALSE");

/* Please do not edit below this point... */
	
/* We always want to load these */
if(!function_exists('miniblog_return_entries')) {
	function miniblog_return_entries($limit = 5, $offset = 0, $identifier = '', $sortby = '_date', $filter = TRUE, $full = 1, $limitperblog= 0) {
		global $wpdb;
		
		if(!is_numeric($offset)) {
			$offset = 0;
		}

		if(!is_numeric($limit) || $limit < 2) {
			$limit = 4;
		}
		
		if($identifier == 'all') {
			$identifier = '';
		}

		if(!is_numeric($limitperblog)) {
			$limitperblog = 0;
		}

		$limit_q = $offset . ',' . $limit;
		
		$identifier_q = '';
		if($identifier) {
			if(stristr($identifier, '%') !== FALSE) {
				$identifier_q = 'WHERE blog LIKE \'' . $identifier . '\'';
			 } else {
				$identifier_q = 'WHERE blog="' . $identifier . '"';
			}
		}
	
			/* Sort ordering */
		$sort_o = '` ASC';
		if (substr($sortby, 0, 1) == '_') {
			$sort_o = '` DESC';
			$sortby = substr($sortby, 1);
		}
		$sortby_q = 'ORDER BY `' . $sortby;
		$sortby_q .= $sort_o;
		
		/* We can either show the most recent in each blog
		   or show everything. Group By is just that way.
		   The subquery table pre-sorts the data so that
		   the Group By can pick up rows in the desired order.	*/
		if(!empty($identifier) AND $limitperblog == 0) {
			  // If $identifier is not empty and $limitperblog is 0
				$query = 'SELECT * FROM ' . $wpdb->prefix .
						'miniblog ' . $identifier_q . ' ' . $sortby_q .
						' LIMIT ' . $limit_q;
		
				//$query = 'SELECT * FROM `' . $wpdb->prefix . 'miniblog` '.$identifier_q.' LIMIT '.$limit_q.'';
			} else {
			  // If $identifier is empty and $limitperblog is 0 or a number
			      $query = 'SELECT * FROM '.$wpdb->prefix.'miniblog '
	            . ( $limitperblog ? ' GROUP BY `blog`' : '' )
	            . ' ' . $sortby_q . ' LIMIT ' . $limit_q;
            }
		$results = $wpdb->get_results($query);
		
		if ($results) {
			$cnt = count($results);
			for($i = 0; $i < $cnt; $i++) {
				$results[$i]->blog  = stripslashes($results[$i]->blog);
				$results[$i]->title = apply_filters('the_title', stripslashes($results[$i]->title));
				$results[$i]->url   = stripslashes($results[$i]->url);

				if($results[$i]->text) {

					if (preg_match('/\[readon\]/',$results[$i]->text) && $full == 0) {
						$parts = preg_split('/\[readon\]/',$results[$i]->text);
						$results[$i]->text = $parts[0] . '... <a href="' . 
							miniblog_create_post_url($results[$i]->id) . '">read on</a>.';
					} else if ($full == 1) {
						$results[$i]->text = preg_replace('/\[readon\]/','',$results[$i]->text);
					}

					if ($filter == 'ON') {
						$results[$i]->text = apply_filters('the_content', stripslashes($results[$i]->text));
					} else {
						$results[$i]->text = stripslashes($results[$i]->text);
					}
				}
			}
		} else {
			$results = array();
		}
		
		return $results;
	}

}

/* Simple, beginner-friendly (not to mention smart) version of the above function */
if(!function_exists('miniblog_list_entries')) {
	function miniblog_list_entries($before = '<li>', $between = '<br />', $after = '</li>', $identifier = '', $limit = 5, $offset = 0, $sortby = '_date', $filter = TRUE, $full = 1) {
		$entries = array();
		$entry   = '';
		$entries = miniblog_return_entries($limit, $offset, $identifier, $sortby, $filter, $full);

		foreach($entries as $entry) {
			$date = date("F j, Y", strtotime($entry->date));
			echo $before;

			if ($entry->title && $entry->url) {
				echo '<a href="' . $entry->url . '">' . $entry->title . '</a>';
			} elseif ($entry->title) {
				echo $entry->title;
			} elseif ($entry->url) {
				echo '<a href="' . $entry->url . '">' . $date . '</a>';
			} else {
				echo $date;
			}

			if(trim($entry->text)) {
				if (strtolower($between) == strtolower('<none>')) {
					echo str_replace("<p>" , '', str_replace("</p>" , '', $entry->text));
				} else {
					echo $between;
					echo str_replace("\n" , '', $entry->text);
				}
			}

			echo $after . "\n";
		}
	}

}

function miniblog_get_verison($miniblog_version="0.16-COMPAT") {
	if(ini_get('allow_url_fopen') == 'On' OR (ini_get('allow_url_fopen') == 1)) {
 // If this is disabled and is somehow selected stop the error from being shown
	 	$version_file = @file("http://blog.fileville.net/miniblog/version.xml");	
		$version_file = implode('', $version_file);
	} else {
	 	if(function_exists('curl_init')) {
			$version_check = curl_init();
		curl_setopt($version_check, "CURLOPT_URL", $url);
		curl_setopt($version_check, "CURLOPT_RETURNTRANSFER", TRUE);
		/* This HAS to be set, if it is not you will be blocked */
		curl_setopt($version_check, "CURLOPT_USERAGENT", "Miniblog Version Checker (Miniblog $current)");
		$version_file = curl_exec($version_check);
		curl_close($version_check);
		} else {
			$disable = TRUE;
		  	echo "<!-- Cannot get version information for some reason, so lets just lets just display the old link --> <a href=\"http://blog.fileville.net/?page_id=121\">Check for Updates</a> ";
		}
	} 
  /* Finally get the version and check to see if Miniblog is up to date */
  	if(!isset($disable) OR $disable !== TRUE) {
	 	$pattern = "/\<version>(.*?)\<\/version>/";
		preg_match($pattern, $version_file, $pat);
	    $string = trim($pat[1]);
		$v = version_compare($string, $miniblog_version);
		if (($v == -1) OR ($v == 0)) { // Compare version
			echo "Miniblog is up to date. ";
		} else {
			echo " <a href=\"#\" onclick=\"javascript:alert('Please Upgrade. Your version is $miniblog_version, the current version is $string.')\"><strong><font color=\"red\">Miniblog is out of date!</font></strong> </b></a> ";
		}
	}
}
/* Get the current miniblog directory */
function miniblog_get_dirname($separator="") {
   /* If $separator is ever empty, lets try to fill in the separator */
/*	if(empty($separator) AND defined(DIRECTORY_SEPARATOR)) {
			$separator = DIRECTORY_SEPARATOR;
	}*/
//	$separator = "%^(http://www.|http://|www.)((.{1,}))$%";
	$plugins = get_settings('active_plugins');
	//$file = DIRECTORY_SEPARATOR."miniblog.php<Br />";
	 foreach($plugins as $plugin) {		   
	   if($dir = str_replace("/miniblog.php", "", $plugin)) {
	     break;
	   }
	}
 return $dir."/";
	
	//var_dump(in_array("miniblogblog", $plugins));
	//echo preg_match($separator, get_settings('siteurl'), $matches)."<BR>";
//	print_r($matches);
//	echo "<BR>";
   // stristr("http://",get_settings('siteurl'))."<BR>";
//	echo str_replace(get_settings('siteurl'), "", $_SERVER['PHP_SELF'])."<br />";
	//strrev(dirname(strrev($_SERVER['PHP_SELF'])));
	//$dirname = explode($separator, dirname($_SERVER['PHP_SELF']));
	//print_r($dirname);
}

/* Creates the RSS feed URL to use in the href="" tag */
if(!function_exists('miniblog_create_rss_url')) {
	function miniblog_create_rss_url($limit = 5, $offset = 0, $identifier = '', $sortby = '_date', $title = '%site_name%', $description = '%site_description%', $version = 2) {
		/* Get the blog URL */
		$url = get_settings('siteurl');
		
		/* Build new URL */
		$url .= '/wp-content/plugins/' . miniblog_get_dirname("/") . basename(__FILE__);
		$url .= '?action=rss&amp;n=' . $limit . '&amp;o=' . $offset;
		$url .= '&amp;q=' . htmlentities(urlencode($identifier)) . '&amp;s=' . htmlentities(urlencode($sortby));
		$url .= '&amp;t=' . htmlentities(urlencode($title)) . '&amp;d=' . htmlentities(urlencode($description));
		$url .= '&amp;v=' . $version;

		return $url;
	}

}


/* Creates the Archive URL to use in the href="" tag */
if(!function_exists('miniblog_create_archive_url')) {
	function miniblog_create_archive_url($limit = 10, $offset = 0, $identifier = '', $sortby = '_date', $title = '', $before = '<li>', $between = '<br />', $after = '</li>', $full = 1) {
		/* Get the blog URL */
		$url = get_settings('siteurl');
		
		/* Build new URL */
		$url .= '/wp-content/plugins/' . miniblog_get_dirname("/") . basename(__FILE__);
		$url .= '?action=archive&amp;limit=' . $limit . '&amp;offset=' . $offset;
		$url = ($identifier != '') ? $url . '&amp;category=' . htmlentities(urlencode($identifier)) : $url;
		$url .= "&amp;sortby=" . htmlentities(urlencode($sortby));
		$url = ($title != '') ? $url . '&amp;title=' . htmlentities(urlencode($title)) : $url;
		$url .= '&amp;before=' . htmlentities(urlencode($before));
		$url .= '&amp;between=' . htmlentities(urlencode($between));
		$url .= '&amp;after=' . htmlentities(urlencode($after));
		$url .= '&amp;full=' . $full;
		return $url;
	}
	
}

/* Creates the Single Post URL to use in the href="" tag */
if(!function_exists('miniblog_create_post_url')) {
	function miniblog_create_post_url($postid) {
		/* Get the blog URL */
		$url = get_settings('siteurl');
		
		/* Build new URL */
		$url .= '/wp-content/plugins/' . basename(__FILE__);
		$url .= '?action=single_post&amp;postid=' . $postid;

		return $url;
	}

}

/* Cooperation with other popular plugins */
if(!function_exists('miniblog_cooperate')) {
	function miniblog_cooperate() {
		/* Staticize Reloaded */
		if(function_exists('StaticizeClean')) {
			StaticizeClean();
		}
	}
}

/* My general purpose variable sanitizer */
if(!function_exists('miniblog_clean_var')) {
	function miniblog_clean_var(&$var) {
		return trim(strip_tags(stripslashes($var)));
	}
}

/* This code is called when the plugin is viewed from the admin panel */
if(strpos($_SERVER['PHP_SELF'], 'wp-admin') !== FALSE) {
	if($_GET['page'] == basename(__FILE__)) {
		/* This function echos the plugin page contents */
		if (!function_exists('miniblog_render_plugin_page')) {

			function miniblog_render_plugin_page($post_date='',$post_blog='',$post_title='',$post_url='',$post_text='', $post_id='') {
				global $wpdb;

				if(!$post_id) {
					$post_id = '-';
				}

				if(!$post_blog) {
					$post_blog = 'default';
				}

				/* Number of posts to list per page (editing pagination) */
				$per_page = 50; ?>
				<div class="wrap" style="text-align: center">
					<a href="post-new.php?page=<?php echo basename(__FILE__); ?>">Write new post</a> |
					<a href="#list">Edit posts</a> |
					<a href="#docs">Documentation and Help</a> | <?php
$version = miniblog_get_verison();
$v = version_compare($string, $version);
unset($v, $string, $xml); // Clean up variables
?> |
					<a href="#uninstall">Uninstall</a>				</div>
				
				<div class="wrap"><a name="post"></a>
					<h2><?php _e('Create/Edit') ?></h2>
					<form name="post" id="post" action="post-new.php?page=<?php echo basename(__FILE__); ?>" method="post">
					<!--<script type="text/javascript">
							function focusit() {
								// focus on first input field
								document.post.name.focus();
							}
							window.onload = focusit;
						</script>-->
						<div id="poststuff">
							<input type="hidden" name="action" value="saveminiblogpost" />
							<input type="hidden" name="id" value="<?php echo $post_id; ?>" />
							<fieldset id="titlediv">
								<legend><a href="#titlefield"><?php _e('Title') ?></a></legend> 
								<div><input type="text" name="title" size="30" tabindex="1" value="<?php echo $post_title; ?>" id="title" /></div>
							</fieldset>
						
							<fieldset id="titlediv">
								<legend><a href="#urlfield"><?php _e('URL') ?></a></legend> 
								<div><input type="text" name="url" size="30" tabindex="2" value="<?php echo $post_url; ?>" id="title" /></div>
							</fieldset>
				
							<fieldset id="titlediv">
								<legend><a href="#blogidfield"><?php _e('Blog Identifier [?]') ?></a></legend> 
								<div><input type="text" name="blog" size="30" tabindex="2" value="<?php echo $post_blog; ?>" id="title" /></div>
							</fieldset>
			
							<?php if ($_GET['action'] == 'editminiblogpost') { ?>
							<fieldset id="titlediv">
								<legend><a href="#timestampfield"><?php _e('Time Stamp') ?></a></legend> 
								<div><input type="text" name="date" size="30" tabindex="2" value="<?php echo $post_date; ?>" id="title" /></div>
							</fieldset>
							<?php } ?>
						</div>
					<fieldset <?php //echo "id=\"".(user_can_richedit() ? 'postdivrich' : 'postdiv')."\""; ?> style="clear: both;">
						<br /> <!-- Fixes line break bug -->
							<div><?php 
if(isset($editor)) { unset($editor); } // Unset Editor
ob_start();
the_editor($post_text, 'content');
$editor = ob_get_contents();
ob_end_clean();	
echo str_replace("id='content'></textarea>", "id='content'>$post_text</textarea>", $editor);					
?></div>

						</fieldset>
						<p class="submit">
							<input type="submit" name="submit" value="<?php _e('Save/Publish') ?>" style="font-weight: bold;" tabindex="4" />
						</p>
					</form>
				</div>
				
				<div class="wrap">
					<a name="list"></a>
					<h2><?php _e('Post List') ?></h2>
					<table width="100%" cellpadding="3" cellspacing="3">
						<tr>
							<th scope="col"><?php _e('ID') ?></th>
							<th scope="col"><?php _e('Blog') ?></th>
							<th scope="col"><?php _e('Title') ?></th>
							<th scope="col"><?php _e('URL') ?></th>
							<th scope="col"></th> <?php /* Edit button */ ?>
							<th scope="col"></th> <?php /* Delete button */ ?>
						</tr>
						<?php
							if(is_numeric($_GET['p']) && $_GET['p'] > 1) {
								$page           = $_GET['p'];
								$page_url       = '&amp;p=' . $page;
								$page_first_num = ($page-1)*$per_page;

								if(!$page_first_num) {
									$page_first_num = '0';
								}

								$page_query = $page_first_num . ',' . ($page+$per_page);
							} else {
								$page       = 1;
								$page_url   = '&amp;p=' . $page;
								$page_query = '0,' . ($page+$per_page);
							}
							$posts = $wpdb->get_results('SELECT id,blog,title,url FROM ' . $wpdb->prefix . 'miniblog ORDER BY `date` DESC LIMIT ' . $page_query);
							if($posts) {
								$_alt = ' class="alternate"';
								foreach($posts as $post) {
									?>
									<tr<?php _e($_alt); ?>>
										<td><?php _e($post->id); ?></td>
										<td><?php _e(stripslashes($post->blog)); ?></td>
										<td><?php _e(stripslashes($post->title)); ?></td>
										<td><?php _e(stripslashes($post->url)); ?></td>
										<td><a href="post-new.php?page=<?php echo basename(__FILE__); ?>&amp;action=editminiblogpost&amp;id=<?php _e($post->id . $page_url) ?>#post"><?php _e('Edit'); ?></a></td>
										<td><a href="post-new.php?page=<?php echo basename(__FILE__); ?>&amp;action=deleteminiblogpost&amp;id=<?php _e($post->id . $page_url) ?>#list" class="delete" onclick="return confirm('Are you sure you want to delete this post?')"> <?php _e('Delete'); ?> </a></td>
									</tr>
									<?php
									if($_alt != ' class="alternate"') $_alt = ' class="alternate"';
									else $_alt = '';
								}
							}
						?>
						<?php /* Calculate offsets and pagination */
							$prev_page_url = '';
							$next_page_url = '';
							if($page > 1) {
								$prev_page     = $page - 1;
								$prev_page_url = '&amp;p=' . $prev_page;
								$next_page     = $page + 1;
								$next_page_num = (($next_page-1)*$per_page);

								if(!$next_page_num) {
									$next_page_num = '0';
								}

								$next_page_query = $next_page_num . ',' . ($next_page+$per_page);
							} else {
								$next_page       = 2;
								$next_page_query = $per_page . ',' . ($next_page+$per_page);
							}
							/* Find out if there are more entries on the next page */
							$are_more = $wpdb->get_results('SELECT id FROM ' . $wpdb->prefix . 'miniblog LIMIT ' . $next_page_query);
							if(count($are_more)) {
								if(is_numeric($are_more[0]->id)) {
									$next_page_url = '&amp;p=' . $next_page;
								}
							}
						?>
						<p style="text-align: right">
							<?php if($prev_page_url) { ?><a href="post-new.php?page=<?php echo basename(__FILE__); ?><?php _e($prev_page_url); ?>#list">&laquo; Previous</a> <?php } ?>
							<?php if($next_page_url) { ?><a href="post-new.php?page=<?php echo basename(__FILE__); ?><?php _e($next_page_url); ?>#list">Next &raquo;</a> <?php } ?>
						</p>
					</table>
				</div>
				<?php 	
			$doc = ABSPATH .'/wp-content/plugins/miniblog/documentation.php'; 
			if(file_exists($doc)) {
			  include 'documentation.php';
			} else { ?>
			   <div class="wrap">
			   <h2><?php _e('Could not load documentation!') ?></h2> 
			   		<!-- Debug: <?php file_exists('documentation.php'); ?> -->
			<?php } ?>
				<div class="wrap">
					<a name="uninstall"></a><h2><?php _e('Uninstall'); ?></h2>
					<p>To remove the table from the database, do it manually or enter the string "Remove Thyself" (case sensitive) in the box
					below and press submit.</p>
					<p>
				  <form name="miniblog" id="miniblog" action="post-new.php?page=<?php echo basename(__FILE__); ?>" method="post">
							<input type="text" name="delstring" />
							<input type="submit" name="submit" value="<?php _e('Delete') ?>" style="font-weight: bold;" tabindex="4" />
				  </form>
					</p>
				</div> <?php
			}
		}
		/* Options page callback function */
		if(!function_exists('miniblog')) {
			function miniblog() {
				global $wpdb;

				$wpdb->query('CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'miniblog` (
						`id` bigint(20) unsigned NOT NULL auto_increment,
						`date` datetime NOT NULL default \'0000-00-00 00:00:00\',
						`blog` text NOT NULL,
						`title` text NOT NULL,
						`url` text NOT NULL,
						`text` text NOT NULL,
						FULLTEXT (`blog`),
						PRIMARY KEY  (`id`)) ENGINE=MYISAM');

				/* If the user requested to uninstall */
				if($_POST['delstring'] == 'Remove Thyself') {
					$wpdb->query('DROP TABLE `' . $wpdb->prefix . 'miniblog`'); ?>
					<div id="message" class="updated fade">
					  <p><strong>
						The Miniblog table has been deleted from the MySQL database. To finish
						uninstalling the plugin, deactivate it from the "Plugins" menu and delete
						the file from the plugins directory.</strong></p>
					</div>
					<?php miniblog_cooperate();
				/* If the user wants to edit a post */
				} elseif ($_GET['action'] == 'editminiblogpost') {
					$id = $_GET['id'];
					if (!is_numeric($_GET['id'])) $id = 0;
					$entry = '';
					$entry = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'miniblog WHERE id=' . $id . ' LIMIT 1');
					if ($entry) {
						$entry = $entry[0];
						if($entry->date) $post_date = stripslashes($entry->date);
						if($entry->blog) $post_blog = stripslashes($entry->blog);
						if($entry->title) $post_title = stripslashes($entry->title);
						if($entry->url) $post_url = stripslashes($entry->url);
						if($entry->text) $post_text = stripslashes($entry->text);
						if($entry->id) $post_id = stripslashes($entry->id);
						miniblog_render_plugin_page($post_date,$post_blog,$post_title,$post_url,$post_text,$post_id);
					} else {
						?> <div id="message" class="updated fade">
					  <p><font color="red">Post not found.</font></p>
					</div>
						<?php miniblog_render_plugin_page();
					}
				/* If the user wants to save a post */
				} elseif ($_POST['action'] == 'saveminiblogpost') {
					/* If there's an ID in the data, it's to save (not to add) */
					if($_POST['id'] && is_numeric($_POST['id'])) {
						if(!$wpdb->query('UPDATE ' . $wpdb->prefix . 'miniblog SET
								date="' . mysql_escape_string($_POST['date']) .'",
								blog="' . mysql_escape_string($_POST['blog']) .'",
								title="' . mysql_escape_string($_POST['title']) .'",
								url="' . mysql_escape_string($_POST['url']) .'",
								text="' . mysql_escape_string($_POST['content']) . '"
								WHERE id=' . $_POST['id'])) {
							?> <div id="message" class="updated fade"><p><font color="red">Unable to update entry.</font></p></div>
							<?php miniblog_render_plugin_page();
						} else {
							?> <div id="message" class="updated fade"><p><font color="green">Entry successfully updated</font></p></div>
							<?php miniblog_render_plugin_page();
							miniblog_cooperate();
						}
					/* There's no ID specified, so it's to add a new entry */
					} else {
						$timedate = gmdate('Y-m-d H:i:s', (time() + (get_settings('gmt_offset') * 3600)));
						if(!$wpdb->query('INSERT INTO ' . $wpdb->prefix . 'miniblog SET
									date="'. $timedate .'",
									blog="' . mysql_escape_string($_POST['blog']) .'",
									title="' . mysql_escape_string($_POST['title']) .'",
									url="' . mysql_escape_string($_POST['url']) .'",
									text="' . mysql_escape_string($_POST['content']) . '"')) {
							?> <div id="message" class="updated fade">
							  <p><font color="red">Unable to add entry.</font></p>
							</div>
							<?php miniblog_render_plugin_page();
						} else {
							?> <div id="message" class="updated fade"><p><font color="green">Entry successfully added.</font></p></div>
							<?php miniblog_render_plugin_page();
							miniblog_cooperate();
						}
					}
				/* If the user wants to delete an entry */
				} elseif ($_GET['action'] == 'deleteminiblogpost') {
					if(!$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'miniblog WHERE id=' . $_GET['id'])) {
						?> <div id="message" class="updated fade"><p><font color="red">Unable to delete entry.</font></p></div>
						<?php miniblog_render_plugin_page();
					} else {
						?> <div id="message" class="updated fade"><p>Entry successfully deleted.</p></div>
						<?php miniblog_render_plugin_page();
						miniblog_cooperate();
					}
				/* No actions */
				} else {
					miniblog_render_plugin_page();
				}
			}
		}
	}
/* This isn't a plugin page and it's not being called from WordPress */
} else {
	if(isset($_GET['action'])) {
		/* Just to make sure...again... */
		if (get_magic_quotes_gpc()) {
			foreach($_GET as $k => $v) {
				$_GET[$k] = stripslashes($v);
			}

			foreach($_POST as $k => $v) {
				$_POST[$k] = stripslashes($v);
			}
		}
	$wordpress_dir = dirname(dirname(dirname(dirname(__FILE__))));
			if(DREAMHOST == 'FALSE') {
				/* Call up the WordPress configuration stuff */
	
				if (!file_exists($wordpress_dir . '/wp-config.php')) {
					die('Can\'t load wp-config.php (located in "' .
					$wordpress_dir . '"). You must put this plugin in
					your plugins directory ("/wordpress/wp-content/plugins/").');
				}
			}


		require_once($wordpress_dir . '/wp-config.php');

		/* RSS display */
		if($_GET['action'] == 'rss') {
			/* Get the blog name and description (stupid stupid WordPress making me do this) */
			$blog_name = get_settings('blogname');
			$blog_description = get_settings('blogdescription');
			
			/* Return to sanity */
			$number = $_GET['n'];
			$offset = $_GET['o'];
			$blog_term = htmlspecialchars(urldecode($_GET['q']));
			$sort_by = htmlspecialchars(urldecode($_GET['s']));
			$title = htmlspecialchars(urldecode($_GET['t']));
			$description = htmlspecialchars(urldecode($_GET['d']));
			
			/* Parse custom tags */
			$title = str_replace('%site_name%', $blog_name, $title);
			$description = str_replace('%site_name%', $blog_name, $description);
			$title = str_replace('%site_description%', $blog_description, $title);
			$description = str_replace('%site_description%', $blog_description, $description);
			
			/* Clean the variables */
			$varstoclean = array(&$number, &$offset, &$blog_term, &$sort_by, &$title, &$description);
			foreach($varstoclean as $varname) {
				$varname = miniblog_clean_var($varname);
			}
			
			/* Render the RSS */
			header('Content-type: text/xml; charset=' . get_settings('blog_charset'), true); 
			echo "<?xml version=\"1.0\" encoding=\"" . get_settings('blog_charset') . "\"?>\n";

			/* Modifications by Glenn Slaven 2005-06-20; added RSS 2.0 and fixed a duplicate entry bug */
			switch ($_GET['v']) {
				case '0.92':
					echo "<rss version=\"0.92\">\n";
					echo "\t<channel>\n";
					echo "\t\t<title>" . miniblog_clean_var($title) . "</title>\n";
					echo "\t\t<link>";

					bloginfo('url');

					echo "</link>\n";
					echo "\t\t<description>" . miniblog_clean_var($descriptions) . "</description>\n";
					echo "\t\t<docs>http://backend.userland.com/rss092</docs>\n";

					$entries = miniblog_return_entries($number, $offset, $blog_term, $sort_by, FALSE);

					foreach($entries as $entry) {
						echo "\t\t<item>\n"; 
						echo "\t\t\t<title>" . miniblog_clean_var($entry->title) . "</title>\n";
						echo "\t\t\t<description>" . miniblog_clean_var($entry->text) . "</description>\n";

						if (miniblog_clean_var($entry->url)) {
							echo "\t\t\t<link><![CDATA[" . miniblog_clean_var($entry->url) . "]]></link>\n";
						}

						echo "\t\t</item>\n";
					}

					break;

				default /* RSS 2.0 */:
					echo "<rss version=\"2.0\">\n";
					echo "\t<channel>\n";
					echo "\t\t<title>" . miniblog_clean_var($title) . "</title>\n";
					echo "\t\t<link>";

					bloginfo('url');

					echo "</link>\n";
					echo "\t\t<description>" . miniblog_clean_var($descriptions) . "</description>\n";
					echo "\t\t<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

					$entries = miniblog_return_entries($number, $offset, $blog_term, $sort_by, FALSE);

					foreach($entries as $entry) {

						echo "\t\t<item>\n";
						echo "\t\t\t<title>" . miniblog_clean_var($entry->title) . "</title>\n"; 
						echo "\t\t\t<description>" . miniblog_clean_var($entry->text) . "</description>\n";
						echo "\t\t\t<pubDate>".mysql2date('D, d M Y H:i:s O', $entry->date, false)."</pubDate>\n";

						if (miniblog_clean_var($entry->url)) {
							echo "\t\t\t<link><![CDATA[" . miniblog_clean_var($entry->url) . "]]></link>\n"; 
						}

						echo "\t\t</item>\n"; 
					}
			}

			echo "\t</channel>\n";
			print "</rss>"; 

		} else if ($_REQUEST['action'] == "single_post") {
			global $wpdb;

			require_once( ABSPATH . 'wp-blog-header.php');
			get_header();
			echo '<div id="content" class="narrowcolumn">';

			$postid = (isset($_REQUEST['postid']) && is_numeric($_REQUEST['postid'])) ? $_REQUEST['postid'] : -1;

			if ($postid < 0) {
				echo '<h2>Invalid Post</h2>';
			} else {
				$sql  = 'SELECT * FROM `' . $wpdb->prefix . 'miniblog` ';
				$sql .= "WHERE id = '$postid'";

				$results = $wpdb->get_results($sql);

				if ($results) {

					$entry = $results[0];

					$date = date("F j, Y", strtotime($entry->date));
					echo '<p><strong>';

					if ($entry->title && $entry->url) {
						echo '<a href="' . stripslashes($entry->url) . '">' . stripslashes($entry->title) . '</a>';
					} elseif ($entry->title) {
						echo stripslashes($entry->title);
					} elseif ($entry->url) {
						echo '<a href="' . stripslashes($entry->url) . '">' . $date . '</a>';
					} else {
						echo $date;
					}

					echo '</strong><blockquote>'; 

					$entry->text = preg_replace('/\[readon\]/','',$entry->text);

					if(trim($entry->text)) {
						$text = str_replace("\n" , '', $entry->text);
						echo stripslashes($text);
					}

					echo '</blockquote></p>';
				} else {
					echo '<h2>Invalid Post</h2>';
				}
			}
			echo '</div>';

			get_footer();

		} else if ($_REQUEST['action'] == "archive") {
			global $wpdb;

			$sortby =  isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : '_date';
			$limit  = (isset($_REQUEST['limit' ]) && is_numeric($_REQUEST['limit' ])) ? $_REQUEST['limit' ] : 10;
			$offset = (isset($_REQUEST['offset']) && is_numeric($_REQUEST['offset'])) ? $_REQUEST['offset'] :  0;
			$before = isset($_REQUEST['before']) ? $_REQUEST['before'] : '<p><strong>';
			$between= isset($_REQUEST['between']) ? $_REQUEST['between'] : '</strong><blockquote>';
			$after  = isset($_REQUEST['after']) ? $_REQUEST['after'] : '</blockquote></p>';
			$full   = isset($_REQUEST['full']) && is_numeric($_REQUEST['full']) ? $_REQUEST['full'] : 1;


			require_once( ABSPATH . 'wp-blog-header.php');
				echo "NOTE:<br /><pre>".var_dump($_SESSION)."</pre><br /><br />";
			get_header();
				echo "NOTE:<br /><pre>".var_dump($_SESSION)."</pre><br /><br />";
			echo '<div id="content" class="narrowcolumn">';

			if (isset($_REQUEST['title'])) {
				echo '<h2>' . $_REQUEST['title'] . '</h2>';
			} else {
				echo '<h2>Miniblog Archive</h2>';
			}

			/* display only a specific category */
			if (isset($_REQUEST['category'])) {

				miniblog_list_entries($before, $between, $after, $_REQUEST['category'], $limit, $offset, $sortby, FALSE, $full);

			} else {

				/* Display All categories */
				echo 'Categories:';
				$results = $wpdb->get_results('SELECT DISTINCT blog FROM `' . $wpdb->prefix . 'miniblog`');
				if ($results) {
					$cnt = count($results);
					for ($i = 0; $i < $cnt; $i++) {
						echo ' <a href=" ' . miniblog_create_archive_url($limit, 0, $results[$i]->blog, $sortby, isset($_REQUEST['title']) ? $_REQUEST['title'] : 'Miniblog Archive',$before, $between, $after, $full) .'">' . $results[$i]->blog . '</a> ';
					}
				}

				miniblog_list_entries($before, $between, $after, '', $limit, $offset, $sortby, FALSE, $full);
			}


			/* next/prev links */
			$sql = 'SELECT * FROM `' . $wpdb->prefix . 'miniblog`';
			if (isset($_REQUEST['category'])) {
				$sql = $sql . " WHERE blog like '" . $_REQUEST['category'] . "'";
			}

			echo '<table border="0" width="100%"><tr><td width="50%" align="left">';

			$results = $wpdb->get_results($sql);
			if ($results) {
				$cnt = count($results);
			}

			if ($results && $offset > 0) {
				if (isset($_REQUEST['category'])) {
					echo '<a href="' . miniblog_create_archive_url($limit, ($offset - $limit > 0) ? $offset - $limit : 0, $_REQUEST['category'], $sortby, isset($_REQUEST['title']) ? $_REQUEST['title'] : 'Miniblog Archive', $before, $between, $after, $full) . '">Previous Page</a>';
				} else {
					echo '<a href="' . miniblog_create_archive_url($limit, ($offset - $limit > 0) ? $offset - $limit : 0, $_REQUEST['category'], $sortby, isset($_REQUEST['title']) ? $_REQUEST['title'] : 'Miniblog Archive', $before, $between, $after, $full) . '">Previous Page</a>';
				}
			}
			echo '&nbsp;</td><td width="50%" align="right">&nbsp;';
			if ($results && $cnt > ($offset+$limit)) {
				if (isset($_REQUEST['category'])) {
					echo '<a href="' . miniblog_create_archive_url($limit, $offset + $limit, $_REQUEST['category'], $sortby, isset($_REQUEST['title']) ? $_REQUEST['title'] : 'Miniblog Archive', $before, $between, $after, $full) . '">Next Page</a>';
				} else {
					echo '<a href="' . miniblog_create_archive_url($limit, $offset + $limit, $_REQUEST['category'], $sortby, isset($_REQUEST['title']) ? $_REQUEST['title'] : 'Miniblog Archive',$before, $between, $after, $full) . '">Next Page</a>';
				}
			}
			echo "NOTE:<br /><pre>".var_dump($_SESSION)."</pre><br /><br />";
			echo '</td></tr><tr><td colspan="2" align="center">';
			echo '<small>Powered by <a href="http://blog.fileville.net/?page_id=121">Miniblog</a></small>';
			echo '</td></tr></table></div>';

			get_footer();
		}
	}
}

/* Add the menu item */
if(!function_exists('miniblog_menu')) {
	function miniblog_menu () {
			add_submenu_page('post.php', 'Miniblog', 'Miniblog', 9, basename(__FILE__), 'miniblog');
		}
	}
/* Add what we need to the Wordpress admin area header */
if(!function_exists('miniblog_header')) {
	function miniblog_header($switch=0) {
	  if(stristr($_SERVER['REQUEST_URI'], "miniblog")) {
		  if(user_can_richedit()) {	
		  	$site_url = get_settings('siteurl');
		  	/* If your having problem just comment the line below out */
		  	$site_url = $site_url;
		  	
		  	$opt = ($switch == 1) ? "?ver=20061113" : "";
			echo "<script type='text/javascript' src='".$site_url."/wp-includes/js/tinymce/tiny_mce_gzip.php$opt'></script>\n<script type='text/javascript' src='".$site_url."/wp-includes/js/tinymce/tiny_mce_config.php$opt'></script>";
		  }
	   echo "\n<style type=\"text/css\">
		p.code {
		border: 1px solid #CCC;
		background: #EEE;
		padding: 10px;
		}
	</style>";
	  }
	}
}

if(function_exists('add_action')) {
	add_action('admin_menu', 'miniblog_menu');
	add_action('admin_head', 'miniblog_header');
}

?>
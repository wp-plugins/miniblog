<?php
/*
Plugin Name: Miniblog
Plugin URI: http://mediumbagel.org/?page_id=16
Description: Allows miniature blogs, links, notes, asides, or whatever to be created. The menu, functionality, and documentation can be found in the Write : Miniblog menu once the plugin is activated. This plugin was originally authored by <a href="http://www.nmyworld.com/">Ryan Poe</a>.
Author: Thomas Cort <http://mediumbagel.org>
Version: 0.6
Author URI: http://mediumbagel.org/
*/

/* We always want to load these */
if(!function_exists('miniblog_return_entries')) {
	function miniblog_return_entries($limit = 5, $offset = 0, $identifier = '', $sortby = '_date', $filter = TRUE) {
		global $wpdb, $table_prefix;
		
		if(!is_numeric($offset)) $offset = 0;
		if(!is_numeric($limit)) $limit = 5;
		
		$limit_q = $offset . ',' . ($offset + $limit);
		
		$identifier_q = '';
		if($identifier) {
			if(stristr($identifier, '%') !== FALSE)
				$identifier_q = 'WHERE blog LIKE \'' . $identifier . '\'';
			else
				$identifier_q = 'WHERE blog="' . $identifier . '"';
		}
		
		/* Sort ordering */
		$sort_o = '` ASC';
		if (substr($sortby, 0, 1) == '_') {
			$sort_o = '` DESC';
			$sortby = substr($sortby, 1);
		}
		
		$sortby_q = 'ORDER BY `' . $sortby;
		$sortby_q .= $sort_o;
		
		$results = $wpdb->get_results('SELECT * FROM ' . $table_prefix .
										'miniblog ' . $identifier_q . ' ' . $sortby_q .
										' LIMIT ' . $limit_q);
	
		if($results) {
			$cnt = count($results);
			for($i = 0; $i < $cnt; $i++) {
				$results[$i]->blog = stripslashes($results[$i]->blog);
				$results[$i]->title = apply_filters('the_title', stripslashes($results[$i]->title));
				$results[$i]->url = stripslashes($results[$i]->url);
				if($results[$i]->text) {
					if ($filter) $results[$i]->text = apply_filters('the_content', stripslashes($results[$i]->text));
					else $results[$i]->text = stripslashes($results[$i]->text);
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
	function miniblog_list_entries($before = '<li>', $between = '<br />', $after = '</li>', $identifier = '', $limit = 5, $offset = 0, $sortby = '_date', $filter = TRUE) {
		$entries = array();
		$entry = '';
		$entries = miniblog_return_entries($limit, $offset, $identifier, $sortby, $filter);
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
				echo $between;
				echo str_replace("\n" , '', $entry->text);
			}
			echo $after . "\n";
		}
	}
}

/* Creates the RSS feed URL to use in the href="" tag */
if(!function_exists('miniblog_create_rss_url')) {
	function miniblog_create_rss_url($limit = 5, $offset = 0, $identifier = '', $sortby = '_date', $title = '%site_name%', $description = '%site_description%') {
		/* Get the blog URL */
		ob_start();
		bloginfo('url');
		$url = ob_get_clean();
		
		/* Build new URL */
		$url = $url . '/wp-content/plugins/' . basename(__FILE__);
		$url = $url . '?action=rss&amp;n=' . $limit . '&amp;o=' . $offset;
		$url = $url . '&amp;q=' . htmlentities(urlencode($identifier)) . '&amp;s=' . htmlentities(urlencode($sortby));
		$url = $url . '&amp;t=' . htmlentities(urlencode($title)) . '&amp;d=' . htmlentities(urlencode($description));
		
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
				global $wpdb, $table_prefix;
				if(!$post_id) $post_id = '-';
				if(!$post_blog) $post_blog = 'default';
				/* Number of posts to list per page (editing pagination) */
				$per_page = 50; ?>
				<div class="wrap" style="text-align: center">
					<a href="post.php?page=<?php echo basename(__FILE__); ?>">Write new post</a> |
					<a href="#list">Edit posts</a> |
					<a href="#docs">Documentation and Help</a> |
					<a href="http://mediumbagel.org/?page_id=16">Check for Updates</a> |
					<a href="#uninstall">Uninstall</a>
				</div>
				
				<div class="wrap"><a name="post"></a>
					<h2><?php _e('Create/Edit') ?></h2>
					<form name="post" id="post" action="post.php?page=<?php echo basename(__FILE__); ?>" method="post">
						<script type="text/javascript">
							function focusit() {
								// focus on first input field
								document.post.name.focus();
							}
							window.onload = focusit;
						</script>
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
						<fieldset style="clear: both;">
							<legend><?php _e('Comment') ?></legend>
							<?php the_quicktags(); ?>
							<?php
								$rows = get_settings('default_post_edit_rows');
								if (($rows < 3) || ($rows > 100)) {
									$rows = 10;
								}
							?>
							<?php /* Give Miniblog to ability to cooperate with Mudbomb's editor:
								http://mudbomb.com/archives/2005/02/02/wysiwyg-plugin-for-wordpress*/
								if(function_exists('wysiwordpress')) { ?>
									<script language="javascript" type="text/javascript" src="../wp-content/plugins/Wysi-Wordpress/tiny_mce.js"></script>
									<script language="javascript" type="text/javascript" src="../wp-content/plugins/Wysi-Wordpress/wordpress.js"></script>
								<?php } ?>
							<div><textarea rows="<?php echo $rows; ?>" cols="40" name="content" tabindex="4" id="content" style="width: 99%"><?php echo $post_text ?></textarea></div>
							<script type="text/javascript">
								<!--
								edCanvas = document.getElementById('content');
								//-->
							</script>
						</fieldset>
						<p class="submit">
							<?php /* Give Miniblog to ability to cooperate with the Coldforged's Spelling Checker
							http://www.coldforged.org/spelling-checker-plugin-for-wordpress/
							*/
							if(function_exists('spell_insert_comment_button')) { ?>
								<input <?php if($button_class!='') echo 'class="' . $button_class . '" ';?> type="button" <?php if($tab_index!='') echo 'tabindex="' . $tab_index .'" ';?> value="Check Spelling" onClick="openSpellChecker();" />
							<?php } ?>
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
								$page = $_GET['p'];
								$page_url = '&amp;p=' . $page;
								$page_first_num = ($page-1)*$per_page;
								if(!$page_first_num) $page_first_num = '0';
								$page_query = $page_first_num . ',' . ($page+$per_page);
							} else {
								$page = 1;
								$page_url = '&amp;p=' . $page;
								$page_query = '0,' . ($page+$per_page);
							}
							$posts = $wpdb->get_results('SELECT id,blog,title,url FROM ' . $table_prefix . 'miniblog ORDER BY `date` DESC LIMIT ' . $page_query);
							if($posts) {
								$_alt = ' class="alternate"';
								foreach($posts as $post) {
									?>
									<tr<?php _e($_alt); ?>>
										<td><?php _e($post->id); ?></td>
										<td><?php _e(stripslashes($post->blog)); ?></td>
										<td><?php _e(stripslashes($post->title)); ?></td>
										<td><?php _e(stripslashes($post->url)); ?></td>
										<td><a href="post.php?page=<?php echo basename(__FILE__); ?>&amp;action=editminiblogpost&amp;id=<?php _e($post->id . $page_url) ?>#post"><?php _e('Edit'); ?></a></td>
										<td><a href="post.php?page=<?php echo basename(__FILE__); ?>&amp;action=deleteminiblogpost&amp;id=<?php _e($post->id . $page_url) ?>#list" class="delete" onclick="return confirm('Are you sure you want to delete this post?')"> <?php _e('Delete'); ?> </a></td>
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
								$prev_page = $page - 1;
								$prev_page_url = '&amp;p=' . $prev_page;
								$next_page = $page + 1;
								$next_page_num = (($next_page-1)*$per_page);
								if(!$next_page_num) $next_page_num = '0';
								$next_page_query = $next_page_num . ',' . ($next_page+$per_page);
							} else {
								$next_page = 2;
								$next_page_query = $per_page . ',' . ($next_page+$per_page);
							}
							/* Find out if there are more entries on the next page */
							$are_more = $wpdb->get_results('SELECT id FROM ' . $table_prefix . 'miniblog LIMIT ' . $next_page_query);
							if(count($are_more)) {
								if(is_numeric($are_more[0]->id)) $next_page_url = '&amp;p=' . $next_page;
							}
						?>
						<p style="text-align: right">
							<?php if($prev_page_url) { ?><a href="post.php?page=<?php echo basename(__FILE__); ?><?php _e($prev_page_url); ?>#list">&laquo; Previous</a> <?php } ?>
							<?php if($next_page_url) { ?><a href="post.php?page=<?php echo basename(__FILE__); ?><?php _e($next_page_url); ?>#list">Next &raquo;</a> <?php } ?>
						</p>
					</table>
				</div>
				
				<?php /* Documentation */ ?>
				<style type="text/css">
					p.code {
						border: 1px solid #CCC;
						background: #EEE;
						padding: 10px;
					}
				</style>
				<div class="wrap">
					<a name="docs"></a><h2><?php _e('Documentation'); ?></h2>
					<p>Welcome to Miniblog! Make sure you have the latest version, or it may not work with
					your version of WordPress. The latest version can be found at the following URL: <a href="http://mediumbagel.org/?page_id=16">http://mediumbagel.org/?page_id=16</a>. This plugin was orignally 
					written by <a href="http://www.nmyworld.com/">Ryan Poe</a>. Development and support was taken over by <a href="http://mediumbagel.org">Thomas Cort</a> in July 2005.</p>
					<h3><?php _e('What is it?'); ?></h3>
					<p>Miniblog is a plugin for Wordpress 1.5+ (and maybe lower). It is:
						<ul>
							<li>Light-weight. Only one table is needed for each WordPress install on which
								you wish to install this plugin. This also makes uninstalling it as simple
								as installing it. The entire plugin consists of only one file, as well, making
								is easy to install.</li>
							<li>Easy to customize. Using the custom function to retrieve data, one can go for
								full customization, using foreach loops and advanced PHP to output the entries.</li>
							<li>Easy to use. All one has to do is click the Activate button after dropping
								the file into the plugins directory, navigate to the Write menu, click the
								Miniblog submenu and start blogging!</li>
						</ul>
					</p>
					<h2><?php _e('Posting'); ?></h2>
					<p>Help on adding items to, editing, or deleting items from the database.
					<a name="titlefield"></a><h3><?php _e('Title'); ?></h3>
					<p>The Title is simply the title of the item you wish to post.</p>
					<a name="urlfield"></a><h3><?php _e('URL'); ?></h3>
					<p>This field is designated as a URL for link blogging, but it can be used for any purpose, as
					it is not required to input a hyperlink.</p>
					<a name="blogidfield"></a><h3><?php _e('Blog Identifier'); ?></h3>
					<p>This field may not seem self explanatory at first, but it's really just a way to organize
					one's postings. The default entry is "default," so if one never changed the field, all posts
					would fall under this one category.</p>
					<p>An example use of this is to separate your link blog from your asides. For every link blog
					entry made, one would change this field to "link." For every aside entry made, one would change
					this field to "aside." This is done so that they can later be called separately in templates.</p>
					<a name="timestampfield"></a><h3><?php _e('Time Stamp'); ?></h3>
					<p>This field doesn't appear the first time you publish an entry. To get it, you must save the
					entry and edit it via the list. All it does is allow you to change the date or time of the post.
					The format is Year-Month-Day Hour:Minute:Second (military time).</p>
					<a name="timestampfield"></a><h3><?php _e('Post'); ?></h3>
					<p>This field is the content of the entry. It is passed through the_content's filters.</p>
					<h2><?php _e('Usage in Templates'); ?></h2>
					<p>This section demonstrates how one can use the entries saved in their templates.</p>
					<h3><?php _e('miniblog_list_entries(...)'); ?></h3>
					<p>This function is a beginner friendly version of miniblog_return_entries(). It has default
					formatting that is changeable and tries to keep in cahoots with other WordPress list functions.</p>
					<p>The function is designed to be smart. If you have no title or url, it displays the date. If you
					have a title and no URL, it displays the title with no link. If you have a URL and no title, it uses
					the date as the href title. If you have no entry text, it skips the between parameter and displays
					nothing as a description.</p>
					<p><strong>Parameters</strong>:
						<ol>
							<li><strong>Before</strong>: text to display before every entry's title link. Default is '&lt;li&gt;'.</li>
							<li><strong>Between</strong>: text to display between every entry's title link and text. Default is '&lt;br /&gt;'.</li>
							<li><strong>After</strong>: text to display after every entry's text. Default is '&lt;/li&gt;'.</li>
							<li><strong>Blog Identifier</strong>: this determines which entries to call specificied
								by an entry's "Blog Identifier" field. This can be any text string. To retrieve all
								field, leave blank (''). Default is blank ('').</li>
							<li><strong>Limit</strong>: the number of entries to return. Default is 10.</li>
							<li><strong>Offset</strong>: the number of entries to skip before returning. For instance,
								a limit of 5 with an offset of 5 will return entries 6 through 10. Default is 0.</li>
							<li><strong>Sort Field</strong>: this is the field that determines the order in which
								entries are returned. Prepending an underscore (_) to the parameter sorts the entries
								descending (latest first) as opposed to ascending (earliest first).
								<p>Options:
									<ul>
										<li>ID - an entry's ID (unique).</li>
										<li>Date - the date the entry was posted.</li>
										<li>Blog - the entry's blog identifier field.</li>
										<li>Title - the entry's title.</li>
										<li>URL - the entry's URL.</li>
										<li>Text - the entry's text.</li>
									</ul>
									Default is '_date.'
								</p>
							</li>
							<li><strong>Filter Text</strong>: this boolean option determines rather or not to filter the entry's
								text through the_content's filters. This is useful to set to false if you want to use ad plugins
								that apply a filter to the_content to show relevant ads. Default is FALSE.
							</li>
						</ol>
					</p>
					<p>Examples:<br />
						<p class="code">
							<code>
								<span style="color:#000000">
								&lt;h2&gt;Asides&lt;/h2&gt;
								
								<br />&lt;ul&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for miniblog_list_entries" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=miniblog_list_entries">miniblog_list_entries</a></span><span style="color:#007700">(); </span><span style="color:#0000BB">?&gt;
								<br /></span>&lt;/ul&gt;</span>
							</code>
							<p>This will output the latest 5 entries in a simple list that makes up of only
							the title and the text separated with a line break.</p>
						</p>
						<p class="code">
							<code>
								</span><span style="color:#007700">&lt;</span><span style="color:#0000BB">h2</span><span style="color:#007700">&gt;</span><span style="color:#0000BB">Asides</span><span style="color:#007700">&lt;/</span><span style="color:#0000BB">h2</span><span style="color:#007700">&gt;
								<br />&lt;</span><span style="color:#0000BB">ul</span><span style="color:#007700">&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color:#0000BB"><a class="code" title="View manual page for miniblog_list_entries" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=miniblog_list_entries">miniblog_list_entries</a></span><span style="color:#007700">(</span><span style="color:#DD0000">'&lt;li&gt;&lt;strong&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;/strong&gt;&lt;blockquote&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;/blockquote&gt;&lt;/li&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'aside'</span><span style="color:#007700">, </span><span style="color:#0000BB">10</span><span style="color:#007700">);
								
								<br />&lt;/</span><span style="color:#0000BB">ul</span><span style="color:#007700">&gt;</span>
								</span>
							</code>
							<p>This will output the latest 10 entries with the blog identifier of 'aside' and display
							them in a list that will resemble this:
								<ul>
									<li><strong><a href="http://www.techspot.com/story17224.html">Yahoo! Mail Gets a Gig</a></strong><blockquote>Yahoo! Mail, likely in an attempt to compete with G-Mail, will increased their storage capacity to 1gb.</blockquote></li>
								</ul>
							</p>
						</p>
					</p>
					<h3><?php _e('miniblog_return_entries(...)'); ?></h3>
					<p>This function returns a set of entries into an array of arrays containing entry data.</p>
					<p><strong>Parameters</strong>:
						<ol>
							<li><strong>Limit</strong>: the number of entries to return. Default is 10.</li>
							<li><strong>Offset</strong>: the number of entries to skip before returning. For instance,
								a limit of 5 with an offset of 5 will return entries 6 through 10. Default is 0.</li>
							<li><strong>Blog Identifier</strong>: this determines which entries to call specificied
								by an entry's "Blog Identifier" field. This can be any text string. To retrieve all
								field, leave blank (''). Default is blank ('').</li>
							<li><strong>Sort Field</strong>: this is the field that determines the order in which
								entries are returned. Prepending an underscore (_) to the parameter sorts the entries
								descending (latest first) as opposed to ascending (earliest first).
								<p>Options:
									<ul>
										<li>ID - an entry's ID (unique).</li>
										<li>Date - the date the entry was posted.</li>
										<li>Blog - the entry's blog identifier field.</li>
										<li>Title - the entry's title.</li>
										<li>URL - the entry's URL.</li>
										<li>Text - the entry's text.</li>
									</ul>
									Default is '_date.'
								</p>
							</li>
								<li><strong>Filter Text</strong>: this boolean option determines rather or not to filter the entry's
								text through the_content's filters. This is useful to set to false if you want to use ad plugins
								that apply a filter to the_content to show relevant ads. Default is FALSE.
							</li>
						</ol>
					</p>
					<p>The array for each entry contains an object that can be accessed by calling the following methods.
						<ul>
							<li>$var-&gt;id - the entry's id.</li>
							<li>$var-&gt;date - the entry's date.</li>
							<li>$var-&gt;blog - the entry's blog identifier.</li>
							<li>$var-&gt;title - the entry's title.</li>
							<li>$var-&gt;url - the entry's url.</li>
							<li>$var-&gt;text - the entry's text.</li>
						</ul>
					</p>
					<p>Examples:<br />
						<p class="code">
							<code>
							<span style="color: #0000BB">&lt;?php
							<br />&nbsp;&nbsp;&nbsp;&nbsp;$asides </span><span style="color: #007700">= </span><span style="color: #0000BB">miniblog_return_entries</span><span style="color: #007700">(</span><span style="color: #0000BB">5</span><span style="color: #007700">, </span><span style="color: #0000BB">0</span><span style="color: #007700">, </span><span style="color: #DD0000">''</span><span style="color: #007700">, </span><span style="color: #DD0000">'Title'</span><span style="color: #007700">);
							
							<br />&nbsp;&nbsp;&nbsp;&nbsp;foreach(</span><span style="color: #0000BB">$asides </span><span style="color: #007700">as </span><span style="color: #0000BB">$aside</span><span style="color: #007700">) { </span><span style="color: #0000BB">?&gt;
							<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$aside</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">title</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;</span>&lt;br /&gt;<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$aside</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">text</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;</span>&lt;/li&gt;
							
							<br /><span style="color: #0000BB">&lt;?php </span><span style="color: #007700">} </span><span style="color: #0000BB">?&gt;</span>
							</span>
							</code>
							<p>This will output the latest 5 entries in a simple list that makes up of only
							the title and the text separated with a line break.</p>
						</p>
						<p class="code">
							<code>
								<span style="color: #0000BB">&lt;?php
								<br />&nbsp;&nbsp;&nbsp;&nbsp;$news_array </span><span style="color: #007700">= </span><span style="color: #0000BB">miniblog_return_entries</span><span style="color: #007700">(</span><span style="color: #0000BB">15</span><span style="color: #007700">, </span><span style="color: #0000BB">0</span><span style="color: #007700">, </span><span style="color: #DD0000">'news'</span><span style="color: #007700">, </span><span style="color: #DD0000">'_date'</span><span style="color: #007700">);
								<br />&nbsp;&nbsp;&nbsp;&nbsp;if(</span><span style="color: #0000BB">$news_array</span><span style="color: #007700">) {
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;foreach(</span><span style="color: #0000BB">$news_array </span><span style="color: #007700">as </span><span style="color: #0000BB">$news</span><span style="color: #007700">) { </span><span style="color: #0000BB">?&gt;
								
								<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class="post"&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;h2&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a href="<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$news</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">url</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;</span>"&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$news</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">title</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;
								
								<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/a&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/h2&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class="meta"&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Posted on <span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB"><a class="code" title="View manual page for date" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=date">date</a></span><span style="color: #007700">(</span><span style="color: #DD0000">"F j, Y, g:i a"</span><span style="color: #007700">, </span><span style="color: #0000BB"><a class="code" title="View manual page for strtotime" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=strtotime">strtotime</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$news</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">date</span><span style="color: #007700">))); </span><span style="color: #0000BB">?&gt;
								
								<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class="content"&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$news</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">text</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;
								<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;
								
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">&lt;? </span><span style="color: #007700">}
								<br />&nbsp;&nbsp;&nbsp;&nbsp;}
								<br /></span><span style="color: #0000BB">?&gt;</span>
							</code>
							<p>This will output the latest 15 entries with the string 'news' in their
							blog identifier field and outputs them in a format much like Wordpress's
							default output style.</p>
						</p>
					</p>
					<h3><?php _e('miniblog_create_rss_url(...)'); ?></h3>
					<p>
					This function returns a URL to an RSS 2.0 feed specified by the parameters 
					entered. Miniblog also supports RSS 0.92. To link to an RSS 0.92 feed you 
					must append "&amp;v=0.92" to the result from miniblog_create_rss_url(...).
					The parameters are exactly the same as the parameters of miniblog_return_entries() with two note-worthy exceptions:
						<ol>
							<li><strong>Limit</strong>...</li>
							<li><strong>Offset</strong>: ...</li>
							<li><strong>Blog Identifier</strong>: ...</li>
							<li><strong>Sort Field</strong>: ...</li>
							<li><strong>RSS Feed Title</strong>: this field determines the title of the RSS feed. Default is %site_name%</li>
							<li><strong>RSS Feed Description</strong>: this field determines the description of the RSS feed. Default is %site_description%.</li>
						</ol>
						<p>
							The two new fields can have the following template tags within them:
							<ul>
								<li>%site_name% - the name of your blog specified in your options menu.</li>
								<li>%site_description% - your blogs description specified in your options menu.</li>
							</ul>
						</p>
					</p>
					<p>Examples: <br />
						<p class="code">
							<code>
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB"><a class="code" title="View manual page for miniblog_create_rss_url" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=miniblog_create_rss_url">miniblog_create_rss_url</a></span><span style="color:#007700">()); </span><span style="color:#0000BB">?&gt;</span>"&gt;Miniblog RSS 2.0&lt;/a&gt;</span><br />
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB"><a class="code" title="View manual page for miniblog_create_rss_url"  href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=miniblog_create_rss_url">miniblog_create_rss_url</a></span><span style="color:#007700">()); </span><span style="color:#0000BB">?&gt;</span>&amp;amp;v=0.92"&gt;Miniblog RSS 0.92&lt;/a&gt;</span>
							</code>
							<p>This example will simply display the latest 5 miniblog entries that you've posted
							with your site's title and description as the feed's description.</p>
						</p>
						<p class="code">
							<code>
								&lt;h2&gt;Asides&lt;/h2&gt;
								<br />&lt;ul&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">&lt;?php
								<br />&nbsp;&nbsp;&nbsp;&nbsp;$asides </span><span style="color: #007700">= </span><span style="color: #0000BB">miniblog_return_entries</span><span style="color: #007700">(</span><span style="color: #0000BB">5</span><span style="color: #007700">, </span><span style="color: #0000BB">0</span><span style="color: #007700">, </span><span style="color: #DD0000">'aside'</span><span style="color: #007700">, </span><span style="color: #DD0000">'_Date'</span><span style="color: #007700">);
								
								<br />&nbsp;&nbsp;&nbsp;&nbsp;foreach(</span><span style="color: #0000BB">$asides </span><span style="color: #007700">as </span><span style="color: #0000BB">$aside</span><span style="color: #007700">) { </span><span style="color: #0000BB">?&gt;
								<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span&gt;&lt;a href="<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$aside</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">url</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;</span>"&gt;<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$aside</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">title</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;</span>&lt;/a&gt;&lt;/span&gt;
								
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$aside</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">text</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;
								<br /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">&lt;?php </span><span style="color: #007700">} </span><span style="color: #0000BB">?&gt;
								<br /></span>&lt;/ul&gt;
								
								<br /><span style="color: #0000BB">&lt;?php $feed_url </span><span style="color: #007700">= </span><span style="color: #0000BB">miniblog_create_rss_url</span><span style="color: #007700">(</span><span style="color: #0000BB">5</span><span style="color: #007700">, </span><span style="color: #0000BB">0</span><span style="color: #007700">, </span><span style="color: #DD0000">'aside'</span><span style="color: #007700">, </span><span style="color: #DD0000">'_Date'</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;
								<br /></span>&lt;p id="asidefeed"&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;a href="<span style="color: #0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color: #007700">(</span><span style="color: #0000BB">$feed_url</span><span style="color: #007700">); </span><span style="color: #0000BB">?&gt;</span>"&gt;rss&lt;/a&gt;
								
								<br />&lt;/p&gt;</span>
							</code>
							<p>This example is what I use on my index page to show asides. Notice at the bottom how I use the
							miniblog_create_rss_url().</p>
						</p>
						<p class="code">
							<code>
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB"><a class="code" title="View manual page for miniblog_create_rss_url" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=miniblog_create_rss_url">miniblog_create_rss_url</a></span><span style="color:#007700">(</span><span style="color:#0000BB">5</span><span style="color:#007700">, </span><span style="color:#0000BB">0</span><span style="color:#007700">, </span><span style="color:#DD0000">'aside'</span><span style="color:#007700">, </span><span style="color:#DD0000">'_date'</span><span style="color:#007700">, </span><span style="color:#DD0000">"%site_name%'s Asides"</span><span style="color:#007700">, </span><span style="color:#DD0000">"%site_name% (%site_description%) has asides. These are them."</span><span style="color:#007700">)); </span><span style="color:#0000BB">?&gt;</span>"&gt;Miniblog RSS&lt;/a&gt;</span>
							</code>
							<p>This example will output an RSS feed link with the title of "nmyworld's asides" and a description
							of "nmyworld (Just another WordPress blog) has asides. These are them." Your blog will show different
							results (because your blog has different settings).</p>
						</p>
					</p>
				</div>
				
				<div class="wrap">
					<a name="uninstall"></a><h2><?php _e('Uninstall'); ?></h2>
					<p>To remove the table from the database, do it manually or enter the string "Remove Thyself" (case sensitive) in the box
					below and press submit.</p>
					<p>
						<form name="miniblog" id="miniblog" action="post.php?page=<?php echo basename(__FILE__); ?>" method="post">
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
				global $wpdb, $table_prefix;

				$wpdb->query('CREATE TABLE IF NOT EXISTS `' . $table_prefix . 'miniblog` (
						`id` bigint(20) unsigned NOT NULL auto_increment,
						`date` datetime NOT NULL default \'0000-00-00 00:00:00\',
						`blog` text NOT NULL,
						`title` text NOT NULL,
						`url` text NOT NULL,
						`text` text NOT NULL,
						FULLTEXT (`blog`),
						PRIMARY KEY  (`id`))');

				/* If the user requested to uninstall */
				if($_POST['delstring'] == 'Remove Thyself') {
					$wpdb->query('DROP TABLE `' . $table_prefix . 'miniblog`'); ?>
					<div class="updated"><p><strong>
						The Miniblog table has been deleted from the MySQL database. To finish
						uninstalling the plugin, deactivate it from the "Plugins" menu and delete
						the file from the plugins directory.
					</strong></p></div>
					<?php miniblog_cooperate();
				/* If the user wants to edit a post */
				} elseif ($_GET['action'] == 'editminiblogpost') {
					$id = $_GET['id'];
					if (!is_numeric($_GET['id'])) $id = 0;
					$entry = '';
					$entry = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'miniblog WHERE id=' . $id . ' LIMIT 1');
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
						?> <div class="updated"><p>Post not found.</p></div>
						<?php miniblog_render_plugin_page();
					}
				/* If the user wants to save a post */
				} elseif ($_POST['action'] == 'saveminiblogpost') {
					/* If there's an ID in the data, it's to save (not to add) */
					if($_POST['id'] && is_numeric($_POST['id'])) {
						if(!$wpdb->query('UPDATE ' . $table_prefix . 'miniblog SET
								date="' . mysql_escape_string($_POST['date']) .'",
								blog="' . mysql_escape_string($_POST['blog']) .'",
								title="' . mysql_escape_string($_POST['title']) .'",
								url="' . mysql_escape_string($_POST['url']) .'",
								text="' . mysql_escape_string($_POST['content']) . '"
								WHERE id=' . $_POST['id'])) {
							?> <div class="updated"><p>Unable to update entry.</p></div>
							<?php miniblog_render_plugin_page();
						} else {
							?> <div class="updated"><p>Entry successfully updated</p></div>
							<?php miniblog_render_plugin_page();
							miniblog_cooperate();
						}
					/* There's no ID specified, so it's to add a new entry */
					} else {
						if(!$wpdb->query('INSERT INTO ' . $table_prefix . 'miniblog SET
									date=NOW(),
									blog="' . mysql_escape_string($_POST['blog']) .'",
									title="' . mysql_escape_string($_POST['title']) .'",
									url="' . mysql_escape_string($_POST['url']) .'",
									text="' . mysql_escape_string($_POST['content']) . '"')) {
							?> <div class="updated"><p>Unable to add entry.</p></div>
							<?php miniblog_render_plugin_page();
						} else {
							?> <div class="updated"><p>Entry successfully added.</p></div>
							<?php miniblog_render_plugin_page();
							miniblog_cooperate();
						}
					}
				/* If the user wants to delete an entry */
				} elseif ($_GET['action'] == 'deleteminiblogpost') {
					if(!$wpdb->query('DELETE FROM ' . $table_prefix . 'miniblog WHERE id=' . $_GET['id'])) {
						?> <div class="updated"><p>Unable to delete entry.</p></div>
						<?php miniblog_render_plugin_page();
					} else {
						?> <div class="updated"><p>Entry successfully deleted.</p></div>
						<?php miniblog_render_plugin_page();
						miniblog_cooperate();
					}
				/* No actions */
				} else {
					miniblog_render_plugin_page();
				}
			}
		}
	/* This isn't a plugin page, but WordPress is still calling this plugin (to be used in normal pages) */
	} else {
		/* nothing to see here. Move along */
	}
/* This isn't a plugin page and it's not being called from WordPress */
} else {
	if(isset($_GET['action'])) {
		/* Just to make sure...again... */
		if (get_magic_quotes_gpc()) {
			foreach($_GET as $k => $v)
				$_GET[$k] = stripslashes($v);
			foreach($_POST as $k => $v)
				$_POST[$k] = stripslashes($v);
		}
		/* Call up the WordPress configuration stuff */
		$wordpress_dir = dirname(dirname(dirname(__FILE__)));
		if (!file_exists($wordpress_dir . '/wp-config.php')) {
			die('Can\'t load wp-config.php (located in "' .
			$wordpress_dir . '"). You must put this plugin in
			your plugins directory ("/wordpress/wp-content/plugins/").');
		}
		require_once($wordpress_dir . '/wp-config.php');
		/* RSS display */
		if($_GET['action'] == 'rss') {
			/* Get the blog name and description (stupid stupid WordPress making me do this) */
			ob_start();
			bloginfo('name');
			$blog_name = ob_get_contents();
			ob_end_clean();
			ob_start();
			bloginfo('description');
			$blog_description = ob_get_contents();
			ob_end_clean();
			
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

		}
	}
}

/* Add the menu item */
if(!function_exists('miniblog_menu')) {
	function miniblog_menu () {
		add_submenu_page('post.php', 'Miniblog', 'Miniblog', 9, basename(__FILE__), 'miniblog');
	}
}
if(function_exists('add_action')) add_action('admin_menu', 'miniblog_menu');

?>

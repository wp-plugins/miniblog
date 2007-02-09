				<?php /* Documentation */ ?>

				<div class="wrap">
					<a name="docs"></a><h2><?php _e('Documentation'); ?></h2>
					<p>Welcome to Miniblog! Make sure you have the latest version, or it may not work with
					your version of WordPress. The latest version can be found at the following URL: <a href="http://blog.fileville.net/?page_id=121">http://blog.fileville.net/?page_id=121</a>. This plugin was orignally 
					written by <a href="http://www.nmyworld.com/">Ryan Poe</a>. Development and support was taken over by <a href="http://mediumbagel.org">Thomas Cort</a> in July 2005 and now is being developed by <a href="http://blog.fileville.net">Joe</a>.</p>
					<h3><?php _e('What is it?'); ?></h3>
					<p>Miniblog is a plugin for Wordpress 1.5 and 2.0. It is:
				  <ul>
							<li>Light-weight. Only one database table is needed for each WordPress install on which
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
					
					<h2>
					  <?php _e('Posting'); ?>
					</h2>
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
					<a name="timestampfield"></a><h3><?php _e('Read on'); ?></h3>
					<p>Miniblog allows you to display part of a post with a "read on" link that links to the rest of
					the post. To use this feature you must do two things. First, when you use functions like 
					miniblog_list_entries(...) or miniblog_return_entries(...) you must set the <b>full</b> parameter 
					to 0. This enables the "read on" feature. Second, just put "[readon]" (without quotes) in the text
					of your post where you want the "read on" link to appear.</p>
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
							<li><strong>Between</strong>: text to display between every entry's title link and text. Default is '&lt;br /&gt;'. To make the entry text appear on the same line as the title, set this parameter to '&lt;none&gt;'.</li>
							<li><strong>After</strong>: text to display after every entry's text. Default is '&lt;/li&gt;'.</li>
							<li><strong>Blog Identifier</strong>: this determines which entries to call specified
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
							<li><strong>Full</strong>: this option determines if the full text of posts should be displayed 
							regardless of [readon] tags, or if posts should be truncated at the [readon] tag. Default is 1 for use 
							full text (ie ignore [readon] tags in posts). Set it to 0 to enable the "read on" feature.
							</li>
				  </ol>
					</p>
					<p>Examples:<br />
				  <p class="code">
					<code>
					<span style="color:#000000">
								&lt;h2&gt;Asides&lt;/h2&gt;
								
								<br />&lt;ul&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#0000BB">&lt;?php miniblog_list_entries</span><span style="color:#007700">(); </span><span style="color:#0000BB">?&gt;
								<br /></span>&lt;/ul&gt;</span>
					</code>
				  <p>This will output the latest 5 entries in a simple list that makes up of only
							the title and the text separated with a line break.</p>
						</p>
						<p class="code">
							<code>
								</span><span style="color:#007700">&lt;</span><span style="color:#0000BB">h2</span><span style="color:#007700">&gt;</span><span style="color:#0000BB">Asides</span><span style="color:#007700">&lt;/</span><span style="color:#0000BB">h2</span><span style="color:#007700">&gt;
								<br />&lt;</span><span style="color:#0000BB">ul</span><span style="color:#007700">&gt;
								<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color:#0000BB">&lt;?php miniblog_list_entries</span><span style="color:#007700">(</span><span style="color:#DD0000">'&lt;li&gt;&lt;strong&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;/strong&gt;&lt;blockquote&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;/blockquote&gt;&lt;/li&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'aside'</span><span style="color:#007700">, </span><span style="color:#0000BB">10</span><span style="color:#007700">);</span> <span style="color:#0000BB">?&gt;</span><span style="color:#007700">
								
								<br />
								&lt;/</span><span style="color:#0000BB">ul</span><span style="color:#007700">&gt;</span>
								</span>
							</code>
				  <p>This will output the latest 10 entries with the blog identifier of 'aside' and display
							them in a list that will resemble this:
				  <ul>
									<li><strong><a href="http://www.techspot.com/story17224.html">Yahoo! Mail Gets a Gig</a></strong>
									<blockquote>Yahoo! Mail, likely in an attempt to compete with GMail, will increased their storage capacity to 1gb.</blockquote>
									</li>
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
							<li><strong>Full</strong>: this option determines if the full text of posts should be displayed 
								regardless of [readon] tags, or if posts should be truncated at the [readon] tag. Default is 1 for use 
								full text (ie ignore [readon] tags in posts). Set it to 0 to enable the "read on" feature.
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
					entered. Miniblog also supports RSS 0.92. To return a URL to an RSS 0.92 
					feed you must specify a version parameter of 0.92. 
					The parameters for miniblog_create_rss_url() are exactly the same as the 
					parameters of miniblog_return_entries() with a few note-worthy exceptions:
				  <ol>
							<li><strong>Limit</strong>: the number of entries to return. Default is 10.</li>
							<li><strong>Offset</strong>:  the number of entries to skip before returning. For instance, a limit of 5 with an offset of 5 will return entries 6 through 10. Default is 0.</li>
							<li><strong>Blog Identifier</strong>: this determines which entries to call specificied by an entry's "Blog Identifier" field. This can be any text string. To retrieve all field, leave blank (''). Default is blank ('').</li>
							<li><strong>Sort Field</strong>: this is the field that determines the order in which entries are returned. Prepending an underscore (_) to the parameter sorts the entries descending (latest first) as opposed to ascending (earliest first).</li>
							<li><strong>RSS Feed Title</strong>: this field determines the title of the RSS feed. Default is %site_name%.</li>
							<li><strong>RSS Feed Description</strong>: this field determines the description of the RSS feed. Default is %site_description%.</li>
							<li><strong>Version</strong>: this field determines which RSS version to use. 2.0 and 0.92 are supported. Default is 2.0.</li>
				  </ol>
						<p>
							Fields 5 and 6 can have the following template tags within them:
				  <ul>
								<li>%site_name% - the name of your blog specified in your options menu.</li>
								<li>%site_description% - your blogs description specified in your options menu.</li>
				  </ul>
						</p>
					</p>
					<p>Examples: <br />
				  <p class="code">
							<code>
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB">miniblog_create_rss_url</span><span style="color:#007700">()); </span><span style="color:#0000BB">?&gt;</span>"&gt;Miniblog RSS 2.0&lt;/a&gt;</span><br />
					</code>
				  </p>
						<p class="code">
							<code>
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB">miniblog_create_rss_url</span><span style="color:#007700">(</span><span style="color:#0000BB">5</span><span style="color:#007700">, </span><span style="color:#0000BB">0</span><span style="color:#007700">, </span><span style="color:#DD0000">'aside'</span><span style="color:#007700">, </span><span style="color:#DD0000">'_date'</span><span style="color:#007700">, </span><span style="color:#DD0000">"%site_name%'s Asides"</span><span style="color:#007700">, </span><span style="color:#DD0000">"%site_name% (%site_description%) has asides. These are them."</span><span style="color:#007700">, </span><span style="color:#0000BB">0.92</span><span style="color:#007700">)); </span><span style="color:#0000BB">?&gt;</span>"&gt;Miniblog RSS 0.92&lt;/a&gt;</span>							</code>
				  <p>This example will output an RSS feed link with the title 
							of "Mosltynothings's asides" and a description
							of &quot;FileVille Blog&quot; (Just another WordPress blog) has asides. These 
							are them." The RSS feed will be RSS v0.92 compliant. Your blog will show different
							results (because your blog has different settings).</p>
						</p>
					</p>
					<h3><?php _e('miniblog_create_archive_url(...)'); ?></h3>
					<p>
					This function returns a URL to an archive of posts specified by the parameters entered. 
					The parameters for miniblog_create_archive_url() are similar to the 
					parameters of miniblog_create_rss_url() with a few note-worthy exceptions:
				  <ol>
							<li><strong>Limit</strong>: the number of entries to return. Default is 10.</li>
							<li><strong>Offset</strong>:  the number of entries to skip before returning. For instance, a limit of 5 with an offset of 5 will return entries 6 through 10. Default is 0.</li>
							<li><strong>Blog Identifier</strong>: this determines which entries to call specificied by an entry's "Blog Identifier" field. This can be any text string. To retrieve all field, leave blank (''). Default is blank ('').</li>
							<li><strong>Sort Field</strong>: this is the field that determines the order in which entries are returned. Prepending an underscore (_) to the parameter sorts the entries descending (latest first) as opposed to ascending (earliest first).</li>
							<li><strong>Title</strong>: this is the field that determines the title to use on the archive page. Default is Miniblog Archive.</li>
							<li><strong>Before</strong>: text to display before every entry's title link. Default is '&lt;li&gt;'.</li>
							<li><strong>Between</strong>: text to display between every entry's title link and text. Default is '&lt;br /&gt;'.</li>
							<li><strong>After</strong>: text to display after every entry's text. Default is '&lt;/li&gt;'.</li>
							<li><strong>Full</strong>: this option determines if the full text of posts should be displayed 
							regardless of [readon] tags, or if posts should be truncated at the [readon] tag. Default is 1 for use 
							full text (ie ignore [readon] tags in posts). Set it to 0 to enable the "read on" feature.
							</li>
				  </ol>
					</p>
					<p>Examples: <br />
				  <p class="code">
							<code>
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB">miniblog_create_archive_url</span><span style="color:#007700">()); </span><span style="color:#0000BB">?&gt;</span>"&gt;Miniblog Archive&lt;/a&gt;</span><br />
					</code>
				  </p>
						<p class="code">
							<code>
 								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB">miniblog_create_archive_url</span><span style="color:#007700">(</span><span style="color:#0000BB">5</span><span style="color:#007700">, </span><span style="color:#0000BB">0</span><span style="color:#007700">, </span><span style="color:#DD0000">'aside'</span><span style="color:#007700">, </span><span style="color:#DD0000">'_date'</span><span style="color:#007700">, </span><span style="color:#DD0000">'Asides Archive'</span><span style="color:#007700">)); </span><span style="color:#0000BB">?&gt;</span>"&gt;Asides Archive&lt;/a&gt;</span>							</code>						</p>
						<p class="code">
							<code>
 								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB">miniblog_create_archive_url</span><span style="color:#007700">(</span><span style="color:#0000BB">5</span><span style="color:#007700">, </span><span style="color:#0000BB">0</span><span style="color:#007700">, </span><span style="color:#DD0000">'aside'</span><span style="color:#007700">, </span><span style="color:#DD0000">'_date'</span><span style="color:#007700">, </span><span style="color:#DD0000">'Asides Archive'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;li&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;br /&gt;'</span><span style="color:#007700">, </span><span style="color:#DD0000">'&lt;/li&gt;'</span><span style="color:#007700">)); </span><span style="color:#0000BB">?&gt;</span>"&gt;Asides Archive&lt;/a&gt;</span>							</code>						</p>
					</p>
					<h3><?php _e('miniblog_create_post_url(...)'); ?></h3>
					<p>
					This function returns a URL to a single post specified by the postid parameter. See the "Post List" at the top of the page for a list of ID numbers.
				  <ol>
							<li><strong>ID</strong>: The post ID number.</li>
				  </ol>
					</p>
					<p>Example: <br />
				  <p class="code">
							<code>
								<span style="color:#000000">&lt;a href="<span style="color:#0000BB">&lt;?php <a class="code" title="View manual page for _e" href="http://www.php.net/manual-lookup.php?lang=en&amp;pattern=_e">_e</a></span><span style="color:#007700">(</span><span style="color:#0000BB">miniblog_create_post_url</span><span style="color:#007700">(22)); </span><span style="color:#0000BB">?&gt;</span>"&gt;My Post About Cheese&lt;/a&gt;</span><br />
					</code>
				  </p>
					</p>
				</div>
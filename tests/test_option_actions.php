<?

class SP_OptionActionsTest extends WPTestCase {
	
	function setUp() {
		parent::setUp();
		$this->option = rand_str();
	}
	
	function tearDown() {
		parent::tearDown();
		delete_option($this->option);
	}
	
	function test_option_update() {
		
		$expected = rand_str();
		SiteUpgradeOptionActions::option_update($this->option, $expected);
		$this->assertEquals($expected, get_option($this->option));
		
	}
	
	function test_options() {
	
		$expected = array('active_plugins','admin_email','advanced_edit','avatar_default',
			'avatar_rating','blacklist_keys','blogdescription','blogname','blog_charset',
			'blog_public','category_base','close_comments_days_old','close_comments_for_old_posts',
			'comments_notify','comments_per_page','comment_max_links','comment_moderation','comment_order',
			'comment_registration','comment_whitelist','date_format','db_version','default_category',
			'default_comments_page','default_comment_status','default_email_category','default_link_category',
			'default_pingback_flag','default_ping_status','default_post_edit_rows','default_role','embed_autourls',
			'embed_size_h','embed_size_w','enable_app','enable_xmlrpc','gmt_offset','gzipcompression','hack_file',
			'home','html_type','image_default_align','image_default_link_type','image_default_size','large_size_h',
			'large_size_w','links_recently_updated_append','links_recently_updated_prepend','links_recently_updated_time',
			'links_updated_date_format','mailserver_login','mailserver_pass','mailserver_port','mailserver_url','medium_size_h',
			'medium_size_w','moderation_keys','moderation_notify','page_comments','page_for_posts','page_on_front',
			'permalink_structure','ping_sites','posts_per_page','posts_per_rss','recently_edited','require_name_email',
			'rss_language','rss_use_excerpt','show_avatars','show_on_front','sidebars_widgets','siteurl','start_of_week',
			'sticky_posts','stylesheet','tag_base','template','thread_comments','thread_comments_depth','thumbnail_crop',
			'thumbnail_size_h','thumbnail_size_w','timezone_string','time_format','uploads_use_yearmonth_folders','upload_path',
			'upload_url_path','users_can_register','use_balanceTags','use_smilies','use_trackback','widget_archives',
			'widget_categories','widget_meta','widget_recent-comments','widget_recent-posts','widget_rss','widget_search',
			'widget_text','wp_user_roles');
		$options = SiteUpgradeOptionACtions::options();
		$this->assertEquals($expected, $options);
		
	}
	
}

?>

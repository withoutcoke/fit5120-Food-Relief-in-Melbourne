<?php
namespace MMP;

use MMP\Maps_Marker_Pro as MMP;

class Menu {
	/**
	 * Loads the resources that are needed on all admin pages
	 *
	 * @since 4.0
	 *
	 * @param string $hook Name of the current admin page
	 */
	public function load_global_resources($hook) {
		wp_enqueue_style('mmp-admin');
		if (is_rtl()) {
			wp_enqueue_style('mmp-admin-rtl');
		}

		ob_start();
		?>
		<p>
			<?= esc_html__('If you have any questions or experience issues, please use one of the following support channels.', 'mmp') ?><br/>
			<strong><?= sprintf(esc_html__('Before you post a new support ticket, please follow the instructions on %1$s for a guideline on how to deal with the most common issues.', 'mmp'), '<a href="https://www.mapsmarker.com/readme-first/" target="_blank">https://www.mapsmarker.com/readme-first/</a>') ?></strong>
		</p>
		<ul>
			<li><a href="https://www.mapsmarker.com/kb/" target="_blank"><?= esc_html__('Knowledge base', 'mmp') ?></a></li>
			<li><a href="https://www.mapsmarker.com/helpdesk/" target="_blank"><?= esc_html__('Helpdesk', 'mmp') ?></a></li>
		</ul>
		<?php
		$helptext = ob_get_clean();

		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'id'      => 'mmp-help-tab',
			'title'   => esc_html__('Help & Support', 'mmp'),
			'content' => $helptext
		));
	}

	/**
	 * Displays the admin page
	 *
	 * @since 4.0
	 */
	public function display() {
		$this->show();
		$this->footer();
	}

	/**
	 * Displays an error message
	 *
	 * @since 4.0
	 *
	 * @param string $message Message to be displayed
	 */
	public function error($message) {
		?><div class="notice notice-error"><p><?= $message ?></p></div><?php
	}

	/**
	 * Compares two values and returns the checked property for a checkbox or radio form field
	 *
	 * @since 4.0
	 *
	 * @param mixed $check Value to check
	 * @param mixed $value (optional) Value to check against
	 * @param bool $strict (optional) Whether to do a strict comparison
	 */
	public function checked($check, $value = true, $strict = false) {
		if ((!$strict && $check == $value) || ($strict && $check === $value)) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	/**
	 * Compares two values and returns the selected property for a select form field
	 *
	 * @since 4.0
	 *
	 * @param mixed $check Value to check
	 * @param mixed $value (optional) Value to check against
	 * @param bool $strict (optional) Whether to do a strict comparison
	 */
	public function selected($check, $value = true, $strict = false) {
		if ((!$strict && $check == $value) || ($strict && $check === $value)) {
			return 'selected="selected"';
		} else {
			return '';
		}
	}

	/**
	 * Returns an icon for success or failure, depending on the passed value
	 *
	 * @since 4.13
	 *
	 * @param bool $value Value to check
	 */
	public function yes_no($value) {
		if ($value) {
			return '<i class="dashicons dashicons-yes"></i>';
		} else {
			return '<i class="dashicons dashicons-no"></i>';
		}
	}

	/**
	 * Displays the MMP footer
	 *
	 * @since 4.0
	 */
	protected function footer() {
		if (MMP::$settings['whitelabelBackend']) {
			return;
		}

		?>
		<div class="wrap mmp-wrap">
			<div class="mmp-footer">
				<div class="mmp-footer-links">
					Maps Marker Pro<sup>&reg;</sup> <a href="https://www.mapsmarker.com/v<?= MMP::$version ?>" target="_blank" title="<?= esc_attr__('View release notes', 'mmp') ?>">v<?= MMP::$version ?></a>
					<a href="https://www.mapsmarker.com/" target="_blank">
						<img src="<?= plugins_url('images/icons/website-home.png', __DIR__) ?>" /> MapsMarker.com
					</a>
					<a href="https://affiliates.mapsmarker.com/" target="_blank" title="<?= esc_attr__('MapsMarker affiliate program - sign up now and receive commissions up to 50%!', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/affiliates.png', __DIR__) ?>" /> <?= esc_html__('Affiliates', 'mmp') ?>
					</a>
					<a href="https://www.mapsmarker.com/reseller/" target="_blank" title="<?= esc_attr__('MapsMarker reseller program - re-sell with a 20% discount!', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/resellers.png', __DIR__) ?>" /> <?= esc_html__('Resellers', 'mmp') ?>
					</a>
					<a href="https://www.mapsmarker.com/reviews/" target="_blank" title="<?= esc_attr__('Please review this plugin on wordpress.org', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/star.png', __DIR__) ?>" /> <?= esc_html__('Reviews', 'mmp') ?>
					</a>
					<a href="https://translate.mapsmarker.com/" target="_blank" title="<?= esc_attr__('Help translate Maps Marker Pro', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/translations.png', __DIR__) ?>" /> <?= esc_html__('Translations', 'mmp') ?>
					</a>
					<a href="https://www.mapsmarker.com/hackerone/" target="_blank" title="<?= esc_attr__('Bounty Hunters wanted - find security bugs to earn cash and licenses!', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/hackerone.png', __DIR__) ?>" /> hackerone
					</a>
					<a href="https://twitter.com/mapsmarker/" target="_blank" title="<?= esc_attr__('Follow @MapsMarker on Twitter', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/twitter.png', __DIR__) ?>" /> Twitter
					</a>
					<a href="https://facebook.com/mapsmarker/" target="_blank" title="<?= esc_attr__('Follow MapsMarker on Facebook', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/facebook.png', __DIR__) ?>" /> Facebook
					</a>
					<a href="https://www.mapsmarker.com/changelog/pro/" target="_blank">
						<img src="<?= plugins_url('images/icons/changelog.png', __DIR__) ?>" /> <?= esc_html__('Changelog', 'mmp') ?>
					</a>
					<a href="http://www.mapsmarker.com/feed/" target="_blank" title="<?= esc_attr__('News via RSS', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/rss.png', __DIR__) ?>" /> RSS
					</a>
					<a href="https://feedburner.google.com/fb/a/mailverify?uri=MapsMarker" target="_blank" title="<?= esc_attr__('News via email', 'mmp') ?>">
						<img src="<?= plugins_url('images/icons/rss-email.png', __DIR__) ?>" /> RSS (<?= esc_html__('via email', 'mmp') ?>)
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}

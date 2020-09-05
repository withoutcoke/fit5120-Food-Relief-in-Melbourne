<?php
namespace MMP;

use MMP\Maps_Marker_Pro as MMP;

class Geo_Sitemap {
	/**
	 * Registers the hooks
	 *
	 * @since 4.0
	 */
	public function init() {
		if (MMP::$settings['sitemapGoogle']) {
			add_action('sm_buildmap', array($this, 'add_kml_to_google_sitemap'));
		}
		if (MMP::$settings['apiSitemap'] && MMP::$settings['sitemapYoast']) {
			add_filter('wpseo_sitemap_index', array($this, 'add_sitemap_to_yoast'));
		}
	}

	/**
	 * Adds the KML links for maps to the sitemap generated by the Google XML Sitemaps plugin
	 *
	 * @since 4.0
	 */
	public function add_kml_to_google_sitemap() {
		$db = MMP::get_instance('MMP\DB');
		$api = MMP::get_instance('MMP\API');

		if (!class_exists('GoogleSitemapGenerator')) {
			return;
		}

		$sitemap = \GoogleSitemapGenerator::GetInstance();

		$maps = $db->get_all_maps(false, array(
			'include' => MMP::$settings['sitemapGoogleInclude'],
			'exclude' => MMP::$settings['sitemapGoogleExclude']
		));
		foreach ($maps as $map) {
			$sitemap->AddUrl(
				$api->link("/export/kml/{$map->id}/"),
				$map->updated_on,
				MMP::$settings['sitemapGoogleFrequency'],
				MMP::$settings['sitemapGooglePriority']
			);
		}
	}

	/**
	 * Adds the geo sitemap to the yoast sitemap index
	 *
	 * @since 4.0
	 *
	 * @param string $custom_items Current added custom items
	 */
	function add_sitemap_to_yoast($custom_items) {
		$db = MMP::get_instance('MMP\DB');
		$api = MMP::get_instance('MMP\API');

		$maps = $db->get_all_maps(array(
			'orderby'   => 'id',
			'sortorder' => 'DESC',
			'limit'     => 1
		));
		if (!count($maps)) {
			return $custom_items;
		}

		$loc = $api->link('/geo-sitemap/');
		$lastmod = (new \DateTime($maps[0]->created_on, new \DateTimeZone('UTC')))->format('c');
		$custom_items .= "<sitemap><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod></sitemap>";

		return $custom_items;
	}

	/**
	 * Shows a geo sitemap with links to the fullscreen maps
	 *
	 * @since 4.0
	 */
	public function show_sitemap() {
		$db = MMP::get_instance('MMP\DB');
		$api = MMP::get_instance('MMP\API');

		$maps = $db->get_all_maps();

		$sitemap = new \SimpleXMLElement(
			  '<?xml version="1.0" encoding="UTF-8"?>'
			. '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>'
		);
		foreach ($maps as $map) {
			$url = $sitemap->addChild('url');
			$url->addChild('loc', $api->link("/fullscreen/{$map->id}/"));
			$url->addChild('lastmod', (new \DateTime($map->updated_on, new \DateTimeZone('UTC')))->format('Y-m-d'));
		}

		header('Access-Control-Allow-Origin: *');
		header('Content-type: text/xml; charset=utf-8');

		echo $sitemap->asXML();
	}
}
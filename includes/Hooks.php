<?php

namespace MediaWiki\Extension\DarkMode;

use OutputPage;
use Skin;
use SkinTemplate;
use Title;

class Hooks {
	/**
	 * Handler for PersonalUrls hook.
	 * Add a "Dark mode" item to the user toolbar ('personal URLs').
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PersonalUrls
	 * @param array &$personal_urls Array of URLs to append to.
	 * @param Title &$title Title of page being visited.
	 * @param SkinTemplate $skin
	 */
	public static function onPersonalUrls( array &$personal_urls, Title &$title, SkinTemplate $skin ) {
		if ( !self::shouldHaveDarkMode( $skin ) ) {
			return;
		}

		$insertUrls = [
			'darkmode-link' => [
				'text' => $skin->msg( 'darkmode-link' )->text(),
				'href' => '#',
				'active' => false,
			]
		];

		$personal_urls = wfArrayInsertAfter( $personal_urls, $insertUrls, 'mytalk' );
	}

	/**
	 * Handler for BeforePageDisplay hook.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param OutputPage $output
	 * @param Skin $skin Skin being used.
	 */
	public static function onBeforePageDisplay( $output, $skin ) {
		if ( !self::shouldHaveDarkMode( $skin ) ) {
			return;
		}

		// Load the JS module.
		$output->addModules( [ 'ext.DarkMode' ] );
	}

	/**
	 * Conditions for when Dark Mode should be available.
	 * @param Skin $skin
	 * @return bool
	 */
	private static function shouldHaveDarkMode( $skin ) {
		return $skin->getUser()->isLoggedIn() && $skin->getSkinName() !== 'minerva';
	}
}

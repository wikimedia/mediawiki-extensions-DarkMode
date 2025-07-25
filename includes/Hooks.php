<?php

namespace MediaWiki\Extension\DarkMode;

use Config;
use ExtensionRegistry;
use IContextSource;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\SkinAddFooterLinksHook;
use MediaWiki\Hook\SkinBuildSidebarHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Html\Html;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\User\UserOptionsLookup;
use OutputPage;
use Skin;
use SkinTemplate;
use User;

class Hooks implements
	SkinAddFooterLinksHook,
	SkinTemplateNavigation__UniversalHook,
	SkinBuildSidebarHook,
	BeforePageDisplayHook,
	GetPreferencesHook
{

	public const POSITION_FOOTER = 'footer';
	public const POSITION_PERSONAL = 'personal';
	public const POSITION_SIDEBAR = 'sidebar';
	public const TOGGLE_POSITIONS = [
		self::POSITION_FOOTER => 'footer',
		self::POSITION_PERSONAL => 'personal',
		self::POSITION_SIDEBAR => 'sidebar',
	];

	/** @var string */
	public const CSS_CLASS = 'ext-darkmode-link';

	/** @var string */
	private $linkPosition;

	/** @var UserOptionsLookup */
	private $userOptionsLookup;

	/**
	 * @param Config $options
	 * @param UserOptionsLookup $userOptionsLookup
	 */
	public function __construct(
		Config $options,
		UserOptionsLookup $userOptionsLookup
	) {
		$this->linkPosition = $options->get( 'DarkModeTogglePosition' );
		$this->userOptionsLookup = $userOptionsLookup;
	}

	/**
	 * Handler for SkinAddFooterLinks hook.
	 * Add a "Dark mode" item to the footer if DarkModeTogglePosition is set to 'footer'.
	 *
	 * @param Skin $skin Skin being used.
	 * @param string $key Current position in the footer.
	 * @param array &$footerItems Array of URLs to add to.
	 */
	public function onSkinAddFooterLinks( Skin $skin, string $key, array &$footerItems ) {
		if ( $key !== 'places' ||
			!self::shouldHaveDarkMode( $skin ) ||
			$this->linkPosition !== self::POSITION_FOOTER
		) {
			return;
		}

		$footerItems['darkmode'] = Html::element(
			'a',
			$this->getLinkAttrs(
				$skin,
				'nwwmw-ui-icon mw-ui-icon-before mw-ui-icon-darkmode'
			),
			$this->getLinkText( $skin )
		);
	}

	/**
	 * Handler for SkinTemplateNavigation__UniversalHook.
	 * Add a "Dark mode" item to the personal links (usually at the top),
	 *   if DarkModeTogglePosition is set to 'personal'.
	 *
	 * @param SkinTemplate $skin
	 * @param array &$links
	 * @return void This hook must not abort, it must return no value
	 * @phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 */
	public function onSkinTemplateNavigation__Universal( $skin, &$links ): void {
		// phpcs:enable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
		if ( !self::shouldHaveDarkMode( $skin ) || $this->linkPosition !== self::POSITION_PERSONAL ) {
			return;
		}

		$insertUrls = [
			'darkmode' => $this->getLinkAttrs( $skin ),
		];

		// Adjust placement based on whether user is logged in or out.
		if ( array_key_exists( 'mytalk', $links['user-menu'] ) ) {
			$after = 'mytalk';
		} elseif ( array_key_exists( 'anontalk', $links['user-menu'] ) ) {
			$after = 'anontalk';
		} else {
			// Fallback to showing at the end.
			$after = false;
			$links['user-menu'] += $insertUrls;
		}

		if ( $after ) {
			$links['user-menu'] = wfArrayInsertAfter( $links['user-menu'], $insertUrls, $after );
		}
	}

	/**
	 * Handler for SkinBuildSidebar hook.
	 * Add a "Dark mode" item to the sidebar in the navigation portlet menu,
	 *   if DarkModeTogglePosition is set to 'sidebar'.
	 *
	 * @param SkinTemplate $skin
	 * @param array &$bar
	 */
	public function onSkinBuildSidebar( $skin, &$bar ) {
		if ( !self::shouldHaveDarkMode( $skin ) || $this->linkPosition !== self::POSITION_SIDEBAR ) {
			return;
		}

		$bar['navigation'][] = $this->getLinkAttrs( $skin );
	}

	/**
	 * Handler for BeforePageDisplay hook.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin Skin being used.
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( !self::shouldHaveDarkMode( $skin ) ) {
			return;
		}

		$out->addModules( 'ext.DarkMode' );
		$out->addModuleStyles( 'ext.DarkMode.styles' );

		if ( $this->isDarkModeActive( $skin ) ) {
			// The class must be on the <html> element because the CSS filter creates a new stacking context.
			// If we use the <body> instead (OutputPage::addBodyClasses), any fixed-positioned content
			// will be hidden in accordance with the w3c spec: https://www.w3.org/TR/filter-effects-1/#FilterProperty
			// Fixed elements may still be hidden in Firefox due to https://bugzilla.mozilla.org/show_bug.cgi?id=1650522
			// client-darkmode is added for backwards compatibility.
			$out->addHtmlClasses( [ 'skin-theme-clientpref-night', 'client-darkmode' ] );
		} else {
			$out->addHtmlClasses( 'skin-theme-clientpref-day' );
		}
	}

	/**
	 * Handler for GetPreferences hook
	 * Add hidden preference to keep dark mode turned on all pages
	 *
	 * @param User $user Current user
	 * @param array &$preferences
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['darkmode'] = [
			'type' => 'api',
			'default' => 0,
		];
	}

	/**
	 * Conditions for when Dark Mode should be available.
	 * @param Skin $skin
	 * @return bool
	 */
	private static function shouldHaveDarkMode( Skin $skin ): bool {
		$name = $skin->getSkinName();
		return !in_array( $name, ExtensionRegistry::getInstance()->getAttribute( 'DarkModeDisabled' ) );
	}

	/**
	 * Is the Dark Mode active?
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	private function isDarkModeActive( IContextSource $context ): bool {
		$var = $context->getRequest()->getRawVal( 'usedarkmode' );

		if ( $var === '0' || $var === '1' ) {
			// On usedarkmode=0 or usedarkmode=1 overwrite the user setting.
			return (bool)$var;
		}
		// On no parameter use the user setting.
		return $this->userOptionsLookup->getBoolOption( $context->getUser(), 'darkmode' );
	}

	/**
	 * @param IContextSource $context
	 * @param string $additionalClasses
	 * @return array
	 */
	private function getLinkAttrs( IContextSource $context, string $additionalClasses = '' ): array {
		$active = $this->isDarkModeActive( $context );

		return [
			'text' => $this->getLinkText( $context ),
			'href' => '#',
			'class' => self::CSS_CLASS . ' ' . $additionalClasses,
			'title' => $context->msg( $active ?
				'darkmode-default-link-tooltip' :
				'darkmode-link-tooltip'
			)->text(),
			'icon' => $active ? 'moon' : 'bright',
		];
	}

	/**
	 * Get the initial message text for the dark mode toggle link.
	 *
	 * @param IContextSource $context
	 * @return string
	 */
	private function getLinkText( IContextSource $context ): string {
		return $context->msg( $this->isDarkModeActive( $context )
			? 'darkmode-default-link'
			: 'darkmode-link'
		)->text();
	}

}

<?php

if ( !defined( 'MEDIAWIKI' ) )
	die();

/**
 * General extension information.
 */
$wgExtensionCredits['specialpage'][] = array(
	'path'           				=> __FILE__,
	'name'           				=> 'BooksOnlineOcdla',
	'version'        				=> '0.0.0.1',
	'author'         				=> 'José Bernal',
	// 'descriptionmsg' 		=> 'wikilogocdla-desc',
	// 'url'            		=> 'http://www.mediawiki.org/wiki/Extension:WikilogOcdla',
);



$dir = dirname( __FILE__ );

class BooksOnlineOcdla {

	public static function isBonNamespace($ns){
		global $wgOcdlaBooksOnlineNamespaces;
		return in_array($ns,$wgOcdlaBooksOnlineNamespaces);
	}

	public static function onParserSetup( Parser $parser ) {
		// When the parser sees the <sample> tag, it executes renderTagSample (see below)
		$parser->setHook( 'bonUpdate', 'BooksOnlineOcdla::renderTagBonUpdate' );
		return true;
	}


	// Render <bonUpdate>	
	public static function renderTagBonUpdate( $input, array $args, Parser $parser, PPFrame $frame ) {
		// Nothing exciting here, just escape the user-provided input and throw it back out again (as example)
		// return htmlspecialchars( $input );
		$month = $args['month'];
		$year = $args['year'];
		$deprecated = $args['deprecated'];

		$output = $parser->recursiveTagParse( $input, $frame );

		$title_parts = array($month, $year, "update");
		$title_parts = array_filter($title_parts, function($part){ return !empty($part); });
		$title_parts = array_map(function($part){ return ucwords($part); }, $title_parts);
		

		$classes = array("bon-update");

		if(!empty($year)) {
			$classes []= sprintf("bon-update-%s", $year);
		}

		if(!empty($deprecated) && $deprecated === "true") {
			$classes []= "bon-update-deprecated";
			$title_parts []= "(deprecated)";
		}

		if(!empty($month) && !empty($year)) {
			$classes []= sprintf("bon-update-%s-%s", $year, $month);
		}

		$classes = implode(" ", $classes);
		$title = implode(" ", $title_parts);

		return "<div class='{$classes}'><span class='bon-update-title'>{$title}</span>".$output."</div>";
	}


	public static function SetupBooksOnlineOcdla(){
		global $wgHooks, $wgResourceModules, $wgOcdlaShowBooksOnlineDrawer;

		// $wgHooks['SpecialSearchCreateLink'][] = 'SetupBooksOnlineOcdla::onSpecialSearchCreateLink';
		$wgHooks['BeforePageDisplay'][] = 'BooksOnlineOcdla::onBeforePageDisplay';
		$wgHooks['ParserFirstCallInit'][] = 'BooksOnlineOcdla::onParserSetup';


		$wgResourceModules['ext.booksOnlineOcdla.search'] = array(
			'scripts' => array('js/books-online-view.js','js/books-online-loader.js','js/search.controller.js'),
			'dependencies' => array('clickpdx.framework.js'),
			'position' => 'top',
			'remoteBasePath' => '/extensions/BooksOnlineOcdla',
			'localBasePath' => 'extensions/BooksOnlineOcdla'
		);
		
		$wgResourceModules['ext.booksOnlineOcdla.styles'] = array(
			'styles' => array(
				'css/bon.css'
			),
			'dependencies' => array('ext.uiFixedNav'), 
			'position' => 'top',
			'remoteBasePath' => '/extensions/BooksOnlineOcdla',
			'localBasePath' => 'extensions/BooksOnlineOcdla'
		);
		
	}
	
	
	public static function onBeforePageDisplay(OutputPage &$out, Skin &$skin ) {
		global $wgOcdlaShowBooksOnlineDrawer, $wgOcdlaShowBooksOnlineNs;
		
		$checkNamespace = isset($wgOcdlaShowBooksOnlineNs) && $wgOcdlaShowBooksOnlineNs != NS_ALL;
		
		$skin->customElements = array();
		
		$title = $out->getTitle();
		$ns = $title->getNamespace();
		
		if(self::isBonNamespace($ns)||$ns == NS_SSM) {
			/*
			$out->addModuleStyles( [
				'ext.booksOnlineOcdla.styles'
				//'skins.ocdla.styles',
			] );
			*/
			// $out->addStyle('/extensions/BooksOnlineOcdla/css/bon.css', 'all');
			// $out->addModules('ext.booksOnlineOcdla.styles');
		} else {
			// $out->addModules('ext.booksOnlineOcdla.banner');
		}
		
		$out->addModules('ext.booksOnlineOcdla.search');

		return true;
	}

}
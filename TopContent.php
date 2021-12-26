<?php
class TopContent {

	/**
	 * Hook our callback function into the parser
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		// When the parser sees the <sample> tag, it executes
		// the wfSampleRender function (see below)
		$parser->setHook( 'topcontent', 'TopContent::processHook' );
		// Always return true from this function. The return value does not denote
		// success or otherwise have meaning - it just must always be true.
		return true;
	}

	/**
	 * Callback executed when the <topcontent> tag is found in the text: parse the contents and store it in the
	 * ParserOutput to be passed around the houses later on
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	function processHook( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parsedText = $parser->recursiveTagParse( $input, $frame );
		$parsedText = $parser->doBlockLevels( $parsedText, true );
		$parser->replaceLinkHolders( $parsedText );
		// This overrides any previously-set top content...
		$parser->getOutput()->topcontent = $parsedText;
		// We return nothing
		return '';
	}

	/**
	 * This is the closest hook called after the parsing cycle in Article::view(), so this is where we pick up the
	 * topcontent, if any is set, from the ParserOutput and save it in the OutputPage so we can subsequently get to
	 * it from the Skin.
	 * @param Article $article
	 * @return bool
	 */
	public static function onArticleViewFooter( Article $article ) {
		if ( isset( $article->mParserOutput ) && isset( $article->mParserOutput->topcontent ) ) {
			$article->getContext()->getOutput()->topContent = $article->mParserOutput->topcontent;
		}

		return true;
	}

	/**
	 * Called after the site notice has been constructed.  If the page content contained <topcontent> tags, we inject
	 * that into the output.
	 * @param string &$siteNotice
	 * @param Skin $skin
	 * @return bool
	 */
	public static function onSiteNoticeAfter( &$siteNotice, Skin $skin ) {
		if ( isset( $skin->getContext()->getOutput()->topContent ) ) {
			$topcontent = $skin->getContext()->getOutput()->topContent;
			$siteNotice = "$siteNotice</div><div id='topcontent'>$topcontent";
		}
		return true;
	}
}

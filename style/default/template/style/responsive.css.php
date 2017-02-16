<?php $config = ['priority' => 9001] ?>
/**
 * Document    : responsive.css
 * Created on  : ‎March ‎20, ‎2015, ‏‎17:46:23
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Tweaks for various screen sizes, etc.
 */

@media only screen {
	/* 929 Wide (Half 1080p) */
	:root {
		font-size: 10px;
	}

	/* 1249 Wide (Half 1440p) */
	@media (min-width: 1089px) {
		:root {
			font-size: 12px;
		}
	}

	/* 4k+ at 96dpi (not properly configured?) */
	@media (min-device-width: 3200px) {
		/* 1889 Wide (Half 2160p) */
		@media (min-width: 1569px) {
			:root {
				font-size: 18px;
			}
		}

		/* 2529 Wide (Half 2880p) */
		@media (min-width: 2209px) {
			:root {
				font-size: 25px;
			}
		}
	}

	/* Remove extra details that smaller screens will not even see. */
	@media (max-width: 870px) {
		:root {
			background: <?= is_array($v['style_background']) ? $v['style_background'][0] : $v['style_background'] ?>;
		}

		.fancy-box {
			box-shadow: none;
		}
	}

	/*
	 * Drop to fluid width with small page header. We do this regardless of attempting to preserve the desktop layout or
	 * not as it's just simply nicer on smaller screens, and no more difficult to navigate.
	 */
	@media (max-width: 870px) {
		#site-header {
			margin-top: 0 !important;
			padding: 0.4rem 1.4rem 0.4rem;
		}

		#site-title {
			font-size: 2.6rem;
		}

		#site-tagline {
			font-size: 1.6rem;

			margin-left: 0.8rem;
		}

		.fancy-box {
			margin-top: 0.4rem;
		}

		body {
			display: block;

			width: auto;
		}

		h1 {
			font-size: 1.4em;
		}

		ol, ul {
			margin-left: 0.8rem;
		}
	}

	/*
	 * Target:
	 *  - average to large phones in landscape
	 */
	@media (orientation: landscape) and (max-device-width: 1279px) {
		/* Provide desktop-esque website. */
		@media (max-width: 870px) {
			:root {
				font-size: 1.16vw;
			}
		}
	}

	/* Target:
	 *  - desktop/laptop/tablet/television in all orientations
	 *  - average to large phones in portrait
	 *  - very small phones in all orientations
	 */
	@media (orientation: portrait), (min-device-width: 1280px), (max-width: 650px) {
		@media (max-width: 870px) {
			:root {
				font-size: 10px;
			}
		}
	}
}

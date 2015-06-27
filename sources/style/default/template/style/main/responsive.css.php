<?php $config = ['priority' => 9001] ?>
/**
 * Document    : responsive.css
 * Created on  : ‎March ‎20, ‎2015, ‏‎17:46:23
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Tweaks for various screen sizes, etc.
 */

@media only screen {
	@media (min-device-width : 900px) {
		/* 929 Wide (Half 1080p) */
		:root {
			font-size : 10px;
		}

		/* 1249 Wide (Half 1440p) */
		@media (min-width : 1089px) {
			:root {
				font-size : 12px;
			}
		}

		/* 4k+ at 96dpi (not properly configured?) */
		@media (min-device-width : 3200px) {
			/* 1889 Wide (Half 2160p) */
			@media (min-width : 1569px) {
				:root {
					font-size : 18px;
				}
			}

			/* 2529 Wide (Half 2880p) */
			@media (min-width : 2209px) {
				:root {
					font-size : 25px;
				}
			}
		}
	}

	@media (max-device-width : 889px) {
		:root {
			background : <?= is_array($v['style_background']) ? $v['style_background'][0] : $v['style_background'] ?>;
		}

		.fancy-box {
			box-shadow : none;
		}

		@media (orientation : landscape) {
			/* iPhone 6 Plus (414x736) Full View */
			@media (device-width : 414px)
			and (-webkit-device-pixel-ratio : 3) {
				:root {
					font-size : 8.65px; /* 8.65 * 85 is roughly 736 */
				}
			}

			/* iPhone 6 (375x667) Print View */
			@media (device-width : 375px)
			and (-webkit-device-pixel-ratio : 2) {
				:root {
					font-size : 7.64px; /* 7.84 * 85 is roughly 736 */
				}
			}
		}
	}

	/* This works on iOS even though MDN advises against using it for that.
	 * Maybe we'll look at other devices in the future to handle their edge cases. */
	@media (max-device-width : 889px) and (orientation : portrait), (max-width : 870px) {
		#site-header {
			margin-top : 0 !important;
			padding    : 0.4rem 1.4rem 0.4rem;
		}

		#site-title {
			font-size : 2.6rem;
		}

		#site-tagline {
			font-size   : 1.6rem;

			margin-left : 0.8rem;
		}

		.fancy-box {
			margin-top : 0.4rem;
		}

		body {
			display : block;

			width   : auto;
		}

		h1 {
			font-size : 1.4em;
		}

		ol, ul {
			margin-left : 0.8rem;
		}
	}
}

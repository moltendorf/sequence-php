<?php $config = ['priority' => 1100] ?>
/**
 * Document    : core-class.css
 * Created on  : ‎March ‎20, ‎2015, ‏‎13:00:19
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Margins, padding, borders, etc.
 */

.fancy-box {
	background-color    : <?= $v['style_textcolor'][1] ?>;

	border              : 0.1rem solid;
	border-top-color    : <?= $v['style_border'][0] ?>;
	border-right-color  : <?= $v['style_border'][1] ?>;
	border-bottom-color : <?= $v['style_border'][2] ?>;
	border-left-color   : <?= $v['style_border'][3] ?>;

	box-shadow          : <?= $v['style_shadow'] ?>;

	margin-top          : 1.6rem;
	padding             : 0.6rem 1.4rem 0.8rem;
}

.site-nav:link, .site-nav:visited {
	color           : <?= $v['style_navcolor'][0] ?>;
	font-size       : 2.6rem;
	line-height     : 1.0em;
	text-decoration : none;
	text-transform  : lowercase;

	margin-left     : 1.6rem;
}

.site-nav.active:link, .site-nav.active:visited {
	color : <?= $v['style_navcolor'][1] ?>;
}

.site-nav:hover, .site-nav:visited:hover {
	color : <?= $v['style_navcolor'][2] ?> !important;
}

.site-nav.disabled:link, .site-nav.disabled:visited {
	color           : <?= $v['style_navcolor'][0] ?> !important;
	text-decoration : line-through;
}

#site-sub-nav .site-nav:link, #site-sub-nav .site-nav:visited {
	font-size   : 2.0rem;

	margin-left : 1.2rem;
}

.print {
	display : none;
}

<?php $config = ['priority' => 1200] ?>
/**
 * Document    : core-id.css
 * Created on  : March ‎20, ‎2015, ‏‎12:58:08
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Margins, padding, borders, etc.
 */

#site-header {
	text-align : left;

	overflow   : auto;

	display    : block;
	margin-top : 2.4rem;
	padding    : 0.6rem 1.4rem 1.0rem;
}

#site-header > h1 {
	display : inline-block;
	float   : left;
}

#site-title {
	color           : <?= $v['style_navcolor'][1] ?>;
	font-size       : 5.6rem;
	font-weight     : 300;
	line-height     : 1.0em;
	text-decoration : none;
	text-transform  : lowercase;

	display         : block;
}

#site-title:hover {
	color : <?= $v['style_navcolor'][2] ?>;
}

#site-tagline {
	color          : <?= $v['style_navcolor'][0] ?>;
	font-size      : 1.8rem;
	font-weight    : 300;
	line-height    : 1.0em;
	text-transform : lowercase;

	display        : block;
	margin-left    : 1.8rem;
}

#site-nav {
	text-align : center;

	float      : right;
}

#footer-wrap {
	text-align     : center;

	display        : block;
	margin-top     : 0.8rem;
	padding-bottom : 6.4rem;
}

#footer-copyright, #footer-powered-by {
	color     : <?= $v['style_footer'] ?>;
	font-size : 1.2rem;

	display   : block;
}

#footer-powered-by > a {
	color : <?= $v['style_footer'] ?>;
}

#info p + p {
	margin-top : 0;
}

#info .exception, #info .trace, #info .output {
	border  : 0.1rem solid <?= $v['style_border'][0] ?>;
	margin  : 0.8rem 1.6rem 1.6rem;
	padding : 1.6rem;
}

#info .mono, #info .trace, #info .output {
	font-family : "source-code-pro", monospace;
	font-size   : 1.1rem;
	white-space : pre-wrap;
	word-wrap   : break-word;
}

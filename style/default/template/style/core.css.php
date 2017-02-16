<?php $config = ['priority' => 1000] ?>
/**
 * Document    : core.css
 * Created on  : May ‎06, ‎2014, ‏‎23:33:14
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Margins, padding, borders, etc.
 */

* {
	outline-style : none;

	margin        : 0;
	padding       : 0;
}

:root {
	background               : <?php if (is_array($v['style_background'])): ?>linear-gradient(<?= implode(', ', $v['style_background']) ?>) fixed <?php else: ?><?= $v['style_background'] ?><?php endif; ?>;

	color                    : <?= $v['style_textcolor'][0] ?>;

	font-family              : "freight-sans-pro", sans-serif;
	font-size                : 12px;
	font-weight              : 300;
	text-align               : center;

	overflow-y               : scroll;

	height                   : 100%;

	-webkit-text-size-adjust : none;
}

body {
	font-size  : 1.6rem;
	text-align : left;

	display    : inline-block;
	position   : relative;

	width      : 85rem;
}

a:link {
	color : <?= $v['style_linkcolor'][0] ?>;
}

a:visited {
	color : <?= $v['style_linkcolor'][2] ?>;
}

a:hover {
	color : <?= $v['style_linkcolor'][1] ?>;
}

a:visited:hover {
	color : <?= $v['style_linkcolor'][3] ?>;
}

ol, ul {
	padding-left : 1.2rem;
}

h1 {
	font-size   : 2em;
	font-weight : 300;
}

h2 {
	font-size   : 1.8em;
	font-weight : 300;
}

h3 {
	font-size   : 1.4em;
	font-weight : 300;
}

h4 {
	font-size   : 1.2em;
	font-weight : 300;
}

img + p, p + p {
	margin-top : 2.4rem;
}

strong {
	font-weight : 500;
}

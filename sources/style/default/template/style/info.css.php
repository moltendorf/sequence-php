<?php $f('style/header.css') ?>

<?php $f('style/main/core.css') ?>

/**
 * Document    : info.css
 * Created on  : ‎‎February ‎18, ‎2015, ‏‎19:26:54
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Info page styles.
 */

:root {
	background : linear-gradient(168deg, #efefef, #e0e0ff) fixed !important;
	color      : #000000;
}

body#info {
	display : block;

	width   : auto;
}

#info h1, #info h2 {
	color       : #7799bb;
	font-weight : 300;
}

#info h1 {
	font-size : 2.4rem;

	margin    : 2.2rem 3.2rem 0;
}

#info .exception > h1 {
	font-size : 1.8rem;

	margin    : 0 0 0.8rem;
}

#info .exception > p {
	margin : 0;
}

#info h2 {
	font-size : 1.8rem;

	margin    : 1.6rem 4.0rem 0;
}

#info .exception > h2 {
	margin : 0.8rem 0.8rem 0;
}

#info .message > h2 {
	margin : 0;
}

#info h1 + h2 {
	margin-top : 0.8rem;
}

#info div {
	background-color : #ffffff;

	border           : 0.1rem solid #7799bb;
	margin           : 0.8rem 3.2rem 2.4rem;
	padding          : 1.6rem;
}

#info .mono, #info .trace, #info .output {
	font-family : "source-code-pro", monospace;
	font-size   : 1.1rem;
	white-space : pre-wrap;
	word-wrap   : break-word;
}

#info .trace {
	margin : 0.8rem 0 0;
}

<?php $f('style/main/responsive.css') ?>

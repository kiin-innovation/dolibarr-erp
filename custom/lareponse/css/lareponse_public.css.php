<?php
header('Content-type: text/css');
?>

body {
	font-family: roboto,arial,tahoma,verdana,helvetica;
	margin: 0;
	background-color: rgb(237, 237, 237);
	min-height: 100vh;
	display: flex;
	flex-direction: column;
}

#id-top {
	display: flex;
	flex-grow: 1;
	width: 100%;
	box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
}

.public_article_header {
	background-color: #EA7600;
	padding: 1%;
	width: 100%;
	justify-content: space-between;
	text-align: center;
}

.public_article_header img {
	max-height: 6vh;
}

.border-div {
	width: 5%;
	min-height: 1em;
	display: inline-block;
}

#border-div-left {
	float: left;
}

#border-div-right {
	float: right;
}

#title-div {
	width: 70%;
	display: inline-block;
}

#title-div h1 {
	font-size: 2em;
	color: rgb(240, 240, 240);
}

.public_article_header #society-logo {
	margin-right: auto;
	margin-left: auto;
}

.container {
	align-items: center;
	justify-content: center;
	display: flex;
	flex-grow: 10;
}

div.fiche {
	border: solid thin white;
	background-color: #fafafa;
	border-radius: 5px;
	width: 90vw;
	padding: 1%;
	margin: 2%;
	box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
}

div.iframe {
	display: flex;
	flex-direction: row-reverse;

	.fa-expand-alt {
		position: fixed;
		padding: 10px;
	}

	.fa-compress-alt {
		z-index: 1;
		position: fixed;
		top: 10px;
		right: 10px;
	}
}

div.fiche img {
	max-width: 100%;
	height: auto !important;
}

.public_article_footer {
	justify-content: center;
	background-color: darkgrey;
	font-size:1em;
	height: 5em;
	padding: 0.5%;
	display: flex;
	flex-grow: 1;
	bottom: 0;
}

#inbetween-footer {
	width: 20%;
	height: 100%;
}

.public_article_footer p {
	display: inline-block;
	padding-top: 0.9em;
	float: right;
}

iframe.iframe-fullscreen {
	width: 100%;
	height: 100%;
	position: absolute;
	top: 0px;
	left: 0px;
}
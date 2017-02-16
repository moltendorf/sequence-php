<?php $config = ['priority' => 2000] ?>
/**
 * Document    : account.css
 * Created on  : January 01, ‎2016, 17:23‏:07
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : General account management windows.
 */

#account-module fieldset {
    border-style: none;

    position: relative;
}

.account-log-in {
    clear: left;
    float: left;

    width: 50%;
}

.account-or {
    text-align: center;

    position: absolute;

    top: 0;
    right: 45%;
    left: 45%;
}

.account-log-in > section {
    border-right: 0.1rem solid <?= $v['style_border'][1] ?>;

    margin-right: 0.2rem;
    margin-bottom: 0.8rem;
}

.account-sign-up {
    clear: right;
    float: right;

    width: 50%;
}

.account-sign-up > section {
    border-left: 0.1rem solid <?= $v['style_border'][3] ?>;

    margin-left: 0.2rem;

    position: absolute;

    top: 4.33rem;
    right: 0;
    bottom: 0.8rem;
    left: 50%;
}

.account-sign-up > section > div {
    position: absolute;

    right: 0;
    bottom: 0;
    left: 0;
}

.account-sign-up > section > div > p {
    padding-right: 3.2rem;
    padding-left: 3.2rem;

    text-align: justify;
}

.account-log-in, .account-sign-up {
    text-align: center;
}

.account-module h1 {
    text-align: center;
}

#account-module input {
    color: <?= $v['style_textcolor'][0] ?>;

    background-color: <?= $v['style_textcolor'][2] ?>;

    border: 0.1rem solid;
    border-top-color: <?= $v['style_border'][0] ?>;
    border-right-color: <?= $v['style_border'][1] ?>;
    border-bottom-color: <?= $v['style_border'][2] ?>;
    border-left-color: <?= $v['style_border'][3] ?>;

    font-family: "freight-sans-pro", sans-serif;
    font-size: 1.8rem;
    font-weight: 300;

    line-height: 2.4rem;

    padding-top: 0.5rem;
    padding-right: 0.6rem;
    padding-bottom: 0.6rem;
    padding-left: 0.6rem;

    width: inherit;
    height: 2.4rem;
}

#account-module input:focus {
    background-color: <?= $v['style_textcolor'][1] ?>;
}

#account-module input[type="checkbox"] {
    width: 1.4rem;
    height: 1.6rem;
}

.input-button, .input-checkbox, .input-text {
    display: inline-block;

    font-size: 1.8rem;
    line-height: 2.4rem;

    position: relative;

    margin-top: 0.8rem;
    margin-bottom: 0.8rem;

    width: 90%;
}

.input-button {
    color: <?= $v['style_textcolor'][1] ?> !important;

    font-weight: 500 !important;

    cursor: pointer;

    background-color: <?= $v['style_navcolor'][1] ?> !important;

    padding-top: 0 !important;
    padding-bottom: 0 !important;

    height: 2.9rem !important;
}

.input-button:hover {
    background-color: <?= $v['style_navcolor'][2] ?> !important;
}

.input-button:active {
    background-color: <?= $v['style_navcolor'][0] ?> !important;
}

.input-checkbox {
    text-align: left;
}

.input-checkbox > input {
    margin-left: 1.8rem;
}

.input-checkbox > label {
    cursor: pointer;

    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;

    padding-top: 0;
    padding-left: 4.0rem;

    position: absolute;

    top: 0;
    right: 20rem;
    bottom: 0;
    left: 0;
}

.input-text > label {
    color: <?= $v['style_navcolor'][0] ?>;

    cursor: text;

    display: none;

    padding-top: 0.6rem;
    padding-left: 1.8rem;

    position: absolute;

    top: 0;
    right: 0;
    bottom: 0;
    left: 0;

    text-align: left;
}

.input-text input:invalid + label {
    display: block;
}

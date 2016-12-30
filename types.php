<?php

/*
 * Default list of internet media types.
 */

return [
  /*
   * Markup.
   */
  [['html'], ['text/html']],
  [['css'], ['text/css']],
  [['txt'], ['text/plain']],
  [['xml'], ['text/xml']],

  /*
   * Code.
   */
  [['js'], ['application/javascript']],
  [['json'], ['application/json']],

  /*
   * Files.
   */
  [['zip'], ['application/zip', null]],

  /*
   * Audio.
   */
  [['m4a'], ['audio/mp4', null]],
  [['mp3'], ['audio/mpeg', null]],
  [['ogg'], ['audio/ogg', null]],
  [['opus'], ['audio/opus', null]],
  [['vorbis'], ['audio/vorbis', null]],
  [['wav'], ['audio/vnd.wave', null]],
  [['webm'], ['audio/webm', null]],

  /*
   * Images.
   */
  [['gif'], ['image/gif', null]],
  [['jpg'], ['image/jpeg', null]],
  [['jpeg'], ['image/jpeg', null]],
  [['png'], ['image/png', null]],
  [['svg'], ['image/svg+xml', null]],

  /*
   * Video.
   */
  [['avi'], ['video/avi', null]],
  [['m4v', 'mp4'], ['video/mp4', null]],
  [['oggv'], ['video/ogg', null]],
  [['mov'], ['video/quicktime', null]],
  [['webmv'], ['video/webm', null]],
  [['wmv'], ['video/x-ms-wmv', null]],
  [['flv'], ['video/x-flv', null]],

  /*
   * Documents.
   */
  [['pdf'], ['application/pdf', null]],
  [['docx'], ['application/vnd.ms-word', null]],
  [['xlsx'], ['application/vnd.ms-excel', null]],
  [['pptx'], ['application/vnd.ms-powerpoint', null]],
  [['oxps'], ['application/vnd.ms-xpsdocument', null]],
  [['odt', 'fodt'], ['application/vnd.oasis.opendocument.text', null]],
  [['ods', 'fods'], ['application/vnd.oasis.opendocument.spreadsheet', null]],
  [['odp', 'fodp'], ['application/vnd.oasis.opendocument.presentation', null]],
  [['odg', 'fodg'], ['application/vnd.oasis.opendocument.graphics', null]]
];

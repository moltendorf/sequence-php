<?php
/**
 * Document    : multimodule.php
 * Created on  : ‎‎02/02, 2017, 22:21
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Interface MultiModule
   * A multi module. This module handles both request fetches and protocol queries.
   *
   * @package sequence\root\module
   */
  interface MultiModule extends RequestModule, ProtocolModule {

  }
}

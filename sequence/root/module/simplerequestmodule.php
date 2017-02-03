<?php
/**
 * Document    : simplerequestmodule.php
 * Created on  : ‎‎02/02, 2017, 21:18
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Class SimpleRequestModule
   * The simplest request module. This primary class exists to do common utility routines and set-up. A second class
   * exists to do all the heavy lifting, serving requests based on the name of each method.
   *
   * @package sequence\root\module
   */
  abstract class SimpleRequestModule implements RequestModule {

    use SimpleRequestModuleTrait;
  }
}

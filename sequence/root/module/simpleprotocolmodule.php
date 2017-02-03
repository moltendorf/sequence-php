<?php
/**
 * Document    : simpleprotocolmodule.php
 * Created on  : ‎‎02/02, 2017, 21:45
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Class SimpleProtocolModule
   * The simplest protocol module. This primary class exists to do common utility routines and set-up. A second class
   * exists to do all the heavy lifting, serving queries based on the name of each method.
   *
   * @package sequence\root\module
   */
  abstract class SimpleProtocolModule implements ProtocolModule {

    use SimpleProtocolModuleTrait;
  }
}

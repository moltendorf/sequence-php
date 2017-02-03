<?php
/**
 * Document    : namespaceprotocolmodule.php
 * Created on  : ‎‎02/02, 2017, 21:49
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Class NamespaceProtocolModule
   * The most common protocol module. This primary class exists to do common utility routines and set-up. Child
   * namespaces exist that contain classes to do all the heavy lifting, serving queries based on the name of the
   * namespace and class.
   *
   * @package sequence\root\module
   */
  abstract class NamespaceProtocolModule implements ProtocolModule {

    use NamespaceProtocolModuleTrait;
  }
}

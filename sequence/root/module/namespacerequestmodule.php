<?php
/**
 * Document    : namespacerequestmodule.php
 * Created on  : ‎‎02/02, 2017, 21:33
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Class NamespaceRequestModule
   * The most common request module. This primary class exists to do common utility routines and set-up. Child
   * namespaces exist that contain classes to do all the heavy lifting, serving requests based on the name of the
   * namespace and class.
   *
   * @package sequence\root\module
   */
  abstract class NamespaceRequestModule implements RequestModule {

    use NamespaceRequestModuleTrait;
  }
}

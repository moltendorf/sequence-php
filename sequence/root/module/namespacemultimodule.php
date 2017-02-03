<?php
/**
 * Document    : namespacemultimodule.php
 * Created on  : ‎‎02/02, 2017, 22:33
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Class NamespaceMultiModule
   * Boilerplate class that combines NamespaceRequestModule and NamespaceProtocolModule.
   *
   * @package sequence\root\module
   */
  abstract class NamespaceMultiModule implements MultiModule {

    use NamespaceRequestModuleTrait;
    use NamespaceProtocolModuleTrait;
  }
}

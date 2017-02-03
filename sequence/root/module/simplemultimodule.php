<?php
/**
 * Document    : simplemultimodule.php
 * Created on  : ‎‎02/02, 2017, 22:25
 * Author      : moltendorf
 * Description :
 */

namespace sequence\root\module {

  /**
   * Class SimpleMultiModule
   * Boilerplate class that combines SimpleRequestModule and SimpleProtocolModule.
   *
   * @package sequence\root\module
   */
  abstract class SimpleMultiModule implements MultiModule {

    use SimpleRequestModuleTrait;
    use SimpleProtocolModuleTrait;
  }
}

<?php

namespace sequence\root {

  use ArrayAccess;
  use Exception;
  use sequence as s;
  use sequence\SQL;

  class Settings implements ArrayAccess {

    use s\Broadcaster;
    use SQL;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = [];

    const SQL_FETCH_SETTINGS          = 0;
    const SQL_UPDATE_SETTING_BY_KEY   = 1;
    const SQL_DELETE_SETTING_BY_KEY   = 2;
    const SQL_CREATE_SETTING_WITH_KEY = 3;

    /**
     * Loaded settings.
     *
     * @var array
     */
    private $container = [];

    /**
     * Previous value of settings.
     *
     * @var array
     */
    private $original = [];

    /**
     * Stored values to push to database.
     *
     * @var array
     */
    private $push = [];

    /**
     * Fetch all settings from the database and register listeners for application close to push updates.
     *
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, $binding = '') {
      $this->bind($root, $binding);

      $application = $root->application;

      foreach ($application->settings as $key => $value) {
        $this->container[$key] = $value;
      }

      if (!$root->database) {
        return;
      }

      $this->buildSQL();

      try {
        foreach ($this->fetch(self::SQL_FETCH_SETTINGS) as $row) {
          $this->container[$row[0]] = $row[1];
        }

        $this->listen([$this, 'pushAll'], 'close', 'application');
      } catch (Exception $exception) {
        $application->errors[] = $exception;
      }
    }

    /**
     * Build all SQL statements.
     */
    private function buildSQL(): void {
      $root     = $this->root;
      $database = $root->database;
      $prefix   = $database->prefix;

      $this->sql = [
        self::SQL_FETCH_SETTINGS => "
          SELECT setting_key, setting_value
          FROM {$prefix}settings",

        self::SQL_UPDATE_SETTING_BY_KEY => "
          UPDATE {$prefix}settings
          SET setting_value = :value
          WHERE setting_key = :key",

        self::SQL_DELETE_SETTING_BY_KEY => "
          DELETE FROM {$prefix}settings
          WHERE setting_key = :key",

        self::SQL_CREATE_SETTING_WITH_KEY => "
          REPLACE INTO {$prefix}settings
            (setting_key, setting_value)
          VALUES
            (:key, :value)"
      ];
    }

    /**
     * Bind all classes in root to application identity.
     *
     * @return string
     */
    protected function getBinding() {
      return 'application';
    }

    /*
     * Implementation of arrayaccess.
     */

    /**
     * Temporarily set the value for this run only.
     *
     * Excessive documentation because this is annoyingly confusing.
     *
     * @param string $offset
     * @param string $value
     */
    public function offsetSet($offset, $value) {
      $offset = (string)$offset;
      $value  = (string)$value;

      // Check if this value has been replaced before.
      if (isset($this->original[$offset])) {
        // Check if there's a stored value and compare the original to the current.
        if (!isset($this->push[$offset]) && $this->original[$offset] === $value) {
          unset($this->original[$offset]); // Remove the backup.
        }
      } else {
        // Check if this value exists.
        if (isset($this->container[$offset])) {
          // Compare the previous to the current.
          if ($this->container[$offset] != $value) {
            $this->original[$offset] = $this->container[$offset]; // Create a backup.
          }
        } else {
          $this->original[$offset] = false; // There is no original value.
        }
      }

      $this->container[$offset] = $value; // Store the current value.
    }

    /**
     * Check if a setting exists.
     *
     * @param string $offset
     *
     * @return boolean
     */
    public function offsetExists($offset) {
      return isset($this->container[(string)$offset]);
    }

    /**
     * Get the current value of a setting.
     *
     * @param string $offset
     *
     * @return string
     */
    public function offsetGet($offset) {
      $offset = (string)$offset;

      return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Temporarily unset the value for this run only.
     *
     * Excessive documentation because this is annoyingly confusing.
     *
     * @param string $offset
     */
    public function offsetUnset($offset) {
      $offset = (string)$offset;

      // Check if there's an original value.
      if (isset($this->original[$offset])) {
        // Check if there's a stored value and check if there was no original.
        if (!isset($this->push[$offset]) && $this->original[$offset] === false) {
          unset($this->original[$offset]); // Remove the backup.
        }

        // Check if a runtime value exists.
        if (isset($this->container[$offset])) {
          unset($this->container[$offset]); // Remove the runtime value.
        }
      } else {
        // Check if a runtime value exists.
        if (isset($this->container[$offset])) {
          $this->original[$offset] = $this->container[$offset]; // Create a backup.

          unset($this->container[$offset]); // Remove the runtime value.
        }
      }
    }

    /*
     * End implementation of arrayaccess.
     */

    /**
     * Permanently store the value in settings.
     *
     * Excessive documentation because this is annoyingly confusing.
     *
     * @param string $offset
     * @param string $value
     * @param bool   $updateCurrent
     */
    public function offsetStore($offset, $value, $updateCurrent = true) {
      $offset = (string)$offset;
      $value  = (string)$value;

      // Are we updating the current runtime value?
      if ($updateCurrent) {
        /*
         * Update the current runtime variable and update the variable to be stored.
         */

        // Check if there's an original value.
        if (isset($this->original[$offset])) {
          // Compare the original to the current.
          if ($this->original[$offset] === $value) {
            unset($this->original[$offset]); // Remove the backup.

            // Check if there's a stored value.
            if (isset($this->push[$offset])) {
              unset($this->push[$offset]); // Remove the stored value.
            }
          } else {
            $this->push[$offset] = $value; // Store the new value.
          }
        } else {
          // Check if this value exists.
          if (isset($this->container[$offset])) {
            // Compare the previous to the current.
            if ($this->container[$offset] !== $value) {
              $this->original[$offset] = $this->container[$offset]; // Create a backup.
              $this->push[$offset]     = $value; // Store the new value.
            }
          } else {
            $this->original[$offset] = false; // There is no original value.
            $this->push[$offset]     = $value; // Store the new value.
          }
        }

        $this->container[$offset] = $value; // Store the current value.
      } else {
        /*
         * Only update the variable to be stored.
         */

        // Check if there's an original value.
        if (isset($this->original[$offset])) {
          // Compare the original to the current.
          if ($this->original[$offset] === $value) {
            // Compare the original to the runtime.
            if (isset($this->container[$offset]) && $this->original[$offset] === $this->container[$offset]) {
              unset($this->original[$offset]); // Remove the backup.
            }

            // Check if there's a stored value.
            if (isset($this->push[$offset])) {
              unset($this->push[$offset]); // Remove the stored value.
            }
          } else {
            $this->push[$offset] = $value; // Store the new value.
          }
        } else {
          // Check if a runtime value exists.
          if (isset($this->container[$offset])) {
            $this->original[$offset] = $this->container[$offset]; // Create a backup.
          } else {
            $this->original[$offset] = false; // There is no original value.
          }

          $this->push[$offset] = $value; // Store the new value.
        }
      }
    }

    /**
     * Permanently delete the value in settings.
     *
     * Excessive documentation because this is annoyingly confusing.
     *
     * @param string $offset
     * @param bool   $updateCurrent
     */
    public function offsetDelete($offset, $updateCurrent = true) {
      $offset = (string)$offset;

      if ($updateCurrent) {
        /*
         * Update the current runtime variable and update the variable to be stored.
         */

        // Check if there's an original value.
        if (isset($this->original[$offset])) {
          // Check if there was no original.
          if ($this->original[$offset] === false) {
            unset($this->original[$offset]); // Remove the backup.

            // Check if there's a stored value.
            if (isset($this->push[$offset])) {
              unset($this->push[$offset]); // Remove the stored value.
            }
          } else {
            $this->push[$offset] = false; // Delete the value.
          }

          // Check if the runtime value exists.
          if (isset($this->container[$offset])) {
            unset($this->container[$offset]); // Remove the current value.
          }
        } else {
          // Check if the runtime value exists.
          if (isset($this->container[$offset])) {
            $this->original[$offset] = $this->container[$offset]; // Create a backup.
            $this->push[$offset]     = false; // Delete the value.

            unset($this->container[$offset]); // Remove the current value.
          }
        }
      } else {
        /*
         * Only update the variable to be stored.
         */

        // Check if there's an original value.
        if (isset($this->original[$offset])) {
          // Check if there was no original.
          if ($this->original[$offset] === false) {
            // Compare the original to the runtime.
            if (!isset($this->container[$offset])) {
              unset($this->original[$offset]); // Remove the backup.
            }

            // Check if there's a stored value.
            if (isset($this->push[$offset])) {
              unset($this->push[$offset]); // Remove the stored value.
            }
          } else {
            $this->push[$offset] = false; // Delete the value.
          }
        } else {
          // Check if a runtime value exists.
          if (isset($this->container[$offset])) {
            $this->original[$offset] = $this->container[$offset]; // Create a backup.
            $this->push[$offset]     = false; // Delete the value.
          }
        }
      }
    }

    /**
     * Push the permanent change to the database.
     *
     * @param string $offset
     */
    public function offsetPush($offset) {
      $offset = (string)$offset;

      if (isset($this->push[$offset])) {
        if ($this->original[$offset] !== false) {
          if ($this->push[$offset] !== false) {
            $this->execute(self::SQL_UPDATE_SETTING_BY_KEY, [
              'key'   => $offset,
              'value' => $this->push[$offset]
            ]);
          } else {
            $this->execute(self::SQL_DELETE_SETTING_BY_KEY, [
              'key' => $offset
            ]);
          }
        } else {
          $this->execute(self::SQL_CREATE_SETTING_WITH_KEY, [
            'key'   => $offset,
            'value' => $this->push[$offset]
          ]);
        }

        if ($this->original[$offset] === $this->container[$offset]) {
          unset($this->original[$offset]);
        }

        unset($this->push[$offset]);
      }
    }

    /**
     * Push all permanent changes to the database.
     *
     * @todo Improve bulk update code.
     */
    public function pushAll() {
      foreach (array_keys($this->push) as $offset) {
        $this->offsetPush($offset);
      }
    }
  }
}

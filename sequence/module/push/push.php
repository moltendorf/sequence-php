<?php

namespace sequence\module\push {

  use sequence as s;
  use sequence\classes\Module;
  use sequence\functions as f;
  use sequence\root\Root;
  use sequence\SQL;

  class Push extends Module {

    use s\Listener;
    use SQL;

    const SQL_FETCH_PUSH_DEVICE                                    = 0;
    const SQL_CREATE_PUSH_DEVICE                                   = 1;
    const SQL_CREATE_PUSH_REGISTRATION                             = 2;
    const SQL_DELETE_PUSH_REGISTRATION_BY_NOTIFICATION_AND_DEVICE  = 3;
    const SQL_DELETE_PUSH_REGISTRATION_BY_NOTIFICATION_AND_CHANNEL = 4;
    const SQL_FETCH_PUSH_NOTIFICATION_BY_TOKEN                     = 5;
    const SQL_FETCH_ENABLED_PUSH_REGISTRATIONS_BY_NOTIFICATION     = 6;
    const SQL_DELETE_PUSH_REGISTRATION_BY_CHANNEL                  = 7;
    const SQL_CREATE_PUSH_ERROR_LOG                                = 8;

    private $response = [];

    public function __construct(Root $root, $binding = '') {
      $this->bind($root, $binding);
      $this->buildSQL();
    }

    /**
     * Build all SQL statements.
     */
    private function buildSQL(): void {
      $root     = $this->root;
      $database = $root->database;
      $prefix   = $database->prefix;

      $this->sql = [
        self::SQL_FETCH_PUSH_DEVICE => "
          SELECT HEX(push_device)
          FROM {$prefix}push_devices
          WHERE push_device = UNHEX(:device)
          LIMIT 1",

        self::SQL_CREATE_PUSH_DEVICE => "
          INSERT INTO {$prefix}push_devices
            (push_device)
          VALUES
            (UNHEX(:device))",

        self::SQL_CREATE_PUSH_REGISTRATION => "
          INSERT INTO {$prefix}push_general
            (push_device, push_notification, push_channel)
          VALUES
            (UNHEX(:device), :notification, :channel)
          ON DUPLICATE KEY UPDATE
            push_device = UNHEX(:device),
            push_channel = :channel",

        self::SQL_DELETE_PUSH_REGISTRATION_BY_NOTIFICATION_AND_DEVICE => "
          DELETE FROM {$prefix}push_general
          WHERE push_device = UNHEX(:device)
            AND push_notification = :notification",

        self::SQL_DELETE_PUSH_REGISTRATION_BY_NOTIFICATION_AND_CHANNEL => "
          DELETE FROM {$prefix}push_general
          WHERE push_notification = :notification
            AND push_channel = :channel",

        self::SQL_FETCH_PUSH_NOTIFICATION_BY_TOKEN => "
          SELECT push_notification
          FROM {$prefix}push_tokens
          WHERE push_token = UNHEX(:token)
          LIMIT 1",

        self::SQL_FETCH_ENABLED_PUSH_REGISTRATIONS_BY_NOTIFICATION => "
          SELECT HEX(push_device), push_channel
          FROM {$prefix}push_general
          WHERE push_notification = :notification
            AND push_enabled = 1",

        self::SQL_DELETE_PUSH_REGISTRATION_BY_CHANNEL => "
          DELETE FROM {$prefix}push_general
          WHERE push_channel = :channel",

        self::SQL_CREATE_PUSH_ERROR_LOG => "
          INSERT INTO {$prefix}push_errors
            (push_status, push_device, push_notification, push_channel, push_request_headers, push_request_body, push_response_headers, push_response_body)
          VALUES
            (:status, UNHEX(:device), :notification, :channel, :request_headers, :request_body, :response_headers, :response_body)"
      ];
    }

    /**
     *
     * @param string $request
     * @param string $request_root
     *
     * @return array|null
     */
    public function request($request, $request_root): ?array {
      $root    = $this->root;
      $handler = $root->handler;

      header('Cache-Control: no-cache, no-store, must-revalidate');
      header('Expires: 0');

      $parts = explode('/', $request);

      $code = 404;

      if (count($parts) === 1) {
        $action = array_shift($parts);

        switch ($action) {
        case 'create':
        case 'register':
        case 'cancel':
        case 'send':
          $code = $this->$action();
        }
      }

      if ($code === 200) {
        $handler->setMethod(function () {
          echo json_encode($this->response);
        });

        $handler->setType('json');
      }

      return [$code];
    }

    private function create() {
      $input = f\file_get_json('php://input');

      if (isset($input['device'])) {
        $rows = $this->fetch(self::SQL_FETCH_PUSH_DEVICE, [
          'device' => $input['device']
        ]);

        if (count($rows)) {
          [$row] = $rows;
          $this->response = $row[0];

          return 200;
        }
      }

      while (!$this->execute(self::SQL_CREATE_PUSH_DEVICE, [
        'device' => $device = bin2hex(openssl_random_pseudo_bytes(144))
      ])) {
        // Everything is handled by the execute.
      }

      $this->response = $device;

      return 200;
    }

    private function register() {
      $input = f\file_get_json('php://input');

      if (!isset($input['notification']) || !isset($input['channel']) || !filter_var($input['channel'], FILTER_VALIDATE_URL)) {
        return 404;
      }

      if (!isset($input['device'])) {
        $input['device'] = null;
      }

      $trusted = 'notify.windows.com';
      $length  = strlen($trusted);

      $host = parse_url($input['channel'], PHP_URL_HOST);

      if (substr($host, -$length) != $trusted) {
        return 404;
      }

      $this->execute(self::SQL_CREATE_PUSH_REGISTRATION, [
        'device'       => $input['device'],
        'notification' => $input['notification'],
        'channel'      => $input['channel']
      ]);

      return 200;
    }

    private function cancel() {
      $input = f\file_get_json('php://input');

      if (!isset($input['notification'])) {
        return 404;
      }

      if (isset($input['device'])) {
        $this->execute(self::SQL_DELETE_PUSH_REGISTRATION_BY_NOTIFICATION_AND_DEVICE, [
          'device'       => $input['device'],
          'notification' => $input['notification']
        ]);
      } else {
        if (isset($input['channel']) && filter_var($input['channel'], FILTER_VALIDATE_URL)) {
          $trusted = 'notify.windows.com';
          $length  = strlen($trusted);

          $host = parse_url($input['channel'], PHP_URL_HOST);

          if (substr($host, -$length) != $trusted) {
            return 404;
          }

          $this->execute(self::SQL_DELETE_PUSH_REGISTRATION_BY_NOTIFICATION_AND_CHANNEL, [
            'notification' => $input['notification'],
            'channel'      => $input['channel']
          ]);
        } else {
          return 404;
        }
      }

      return 200;
    }

    private function send() {
      $input = f\file_get_json('php://input');

      if (!isset($input['token']) || !isset($input['message'])) {
        return 404;
      }

      $rows = $this->fetch(self::SQL_FETCH_PUSH_NOTIFICATION_BY_TOKEN, [
        'token' => $input['token']
      ]);

      if (count($rows)) {
        [$row] = $rows;

        $this->listen(function () use ($row, $input) {
          $this->notify($row[0], $input['message']);
        }, 'close', 'application');

        return 200;
      }

      return 404;
    }

    public function notify($notification, $message) {
      $root     = $this->root;
      $settings = $root->settings;

      $token   = $settings["push_token_slack_$notification"];
      $payload = $settings["push_payload_slack_$notification"];

      if ($token !== null && $payload !== null) {
        $content = 'payload='.json_encode(f\json_decode($payload) + ['text' => $message]);

        $headers = [
          'Content-Length: '.strlen($content)
        ];

        try {
          $context = stream_context_create([
            'http' => [
              'method'           => 'POST',
              'protocol_version' => '1.1',
              'header'           => implode("\r\n", $headers)."\r\n",
              'content'          => $content
            ]
          ]);

          $handle = fopen($token, 'rb', false, $context);

          if ($handle) {
            stream_get_contents($handle); // Discard response.
            fclose($handle);
          }
        } catch (\Exception $exception) {
          // Ignore.
        }
      }

      unset($token);

      $token = $settings['push_token_wns'];

      if ($token === null) {
        $token = $this->getToken();
      }

      if ($token === null) {
        return false;
      }

      $rows = $this->fetch(self::SQL_FETCH_ENABLED_PUSH_REGISTRATIONS_BY_NOTIFICATION, [
        'notification' => $notification
      ]);

      foreach ($rows as $row) {
        push:
        $content = "<toast><visual><binding template=\"ToastText01\"><text id=\"1\">$message</text></binding></visual></toast>";

        $headers = [
          "Authorization: Bearer $token",
          'Content-Length: '.strlen($content),
          'Content-Type: text/xml',
          'X-WNS-Type: wns/toast'
        ];

        try {
          $context = stream_context_create([
            'http' => [
              'method'           => 'POST',
              'protocol_version' => '1.1',
              'header'           => implode("\r\n", $headers)."\r\n",
              'content'          => $content
            ]
          ]);

          $handle = fopen($row[1], 'rb', false, $context);

          if ($handle) {
            $response = stream_get_contents($handle);
            fclose($handle);
          }
        } catch (\Exception $exception) {
          // Ignore.
        }

        $search = 'HTTP/1.1 ';
        $length = strlen($search);

        foreach ($http_response_header as $header) {
          if (substr($header, 0, $length) === $search) {
            $code = substr($header, $length, 3);

            if ($code === "401") {
              $token = $this->getToken();

              goto push;
            } elseif ($code === "410") {
              $this->execute(self::SQL_DELETE_PUSH_REGISTRATION_BY_CHANNEL, [
                'channel' => $row[1]
              ]);
            } elseif ($code !== "200") {
              $this->execute(self::SQL_CREATE_PUSH_ERROR_LOG, [
                'status'           => $code,
                'device'           => $row[0],
                'notification'     => $notification,
                'channel'          => $row[1],
                'request_headers'  => implode("\n", $headers),
                'request_body'     => $content,
                'response_headers' => implode("\n", $http_response_header),
                'response_body'    => isset($response) ? $response : ''
              ]);
            }

            break;
          }
        }
      }

      return true;
    }

    private function getToken() {
      $root     = $this->root;
      $settings = $root->settings;

      $host = 'login.live.com';

      $content = http_build_query([
        'grant_type'    => 'client_credentials',
        'client_id'     => $settings['push_wns_client_id'],
        'client_secret' => $settings['push_wns_client_secret'],
        'scope'         => 'notify.windows.com'
      ]);

      $headers = [
        'Connection: close',
        'Content-Type: application/x-www-form-urlencoded'
      ];

      $context = stream_context_create([
        'http' => [
          'method'           => 'POST',
          'protocol_version' => '1.1',
          'header'           => implode("\r\n", $headers)."\r\n",
          'content'          => $content
        ]
      ]);

      try {
        $json = file_get_contents("https://$host/accesstoken.srf", false, $context);
      } catch (\Exception $exception) {
        $json = false;
      }

      if ($json !== false) {
        $response = f\json_decode($json);

        if (isset($response['access_token'])) {
          $token = $response['access_token'];

          $settings->offsetStore('push_token_wns', $token);

          return $token;
        }
      }

      return null;
    }
  }
}

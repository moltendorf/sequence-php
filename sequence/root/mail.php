<?php

namespace sequence\root {

  use Exception;
  use sequence as s;
  use sequence\functions as f;

  use sequence\SQL;
  use Swift;
  use Swift_Mailer as Mailer;
  use Swift_Message as Message;
  use Swift_Preferences as Preferences;
  use Swift_SmtpTransport as SmtpInfo;
  use Swift_Validate as Validate;

  use League\CommonMark\CommonMarkConverter;

  class Mail {

    use s\Broadcaster;
    use SQL;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = [];

    const SQL_FETCH_USER_BY_EMAIL    = 0;
    const SQL_CREATE_USER_WITH_EMAIL = 1;

    /**
     * Message queue.
     *
     * @var array
     */
    private $queue = [];

    /**
     * Basic constructor.
     *
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, $binding = '') {
      $this->bind($root, $binding);
      $this->buildSQL();

      $this->listen([$this, 'close'], 'close', 'application');

      Swift::init(function () {
        Preferences::getInstance()
                   ->setCharset('utf-8');
      });
    }

    /**
     * Build all SQL statements.
     */
    private function buildSQL(): void {
      $root     = $this->root;
      $database = $root->database;
      $prefix   = $database->prefix;

      $this->sql = [
        self::SQL_FETCH_USER_BY_EMAIL => "
          SELECT user_id, user_name
          FROM {$prefix}users
          WHERE user_email = :user_email
          LIMIT 1",

        self::SQL_CREATE_USER_WITH_EMAIL => "
          INSERT INTO {$prefix}users 
            (user_email)
          VALUES
            (:user_email, :user_name)"

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

    /**
     * Send mail.
     *
     * @param string|array $to
     * @param string       $from
     * @param string       $subject
     * @param string       $message
     * @param array        $options
     *
     * @throws Exception
     */
    public function send($to, $from, $subject, $message, array $options = []) {
      $root     = $this->root;
      $settings = $root->settings;

      if (is_string($to)) {
        $to = [$to];
      }

      foreach ($to as $key => $value) {
        if (!Validate::email(is_string($key) ? $key : $value)) {
          throw new Exception('INVALID_EMAIL_TO');
        }
      }

      $encoded = $settings["email_from_$from"];

      if (isset($encoded)) {
        $decoded = f\json_decode($encoded);

        if (is_array($decoded) && count($decoded)) {
          $from = [];

          foreach ($decoded as $key => $value) {
            if (!Validate::email($value)) {
              throw new Exception('INVALID_EMAIL_FROM');
            }

            if (is_string($key)) {
              $from[$value] = $key;
            } else {
              $from[] = $value;
            }
          }
        }
      } else {
        if (!Validate::email($from)) {
          throw new Exception('INVALID_EMAIL_FROM');
        }
      }

      if (isset($options['replyTo'])) {
        $replyTo = $options['replyTo'];

        if (is_string($replyTo)) {
          $replyTo = [$replyTo];
        }

        foreach ($replyTo as $key => $value) {
          if (!Validate::email(is_string($key) ? $key : $value)) {
            throw new Exception('INVALID_EMAIL_REPLYTO');
          }
        }

        $options['replyTo'] = $replyTo;
      }

      $options += [
        'style'    => ['mail.html'],
        'generate' => true,
        'reason'   => null,
        'replyTo'  => false
      ];

      $this->queue[] = [$to, $from, $subject, $message, $options];
    }

    /**
     * Send mail.
     */
    public function close() {
      $root     = $this->root;
      $template = $root->template;

      if (count($this->queue)) {
        $root     = $this->root;
        $settings = $root->settings;

        switch ($settings['email_transport']) {
        case 'smtp':
          $transport = SmtpInfo::newInstance($settings['email_smtp_host'], (int)$settings['email_smtp_port']);
          $transport->setEncryption($settings['email_smtp_security']);
          $transport->setUsername($settings['email_smtp_username']);
          $transport->setPassword($settings['email_smtp_password']);
          break;

        default:
          throw new Exception('INVALID_EMAIL_TRANSPORT');
        }

        $converter = new CommonMarkConverter();
        $mailer    = Mailer::newInstance($transport);

        $lookup = [];
        $tokens = [];

        /**
         * @param string $to
         * @param string $from
         * @param string $subject
         * @param string $message
         * @param array  $options
         *
         * @throws Exception
         */
        $process = function ($to, $from, $subject, $message, array $options = [])
        use ($template, $converter, $mailer, &$lookup, &$tokens) {
          list($name, $email) = $to;

          if (!isset($lookup[$email])) {
            $rows = $this->fetch(self::SQL_FETCH_USER_BY_EMAIL, [
              'user_email' => $email
            ]);

            if (count($rows)) {
              [$row] = $rows;
              $id = $row[0];

              if (!is_string($name)) {
                $name = $row[1];
              }
            } else {
              $id = $this->insertForId(self::SQL_CREATE_USER_WITH_EMAIL, [
                'user_email' => $email,
                'user_name'  => is_string($name) ? $name : null // Assume a little more data if we can get it.
              ]);
            }

            if ($options['generate']) {
              $token = openssl_random_pseudo_bytes(144);

              $tokens[] = $id;
              $tokens[] = bin2hex($token);
              $tokens[] = $options['reason'];

              $lookup[$email] = [$id, $token];
            } else {
              $lookup[$email] = [$id];
            }
          }

          $to = is_string($name) ? [$email => $name] : $email;

          $envelope = Message::newInstance();
          $envelope->setFrom($from);
          $envelope->setTo($to);
          $envelope->setSubject($subject);
          $envelope->setBody($message);

          if ($options['replyTo']) {
            $envelope->setReplyTo($options['replyTo']);
          }

          ob_start();

          try {
            $template->set([
              'subject' => $subject,
              'message' => $converter->convertToHtml($message)
            ]);

            $template->load($template->file(array_shift($options['style'])), ...$options['style']);

            $envelope->addPart(ob_get_clean(), 'text/html');
          } catch (Exception $exception) {
            ob_end_flush();

            if (s\DEBUG) {
              throw $exception;
            }
          }

          $mailer->send($envelope);
        };

        foreach ($this->queue as $envelope) {
          $to = array_shift($envelope);

          foreach ($to as $key => $value) {
            $process([$key, $value], ...$envelope);
          }
        }

        if ($count = count($tokens)/3) {
          $database = $root->database;
          $prefix   = $database->prefix;
          $pdo      = $database->pdo;

          $query = $pdo->prepare("
            REPLACE INTO {$prefix}email_tokens
              (user_id, token_id, token_reason)
            VALUES
              ".implode(",\n", array_fill(0, $count, '(?, UNHEX(?), ?)'))."
          ");

          $query->execute($tokens);
          $query->closeCursor();
        }
      }
    }
  }
}

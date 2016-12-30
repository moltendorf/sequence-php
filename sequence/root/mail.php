<?php

namespace sequence\root {

  use Exception;
  use sequence as s;
  use sequence\functions as f;

  use Swift;
  use Swift_Mailer as Mailer;
  use Swift_Message as Message;
  use Swift_Preferences as Preferences;
  use Swift_SmtpTransport as SmtpInfo;
  use Swift_Validate as Validate;

  use League\CommonMark\CommonMarkConverter;

  class Mail {

    use s\Broadcaster;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = [];

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

      $this->listen([$this, 'close'], 'close', 'application');

      Swift::init(function () {
        Preferences::getInstance()
                   ->setCharset('utf-8');
      });
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
      $database = $root->database;
      $template = $root->template;
      $prefix   = $database->getPrefix();

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
        use ($database, $template, $prefix, $converter, $mailer, &$lookup, &$tokens) {
          list($name, $email) = $to;

          if (!isset($lookup[$email])) {
            $statement = $database->prepare("
						select user_id, user_name
						from {$prefix}users
						where user_email = :user_email
					");

            $statement->execute([
              'user_email' => $email
            ]);

            $row = $statement->fetch();

            $statement->closeCursor();

            if ($row) {
              $id = $row[0];

              if (!is_string($name)) {
                $name = $row[1];
              }
            } else {
              $statement = $database->prepare("
							insert into {$prefix}users (user_email) values
							(:user_email, :user_name)
						");

              $statement->execute([
                'user_email' => $email,
                'user_name'  => is_string($name) ? $name : null // Assume a little more data if we can get it.
              ]);

              $id = $database->lastInsertId();
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
          $statement = $database->prepare("
						replace into {$prefix}email_tokens (user_id, token_id, token_reason) values
						".implode(",\n", array_fill(0, $count, '(?, UNHEX(?), ?)'))."
					");

          $statement->execute($tokens);
          $statement->closeCursor();
        }
      }
    }
  }
}

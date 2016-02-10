<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 30.10.2015
 * Time: 12:49
 */

namespace Pixidos\GPWebPay\Logger;
use Pixidos\GPWebPay\Exceptions\GPWebPayException;

/**
 * Class Logger
 * @package Pixidos\GPWebPay\Logger
 * @author Ondra Votava <ondra.votava@pixidos.com>
 *
 * @method onMail($this)
 */
class Logger implements ILogger
{
    /** @var string name of the directory where errors should be logged */
    public $directory;
    /** @var string|array email or emails to which send error notifications */
    public $email;
    /** @var string sender of email notifications */
    public $fromEmail;
    /** @var callable handler for sending emails */
    public $onMail;

    public function __construct($directory, $email = NULL)
    {
        $this->directory = $directory;
        $this->email = $email;
       // $this->onMail[] = $this->defaultMailer();
    }
    /**
     * Logs message or exception to file and sends email notification.
     * @param  string|\Exception|\Throwable
     * @param  int  one of constant ILogger::INFO, WARNING, ERROR (sends email), EXCEPTION (sends email), CRITICAL (sends email)
     * @return string logged error filename
     */
    public function log(GPWebPayException $message)
    {
        if (!$this->directory) {
            throw new \LogicException('Directory is not specified.');
        } elseif (!is_dir($this->directory)) {
            throw new \RuntimeException("Directory '$this->directory' is not found or is not directory.");
        }

        $line = $this->formatLogLine($message);
        $file = $this->directory . '/gpwebpay.error.log';
        if (!@file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX)) { // @ is escalated to exception
            throw new \RuntimeException("Unable to write to log file '$file'. Is directory writable?");
        }

        if($this->email) {
            $this->sendEmail($message);
        }
    }
    /**
     * @param  string|\Exception|\Throwable
     * @return string
     */
    protected function formatMessage($message)
    {
       return trim($message);
    }
    /**
     * @param  string|\Exception|\Throwable
     * @return string
     */
    protected function formatLogLine($message, $exceptionFile = NULL)
    {
        return implode(' ', [
            @date('[Y-m-d H-i-s]'), // @ timezone may not be set
            preg_replace('#\s*\r?\n\s*#', ' ', $this->formatMessage($message)),
        ]);
    }

    /**
     * @param  string|\Exception|\Throwable
     * @return void
     */
    protected function sendEmail($message)
    {
        if ($this->email && $this->mailer
            && @filemtime($this->directory . '/email-sent') + $snooze < time() // @ file may not exist
            && @file_put_contents($this->directory . '/email-sent', 'sent') // @ file may not be writable
        ) {
            call_user_func($this->mailer, $message, implode(', ', (array) $this->email));
        }
    }
    /**
     * Default mailer.
     * @param  string|\Exception|\Throwable
     * @param  string
     * @return void
     * @internal
     */
    public function defaultMailer($message, $email)
    {
        $host = preg_replace('#[^\w.-]+#', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : php_uname('n'));
        $parts = str_replace(
            ["\r\n", "\n"],
            ["\n", PHP_EOL],
            [
                'headers' => implode("\n", [
                        'From: ' . ($this->fromEmail ?: "noreply@$host"),
                        'X-Mailer: Tracy',
                        'Content-Type: text/plain; charset=UTF-8',
                        'Content-Transfer-Encoding: 8bit',
                    ]) . "\n",
                'subject' => "PHP: An error occurred on the server $host",
                'body' => $this->formatMessage($message) . "\n\nsource: " . Helpers::getSource(),
            ]
        );
        mail($email, $parts['subject'], $parts['body'], $parts['headers']);
    }
}
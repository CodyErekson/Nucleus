<?php
/**
 * Generate and send email
 * This is a wrapper for SwiftMailer; $this->message exposes all methods available to Swift_Message
 */

namespace Nucleus\Helpers;

class Mailer
{
    protected $container;
    protected $path = "/email/";

    public $message = null;

    public $html = null;
    public $text = null;

    /**
     * Mailer constructor.
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
        $this->message = $this->container->email;
        //$this->path = "/" . getenv('TEMPLATE') . "/email/";
    }

    /**
     * Parse defined template file using templating engine
     * @param string $type
     * @param $file
     * @param array $arguments
     * @return bool
     */
    private function parseTemplate($type = 'html', $file, $arguments = [])
    {
        // Get file path
        if ( !$filepath = realpath(__DIR__ . '/../View/templates/' . getenv('TEMPLATE') . $this->path . $file) ){
            $this->$type = null;
            return false;
        }

        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\n" . $filepath);

        if ( !empty($arguments) ){
            // Parse the template
            $this->$type = $this->container->view->fetch($this->path . $file, $arguments);
        } else {
            // Read contents of template file if we don't have any arguments to parse
            if (!$text = file_get_contents($filepath)) {
                return false;
            }
            $this->$type = $text;
        }

        return true;
    }

    /**
     * Specify HTML type for template parsing
     * @param $file
     * @param array $arguments
     * @return bool
     */
    public function htmlTemplate($file, $arguments = [])
    {
        return $this->parseTemplate('html', $file, $arguments);
    }

    /**
     * Specify text type for template parsing
     * @param $file
     * @param array $arguments
     * @return bool
     */
    public function textTemplate($file, $arguments = [])
    {
        return $this->parseTemplate('text', $file, $arguments);
    }

    /**
     * Set the HTML body
     * @param $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Retrieve HTML body
     * @return null|string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set the text body
     * @param $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Retrieve text body
     * @return null|string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Use given CSS file and create inline style tags for HTML body
     * @param $css_file
     * @return bool
     */
    public function inlineStyle($css_file)
    {
        if ( !$filepath = realpath(__DIR__ . '/../View/templates/' . getenv('TEMPLATE') . $this->path . $css_file) ){
            return false;
        }

        $css = file_get_contents($filepath);

        $emogrifier = $this->container->emogrifier;
        $emogrifier->setHtml($this->html);
        $emogrifier->setCss($css);

        $this->html = $emogrifier->emogrify();
        return true;
    }

    /**
     * Send the email
     * @return mixed
     */
    public function send()
    {
        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nHTML: " . $this->html);
        $this->container['debug.log']->debug(__FILE__ . " on line " . __LINE__ . "\nText: " . $this->text);
        if ( ( !is_null($this->html) ) && ( !is_null($this->text) ) ){
            // We have both HTML and text formats
            $this->message->setBody($this->html, 'text/html');
            $this->message->addPart($this->text, 'text/plain');
        } else if ( ( !is_null($this->html) ) && ( is_null($this->text) ) ){
            // We have both only HTML
            $this->message->setBody($this->html, 'text/html');
        } else if ( ( is_null($this->html) ) && ( !is_null($this->text) ) ){
            // We have both only text
            $this->message->setBody($this->text, 'text/plain');
        }

        return $this->container->transport->send($this->message);
    }
}

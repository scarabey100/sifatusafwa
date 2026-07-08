<?php
/**
 * GIFT CARD
 *
 * @author    EIRL Timactive De Véra
 * @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 * @license   Commercial license
 *
 * @category pricing_promotion
 *
 * @version 1.1.0
 *************************************
 **         GIFT CARD                *
 **          V 1.0.0                 *
 *************************************
 * +
 * + Languages: EN, FR, ES
 * + PS version: 1.5,1.6,1.7
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
    // nothing to do
} elseif (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
    include_once _PS_SWIFT_DIR_ . 'swift_required.php';
} else {
    include_once _PS_SWIFT_DIR_ . 'Swift.php';
    include_once _PS_SWIFT_DIR_ . 'Swift/Connection/SMTP.php';
    include_once _PS_SWIFT_DIR_ . 'Swift/Connection/NativeMail.php';
    include_once _PS_SWIFT_DIR_ . 'Swift/Plugin/Decorator.php';
}

class MailGiftCard
{
    const TYPE_HTML = 1;

    const TYPE_TEXT = 2;

    const TYPE_BOTH = 3;

    /**
     * Send Email
     *
     * @param int $id_lang
     *                     Language of the email (to translate the template)
     * @param string $template
     *                         Template: the name of template not be a var but a string !
     * @param string $subject
     * @param string $template_vars
     * @param string $to
     * @param string $to_name
     * @param string $from
     * @param string $from_name
     * @param array $file_attachment
     * @param bool $modeSMTP
     * @param string $template_path
     * @param bool $die
     * @param string $bcc
     *                    Bcc recipient
     */
    public static function send(
        $id_lang,
        $template,
        $subject,
        $template_vars,
        $to,
        $to_name = null,
        $from = null,
        $from_name = null,
        $file_attachment = null,
        $mode_smtp = null,
        $template_path = _PS_MAIL_DIR_,
        $die = false,
        $id_shop = null,
        $bcc = null,
        $giftcard_image = null
    ) {
        $configuration = Configuration::getMultiple([
            'PS_SHOP_EMAIL',
            'PS_MAIL_METHOD',
            'PS_MAIL_SERVER',
            'PS_MAIL_USER',
            'PS_MAIL_PASSWD',
            'PS_SHOP_NAME',
            'PS_MAIL_SMTP_ENCRYPTION',
            'PS_MAIL_SMTP_PORT',
            'PS_MAIL_TYPE',
        ], null, null, $id_shop);
        // Returns immediatly if emails are deactivated
        if ($configuration['PS_MAIL_METHOD'] == 3) {
            return true;
        }
        $theme_path = _PS_THEME_DIR_;
        // Get the path of theme by id_shop if exist
        if (is_numeric($id_shop) && $id_shop) {
            $shop = new Shop((int) $id_shop);
            $theme_name = '';
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $theme_name = $shop->theme->getName();
            } else {
                $theme_name = $shop->getTheme();
            }
            if (_THEME_NAME_ != $theme_name) {
                $theme_path = _PS_ROOT_DIR_ . '/themes/' . $theme_name . '/';
            }
        }
        if (version_compare(_PS_VERSION_, '1.6.1.5', '<') && !isset($configuration['PS_MAIL_SMTP_ENCRYPTION'])) {
            $configuration['PS_MAIL_SMTP_ENCRYPTION'] = 'off';
        }
        if (version_compare(_PS_VERSION_, '1.6.1.5', '>=')
            && (!isset($configuration['PS_MAIL_SMTP_ENCRYPTION'])
                || $configuration['PS_MAIL_SMTP_ENCRYPTION'] === 'off')) {
            $configuration['PS_MAIL_SMTP_ENCRYPTION'] = false;
        }
        if (!isset($configuration['PS_MAIL_SMTP_PORT'])) {
            $configuration['PS_MAIL_SMTP_PORT'] = 'default';
        }
        if (!isset($from) || !Validate::isEmail($from)) {
            $from = $configuration['PS_SHOP_EMAIL'];
        }
        if (!Validate::isEmail($from)) {
            $from = null;
        }
        // $from_name is not that important, no need to die if it is not valid
        if (!isset($from_name) || !Validate::isMailName($from_name)) {
            $from_name = $configuration['PS_SHOP_NAME'];
        }
        if (!Validate::isMailName($from_name)) {
            $from_name = null;
        }
        if (!is_array($to) && !Validate::isEmail($to)) {
            Tools::dieOrLog(Tools::displayError('Error: parameter "to" is corrupted'), $die);

            return false;
        }
        if (!is_array($template_vars)) {
            $template_vars = [];
        }
        // Do not crash for this error, that may be a complicated customer name
        if (is_string($to_name) && !empty($to_name) && !Validate::isMailName($to_name)) {
            $to_name = null;
        }
        if (!Validate::isTplName($template)) {
            Tools::dieOrLog(Tools::displayError('Error: invalid e-mail template'), $die);

            return false;
        }
        if (!Validate::isMailSubject($subject)) {
            Tools::dieOrLog(Tools::displayError('Error: invalid e-mail subject'), $die);

            return false;
        }
        /* Construct multiple recipients list if needed */
        $message = null;
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            $message = new Swift_Message();
        } elseif (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
            $message = Swift_Message::newInstance();
        } else {
            $message = new Swift_RecipientList();
        }
        if (is_array($to) && isset($to)) {
            foreach ($to as $key => $addr) {
                $addr = trim($addr);
                if (!Validate::isEmail($addr)) {
                    Tools::dieOrLog(Tools::displayError('Error: invalid e-mail address'), $die);

                    return false;
                }
                if (is_array($to_name)) {
                    if ($to_name && is_array($to_name) && Validate::isGenericName($to_name[$key])) {
                        $to_name = $to_name[$key];
                    }
                }
                if ($to_name == null || $to_name == $addr) {
                    $to_name = '';
                } else {
                    if (function_exists('mb_encode_mimeheader')) {
                        $to_name = mb_encode_mimeheader($to_name, 'utf-8');
                    } else {
                        $to_name = self::mimeEncode($to_name);
                    }
                }
                $message->addTo($addr, $to_name);
            }
            $to_plugin = $to[0];
        } else {
            /* Simple recipient, one address */
            $to_plugin = $to;
            if ($to_name == null || $to_name == $to) {
                $to_name = '';
            } else {
                if (function_exists('mb_encode_mimeheader')) {
                    $to_name = mb_encode_mimeheader($to_name, 'utf-8');
                } else {
                    $to_name = self::mimeEncode($to_name);
                }
            }
            $message->addTo($to, $to_name);
        }
        if (isset($bcc)) {
            $message->addBcc($bcc);
        }
        try {
            /* Connect with the appropriate configuration */
            if ($configuration['PS_MAIL_METHOD'] == 2) {
                if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT'])) {
                    Tools::dieOrLog(Tools::displayError('Error: invalid SMTP server or SMTP port'), $die);

                    return false;
                }
                $connection = null;
                if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                    $connection = (new Swift_SmtpTransport(
                        $configuration['PS_MAIL_SERVER'],
                        $configuration['PS_MAIL_SMTP_PORT'],
                        $configuration['PS_MAIL_SMTP_ENCRYPTION']
                    ))
                        ->setUsername($configuration['PS_MAIL_USER'])
                        ->setPassword($configuration['PS_MAIL_PASSWD']);
                } elseif (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
                    $connection = Swift_SmtpTransport::newInstance(
                        $configuration['PS_MAIL_SERVER'],
                        $configuration['PS_MAIL_SMTP_PORT'],
                        $configuration['PS_MAIL_SMTP_ENCRYPTION']
                    )->setUsername($configuration['PS_MAIL_USER'])->setPassword($configuration['PS_MAIL_PASSWD']);
                } else {
                    $connection = new Swift_Connection_SMTP(
                        $configuration['PS_MAIL_SERVER'],
                        $configuration['PS_MAIL_SMTP_PORT'],
                        $configuration['PS_MAIL_SMTP_ENCRYPTION'] == 'ssl' ?
                            Swift_Connection_SMTP::ENC_SSL :
                            ($configuration['PS_MAIL_SMTP_ENCRYPTION'] == 'tls' ?
                                Swift_Connection_SMTP::ENC_TLS :
                                Swift_Connection_SMTP::ENC_OFF)
                    );
                    $connection->setTimeout(4);
                    if (!$connection) {
                        return false;
                    }
                    if (!empty($configuration['PS_MAIL_USER'])) {
                        $connection->setUsername($configuration['PS_MAIL_USER']);
                    }
                    if (!empty($configuration['PS_MAIL_PASSWD'])) {
                        $connection->setPassword($configuration['PS_MAIL_PASSWD']);
                    }
                }
            } else {
                if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                    $connection = new Swift_SendmailTransport();
                } elseif (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
                    $connection = Swift_MailTransport::newInstance();
                } else {
                    $connection = new Swift_Connection_NativeMail();
                }
            }
            if (!$connection) {
                return false;
            }
            $swift = null;
            if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                $swift = new Swift_Mailer($connection);
            } elseif (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
                $swift = Swift_Mailer::newInstance($connection);
            } else {
                $swift = new Swift($connection, Configuration::get('PS_MAIL_DOMAIN', null, null, $id_shop));
            }
            /* Get templates content */
            $iso = Language::getIsoById((int) $id_lang);
            if (!$iso) {
                Tools::dieOrLog(Tools::displayError('Error - No ISO code for email'), $die);

                return false;
            }
            $template_name = $template;
            $template = $iso . '/' . $template;
            $module_name = false;
            $override_mail = false;
            // get templatePath
            if (preg_match('#' . __PS_BASE_URI__ . 'modules/#',
                str_replace(DIRECTORY_SEPARATOR, '/', $template_path))
                && preg_match('#modules/([a-z0-9_-]+)/#ui', str_replace(DIRECTORY_SEPARATOR, '/', $template_path), $res)) {
                $module_name = $res[1];
            }
            if ($module_name !== false
                && (file_exists($theme_path . 'modules/' . $module_name . '/mails/' . $template . '.txt')
                    || file_exists($theme_path . 'modules/' . $module_name . '/mails/' . $template . '.html'))) {
                $template_path = $theme_path . 'modules/' . $module_name . '/mails/';
            } elseif (file_exists($theme_path . 'mails/' . $template . '.txt')
                || file_exists($theme_path . 'mails/' . $template . '.html')) {
                $template_path = $theme_path . 'mails/';
                $override_mail = true;
            }
            if (!file_exists($template_path . $template . '.txt')
                && ($configuration['PS_MAIL_TYPE'] == self::TYPE_BOTH
                    || $configuration['PS_MAIL_TYPE'] == self::TYPE_TEXT)) {
                Tools::dieOrLog(Tools::displayError('Error - The following e-mail template is missing:') . ' ' .
                    $template_path . $template . '.txt', $die);

                return false;
            } else {
                if (!file_exists($template_path . $template . '.html')
                    && ($configuration['PS_MAIL_TYPE'] == self::TYPE_BOTH
                        || $configuration['PS_MAIL_TYPE'] == self::TYPE_HTML)) {
                    Tools::dieOrLog(Tools::displayError('Error - The following e-mail template is missing:') . ' ' .
                        $template_path . $template . '.html', $die);

                    return false;
                }
            }
            $template_html = Tools::file_get_contents($template_path . $template . '.html');
            $template_txt = strip_tags(html_entity_decode(
                Tools::file_get_contents($template_path . $template . '.txt'),
                null,
                'utf-8'
            ));
            if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
                if ($override_mail && file_exists($template_path . $iso . '/lang.php')) {
                    include_once $template_path . $iso . '/lang.php';
                } else {
                    if ($module_name && file_exists($theme_path . 'mails/' . $iso . '/lang.php')) {
                        include_once $theme_path . 'mails/' . $iso . '/lang.php';
                    } else {
                        if (file_exists(_PS_MAIL_DIR_ . $iso . '/lang.php')) {
                            include_once _PS_MAIL_DIR_ . $iso . '/lang.php';
                        } else {
                            Tools::dieOrLog(
                                Tools::displayError('Error - The lang file is missing for :') . ' ' . $iso,
                                $die
                            );

                            return false;
                        }
                    }
                }
            }
            /* Create mail and attach differents parts */
            if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                $message = new Swift_Message(
                    '[' . Configuration::get('PS_SHOP_NAME', null, null, $id_shop) . '] ' . $subject
                );
            } else {
                $message->setSubject($subject);
            }
            // If prestashop 8.0.0 add DKIM configuration in configuration
            if (version_compare(_PS_VERSION_, '8.0.0', '>=')
                && (int) Configuration::get('PS_MAIL_DKIM_ENABLE', null, null, $id_shop) === 1) {
                $configuration['PS_MAIL_DKIM_KEY'] = Configuration::get('PS_MAIL_DKIM_KEY', null, null, $id_shop);
                $configuration['PS_MAIL_DKIM_SELECTOR'] = Configuration::get('PS_MAIL_DKIM_SELECTOR', null, null, $id_shop);
                $configuration['PS_MAIL_DKIM_DOMAIN'] = Configuration::get('PS_MAIL_DKIM_DOMAIN', null, null, $id_shop);
                $signer = new Swift_Signers_DKIMSigner(
                    $configuration['PS_MAIL_DKIM_KEY'],
                    $configuration['PS_MAIL_DKIM_DOMAIN'],
                    $configuration['PS_MAIL_DKIM_SELECTOR']
                );
                $message->attachSigner($signer);
            }
            $message->setCharset('utf-8');
            /* Set Message-ID - getmypid() is blocked on some hosting */
            $message->setId(self::generateId());

            if (version_compare(_PS_VERSION_, '1.6.1.0', '>')) {
                $message->setReplyTo($from, null);
            }

            if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                $message->headers->setEncoding('Q');
            }
            if (version_compare(_PS_VERSION_, '1.7.7.0', '<')
                && version_compare(_PS_VERSION_, '1.6.0.4', '>=')) {
                $template_vars = array_map(
                    [
                        'Tools',
                        'htmlentitiesDecodeUTF8',
                    ],
                    $template_vars
                );
                $template_vars = array_map(
                    [
                        'Tools',
                        'stripslashes',
                    ],
                    $template_vars
                );
            }
            if (Configuration::get('PS_LOGO_MAIL') !== false
                && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $id_shop))) {
                $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $id_shop);
            } else {
                if (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop))) {
                    $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop);
                } else {
                    $template_vars['{shop_logo}'] = '';
                }
            }
            if (version_compare(_PS_VERSION_, '1.5.5.0') >= 0) {
                ShopUrl::cacheMainDomainForShop((int) $id_shop);
            }
            /* don't attach the logo as */
            if (isset($logo)) {
                if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                    $template_vars['{shop_logo}'] = $message->attach(
                        new Swift_Message_EmbeddedFile(
                            new Swift_File($logo),
                            null,
                            self::getMimeTypeByExtension($logo)
                        )
                    );
                } else {
                    $template_vars['{shop_logo}'] = $message->embed(Swift_Image::fromPath($logo));
                }
            }
            if ((Context::getContext()->link instanceof Link) === false) {
                Context::getContext()->link = new Link();
            }
            if (isset($giftcard_image)) {
                if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                    $template_vars['{giftcard_image}'] = $message->attach(
                        new Swift_Message_EmbeddedFile(
                            new Swift_File($giftcard_image),
                            null,
                            self::getMimeTypeByExtension($giftcard_image)
                        )
                    );
                } else {
                    $template_vars['{giftcard_image}'] = $message->embed(Swift_Image::fromPath($giftcard_image));
                }
            }
            $template_vars['{shop_name}'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $id_shop));
            $template_vars['{shop_url}'] = Context::getContext()->link->getPageLink(
                'index',
                true,
                $id_lang,
                null,
                false,
                $id_shop
            );
            $template_vars['{my_account_url}'] = Context::getContext()->link->getPageLink(
                'my-account',
                true,
                $id_lang,
                null,
                false,
                $id_shop
            );
            $template_vars['{guest_tracking_url}'] = Context::getContext()->link->getPageLink(
                'guest-tracking',
                true,
                $id_lang,
                null,
                false,
                $id_shop
            );
            $template_vars['{history_url}'] = Context::getContext()->link->getPageLink(
                'history',
                true,
                $id_lang,
                null,
                false,
                $id_shop
            );
            $template_vars['{color}'] = Tools::safeOutput(
                Configuration::get('PS_MAIL_COLOR', null, null, $id_shop)
            );
            if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                $swift->attachPlugin(new Swift_Plugin_Decorator([
                    $to_plugin => $template_vars,
                ]), 'decorator');
            } else {
                $swift->registerPlugin(new Swift_Plugins_DecoratorPlugin([
                    self::toPunycode($to_plugin) => $template_vars,
                ]));
            }
            if ($configuration['PS_MAIL_TYPE'] == self::TYPE_BOTH
                || $configuration['PS_MAIL_TYPE'] == self::TYPE_TEXT) {
                if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                    $message->attach(new Swift_Message_Part($template_txt, 'text/plain', '8bit', 'utf-8'));
                } else {
                    $message->addPart($template_txt, 'text/plain', 'utf-8');
                }
            }
            if ($configuration['PS_MAIL_TYPE'] == self::TYPE_BOTH
                || $configuration['PS_MAIL_TYPE'] == self::TYPE_HTML) {
                if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                    $message->attach(new Swift_Message_Part($template_html, 'text/html', '8bit', 'utf-8'));
                } else {
                    $message->addPart($template_html, 'text/html', 'utf-8');
                }
            }
            if ($file_attachment && !empty($file_attachment)) {
                // Multiple attachments?
                if (!is_array(current($file_attachment))) {
                    $file_attachment = [
                        $file_attachment,
                    ];
                }
                foreach ($file_attachment as $attachment) {
                    if (isset($attachment['content']) && isset($attachment['name']) && isset($attachment['mime'])) {
                        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                            $message->attach(
                                (new Swift_Attachment())->setFilename(
                                    $attachment['name']
                                )->setContentType($attachment['mime'])
                                    ->setBody($attachment['content'])
                            );
                        } elseif (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                            $message->attach(new Swift_Message_Attachment(
                                $attachment['content'],
                                $attachment['name'],
                                $attachment['mime']
                            ));
                        } else {
                            $message->attach(Swift_Attachment::newInstance()->setFilename($attachment['name'])
                                ->setContentType($attachment['mime'])
                                ->setBody($attachment['content']));
                        }
                    }
                }
            }
            /* Send mail */

            if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
                $send = $swift->send($message, $to, new Swift_Address($from, $from_name));
                $swift->disconnect();
            } else {
                $message->setFrom([
                    $from => $from_name,
                ]);
                $send = $swift->send($message);
            }
            if (version_compare(_PS_VERSION_, '1.5.5.0') >= 0) {
                ShopUrl::resetMainDomainCache();
            }
            if (version_compare(_PS_VERSION_, '1.6.1.5', '>=')) {
                if ($send && Configuration::get('PS_LOG_EMAILS')) {
                    $mail = new Mail();
                    $mail->template = $template_name;
                    $mail->subject = Tools::substr($subject, 0, 255);
                    $mail->id_lang = (int) $id_lang;
                    $recipientsTo = $message->getTo();
                    $recipientsCc = $message->getCc();
                    $recipientsBcc = $message->getBcc();
                    if (!is_array($recipientsTo)) {
                        $recipientsTo = [];
                    }
                    if (!is_array($recipientsCc)) {
                        $recipientsCc = [];
                    }
                    if (!is_array($recipientsBcc)) {
                        $recipientsBcc = [];
                    }
                    foreach (array_merge($recipientsTo, $recipientsCc, $recipientsBcc) as $email => $recipient_name) {
                        /* @var Swift_Address $recipient */
                        $mail->id = null;
                        $mail->recipient = Tools::substr($email, 0, 255);
                        $mail->add();
                    }
                }
                if (version_compare(_PS_VERSION_, '1.5.5.0') >= 0) {
                    ShopUrl::resetMainDomainCache();
                }
            }

            return $send;
        } catch (Exception $e) {
            if (version_compare(_PS_VERSION_, '1.6.0.2', '>')) {
                PrestaShopLogger::addLog(
                    'Swift Error: ' . $e->getMessage(),
                    3,
                    null,
                    'Swift_Message'
                );
            } else {
                echo $e->getMessage();
            }

            return false;
        }
    }

    /**
     * Automatically convert email to Punycode.
     *
     * Try to use INTL_IDNA_VARIANT_UTS46 only if defined, else use INTL_IDNA_VARIANT_2003
     * See https://wiki.php.net/rfc/deprecate-and-remove-intl_idna_variant_2003
     *
     * @param string $to Email address
     *
     * @return string
     */
    public static function toPunycode($to)
    {
        $address = explode('@', $to);
        if (empty($address[0]) || empty($address[1])) {
            return $to;
        }

        if (defined('INTL_IDNA_VARIANT_UTS46')) {
            return $address[0] . '@' . idn_to_ascii($address[1], 0, INTL_IDNA_VARIANT_UTS46);
        }

        /*
         * INTL_IDNA_VARIANT_2003 const will be removed in PHP 8.
         * See https://wiki.php.net/rfc/deprecate-and-remove-intl_idna_variant_2003
         */
        if (defined('INTL_IDNA_VARIANT_2003')) {
            return $address[0] . '@' . idn_to_ascii($address[1], 0, INTL_IDNA_VARIANT_2003);
        }

        return $address[0] . '@' . idn_to_ascii($address[1]);
    }

    public static function sendMailTest(
        $smtpChecked,
        $smtpServer,
        $content,
        $subject,
        $type,
        $to,
        $from,
        $smtpLogin,
        $smtpPassword,
        $smtpPort = 25,
        $smtpEncryption = 'tls'
    ) {
        $swift = null;
        $result = false;
        try {
            if ($smtpChecked) {
                $smtp = new Swift_Connection_SMTP($smtpServer, $smtpPort, ($smtpEncryption == 'off')
                    ? Swift_Connection_SMTP::ENC_OFF : (($smtpEncryption == 'tls') ?
                        Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_SSL));
                $smtp->setUsername($smtpLogin);
                $smtp->setpassword($smtpPassword);
                $smtp->setTimeout(5);
                $swift = new Swift($smtp, Configuration::get('PS_MAIL_DOMAIN'));
            } else {
                $swift = new Swift(new Swift_Connection_NativeMail(), Configuration::get('PS_MAIL_DOMAIN'));
            }
            $message = new Swift_Message($subject, $content, $type);

            if ($swift->send($message, $to, $from)) {
                $result = true;
            }

            $swift->disconnect();
        } catch (Swift_ConnectionException $e) {
            $result = $e->getMessage();
        } catch (Swift_Message_MimeException $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /* Rewrite of Swift_Message::generateId() without getmypid() */
    protected static function generateId($idstring = null)
    {
        $date = new DateTime(null, new DateTimeZone('UTC'));
        $midparams = [
            'utctime' => $date->format('YmdHis'),
            'randint' => mt_rand(),
            'customstr' => (preg_match('/^(?<!\\.)[a-z0-9\\.]+(?!\\.)\$/iD', $idstring) ? $idstring : 'swift'),
            'hostname' => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname('n')),
        ];
        if (version_compare(_PS_VERSION_, '1.6.1.5', '<')) {
            return vsprintf('<%s.%d.%s@%s>', $midparams);
        }

        return vsprintf('%s.%d.%s@%s', $midparams);
    }

    public static function isMultibyte($data)
    {
        $length = Tools::strlen($data);
        for ($i = 0; $i < $length; ++$i) {
            $result = ord($data[$i]);
            if ($result > 128) {
                return true;
            }
        }

        return false;
    }

    public static function mimeEncode($string, $charset = 'UTF-8', $newline = "\r\n")
    {
        if (!self::isMultibyte($string) && Tools::strlen($string) < 75) {
            return $string;
        }
        $charset = Tools::strtoupper($charset);
        $start = '=?' . $charset . '?B?';
        $end = '?=';
        $sep = $end . $newline . ' ' . $start;
        $length = 75 - Tools::strlen($start) - Tools::strlen($end);
        $length -= ($length % 4);
        if ($charset === 'UTF-8') {
            $parts = [];
            $maxchars = floor(($length * 3) / 4);
            $stringLength = Tools::strlen($string);

            while ($stringLength > $maxchars) {
                $i = (int) $maxchars;
                $result = ord($string[$i]);

                while ($result >= 128 && $result <= 191) {
                    --$i;
                    $result = ord($string[$i]);
                }
                $parts[] = base64_encode(Tools::substr($string, 0, $i)); // viewed with Francois Gaillard & Emmanuel
                $string = Tools::substr($string, $i);
                $stringLength = Tools::strlen($string);
            }
            $parts[] = base64_encode($string); // viewed with Francois Gaillard & Emmanuel
            $string = implode($sep, $parts);
        } else {
            $string = chunk_split(base64_encode($string), $length, $sep); // viewed with Francois Gaillard & Emmanuel
            $string = preg_replace('/' . preg_quote($sep) . '$/', '', $string);
        }

        return $start . $string . $end;
    }

    public static function getMimeTypeByExtension($file_name)
    {
        $types = [
            'image/gif' => [
                'gif',
            ],
            'image/jpeg' => [
                'jpg',
                'jpeg',
            ],
            'image/png' => [
                'png',
            ],
        ];
        $extension = Tools::substr($file_name, strrpos($file_name, '.') + 1);
        $mime_type = null;
        foreach ($types as $mime => $exts) {
            if (in_array($extension, $exts)) {
                $mime_type = $mime;
                break;
            }
        }
        if ($mime_type === null) {
            $mime_type = 'image/jpeg';
        }

        return $mime_type;
    }
}

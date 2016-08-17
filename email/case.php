<?php

/**
 * Configures the SMTP server
 */
use xlz\SMTP\Email;
class TestCase
{
    /** SMTP server to test against */
    const SERVER = 'smtp.163.com';
    /** plain text port */
    const PORT = 25;
    /** TLS port */
    const PORT_TLS = 465;
    /** SSL port (not supported by mailtrap currently */
    const PORT_SSL = 25;
    /** user for LOGIN auth */
    const USER = 'xlglmm@163.com';
    /** password for LOGIN auth */
    const PASS = '';

    /** from */
    const FROM_NAME = 'xlglmm';
    const FROM_EMAIL = 'xlglmm@163.com';
    /** to */
    const TO_NAME = 'phpapi';
    const TO_EMAIL = 'phpapi@163.com';

    /** delay in microsends between SMTP tests to avoid API limits (we're allowed two messages/second) */
    const DELAY = 500000; // half a second
}


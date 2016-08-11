<?php

/**
 * Configures the SMTP server
 */
use email\SMTP\Email;
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
    const USER = '';
    /** password for LOGIN auth */
    const PASS = '';

    /** from */
    const FROM_NAME = '';
    const FROM_EMAIL = '';
    /** to */
    const TO_NAME = '';
    const TO_EMAIL = '';

    /** delay in microsends between SMTP tests to avoid API limits (we're allowed two messages/second) */
    const DELAY = 500000; // half a second
}


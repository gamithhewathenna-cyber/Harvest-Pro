<?php
/**
 * Minimal SMTP client (no external dependencies - this app is deployed by
 * uploading plain PHP files to shared hosting, no Composer step). Supports
 * plain/SSL/STARTTLS + AUTH LOGIN, which covers typical providers (Gmail,
 * SendGrid, Mailgun, and most cPanel mail servers).
 */

function get_email_settings() {
    $row = db()->query('SELECT * FROM email_settings WHERE id=1')->fetch();
    return $row ?: null;
}

function save_email_settings($data) {
    $db = db();
    $exists = $db->query('SELECT 1 FROM email_settings WHERE id=1')->fetchColumn();
    if ($exists) {
        $db->prepare("UPDATE email_settings SET smtp_host=?, smtp_port=?, smtp_user=?, smtp_pass=?, from_email=?, from_name=?, encryption=? WHERE id=1")
           ->execute([$data['smtp_host'], $data['smtp_port'], $data['smtp_user'], $data['smtp_pass'], $data['from_email'], $data['from_name'], $data['encryption']]);
    } else {
        $db->prepare("INSERT INTO email_settings (id,smtp_host,smtp_port,smtp_user,smtp_pass,from_email,from_name,encryption) VALUES (1,?,?,?,?,?,?,?)")
           ->execute([$data['smtp_host'], $data['smtp_port'], $data['smtp_user'], $data['smtp_pass'], $data['from_email'], $data['from_name'], $data['encryption']]);
    }
}

function smtp_read($fp) {
    $data = '';
    while (($line = fgets($fp, 515)) !== false) {
        $data .= $line;
        if (isset($line[3]) && $line[3] === ' ') break; // final line of a (possibly multi-line) reply
    }
    return $data;
}
function smtp_expect($fp, $expected) {
    $resp = smtp_read($fp);
    $code = (int)substr($resp, 0, 3);
    if (!in_array($code, (array)$expected, true)) {
        throw new RuntimeException("SMTP error: ".trim($resp));
    }
    return $resp;
}
function smtp_cmd($fp, $cmd, $expected) {
    fwrite($fp, $cmd."\r\n");
    return smtp_expect($fp, $expected);
}

/** Sends a plain-text email using the saved SMTP settings. Throws on failure. */
function send_email($to, $subject, $body) {
    $cfg = get_email_settings();
    if (!$cfg || !$cfg['smtp_host'] || !$cfg['from_email']) {
        throw new RuntimeException('Email is not configured yet - fill in SMTP settings first.');
    }

    $host = $cfg['smtp_host']; $port = (int)$cfg['smtp_port'];
    $transport = $cfg['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
    $fp = @stream_socket_client($transport.$host.':'.$port, $errno, $errstr, 15);
    if (!$fp) throw new RuntimeException("Couldn't connect to $host:$port - $errstr");
    stream_set_timeout($fp, 15);

    $localDomain = defined('APP_DOMAIN') ? APP_DOMAIN : 'localhost';

    try {
        smtp_expect($fp, [220]);
        smtp_cmd($fp, "EHLO $localDomain", [250]);

        if ($cfg['encryption'] === 'tls') {
            smtp_cmd($fp, "STARTTLS", [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS negotiation failed');
            }
            smtp_cmd($fp, "EHLO $localDomain", [250]); // must re-greet after the TLS upgrade
        }

        if (!empty($cfg['smtp_user'])) {
            smtp_cmd($fp, "AUTH LOGIN", [334]);
            smtp_cmd($fp, base64_encode($cfg['smtp_user']), [334]);
            smtp_cmd($fp, base64_encode($cfg['smtp_pass']), [235]);
        }

        smtp_cmd($fp, "MAIL FROM:<{$cfg['from_email']}>", [250]);
        smtp_cmd($fp, "RCPT TO:<$to>", [250, 251]);
        smtp_cmd($fp, "DATA", [354]);

        $fromName = $cfg['from_name'] ?: $cfg['from_email'];
        $headers = "From: $fromName <{$cfg['from_email']}>\r\n"
                 . "To: <$to>\r\n"
                 . "Subject: $subject\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/plain; charset=utf-8\r\n"
                 . "Date: ".date('r')."\r\n\r\n";
        // Dot-stuff any line that starts with a lone "." per RFC 5321
        $escapedBody = preg_replace('/^\./m', '..', $body);
        smtp_cmd($fp, $headers.$escapedBody."\r\n.", [250]);

        smtp_cmd($fp, "QUIT", [221]);
    } finally {
        fclose($fp);
    }
}

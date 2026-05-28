<?php

/*
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CentralCorp Panel</title>
</head>
<body style="font-family: sans-serif; text-align: center; margin-top: 1rem">
<h1>CentralCorp Panel - PHP installation issue</h1>
<h2>PHP is not executed</h2>
<p>If you see this page in your browser, it means that PHP is not installed or not configured properly on your server.</p>
<p>On Linux with Apache2 you can try the following command: <code>apt install libapache2-mod-php</code></p>
<p>If you are using another setup, please refer to your web server documentation.</p>
<hr>
<p>This is NOT an issue related to CentralCorp Panel.</p>
</body>
</html><!--
*/

/**
 * The CentralCorp Panel installer.
 *
 * This file is not a part of CentralCorp Panel itself,
 * and can be removed when CentralCorp Panel is installed.
 *
 * @author CentralCorp
 */
$installerVersion = '1.2.1';

$minPhpVersion = '8.2';

$requiredExtensions = [
    'bcmath',
    'ctype',
    'json',
    'mbstring',
    'openssl',
    'PDO',
    'tokenizer',
    'xml',
    'xmlwriter',
    'curl',
    'fileinfo',
    'zip',
];

set_error_handler(function ($level, $message, $file = 'unknown', $line = 0) {
    http_response_code(500);
    exit(json_encode(['message' => "A fatal error occurred: {$message} ({$file}:{$line})"]));  
});

//
// Some helper functions
//

function parse_php_version()
{
    preg_match('/^(\d+)\.(\d+)/', PHP_VERSION, $matches);
    if (count($matches) > 2) {
        return "{$matches[1]}.{$matches[2]}";
    }
    return PHP_VERSION;
}

function array_get($array, $key, $default = null)
{
    if (array_key_exists($key, $array)) {
        return $array[$key];
    }
    if (strpos($key, '.') === false) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
    foreach (explode('.', $key) as $segment) {
        if (!array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }
    return $array;
}

function request_method()
{
    return strtoupper(array_get($_SERVER, 'REQUEST_METHOD', 'GET'));
}

function request_url()
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    $path = !empty($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'])[0] : '';
    return "{$scheme}://{$host}{$path}";
}

function detect_url_rewrite()
{
    global $validInstallationUrlRewrite;
    if (isset($validInstallationUrlRewrite) && $validInstallationUrlRewrite === true) {
        return true;
    }
    if (array_get($_SERVER, 'IIS_WasUrlRewritten') === '1') {
        return true;
    }
    if (array_get($_SERVER, 'REDIRECT_URL') !== null) {
        return true;
    }
    return false;
}

$requestContent = null;

function request_input($key, $default = null)
{
    global $requestContent;
    if (!in_array(request_method(), ['GET', 'HEAD'], true)) {
        if ($requestContent === null) {
            $requestContent = json_decode(file_get_contents('php://input'), true);
        }
        if ($requestContent) {
            $value = array_get($requestContent, $key);
            if ($value !== null) {
                return $value;
            }
        }
    }
    return array_get($_GET, $key, $default);
}

function send_json_response($data = null, $status = 200)
{
    if ($data === null && $status === 200) {
        $status = 204;
    }
    if ($status !== 200) {
        http_response_code($status);
    }
    header('Content-Type: application/json');
    if ($data === null) {
        exit();
    }
    exit(json_encode($data));
}

function read_url($url, $curlOptions = null)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CONNECTTIMEOUT => 150,
        CURLOPT_HTTPHEADER => [
            'User-Agent: CentralCorp Panel Installer v1',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    if ($curlOptions !== null) {
        curl_setopt_array($ch, $curlOptions);
    }
    $response = curl_exec($ch);
    $errno = curl_errno($ch);
    if ($errno || $response === false) {
        $error = curl_error($ch);
        throw new RuntimeException("cURL error {$errno}: {$error}");
    }
    $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if ($statusCode >= 400) {
        throw new RuntimeException("HTTP code {$statusCode} returned for '{$url}'.", $statusCode);
    }
    curl_close($ch);
    return $response;
}

function download_file($url, $path)
{
    return read_url($url, [CURLOPT_FILE => fopen($path, 'wb+')]);
}

function has_function($function)
{
    if (!function_exists($function)) {
        return false;
    }
    try {
        return strpos(ini_get('disable_functions'), $function) === false;
    } catch (Exception $e) {
        return false;
    }
}

function is_windows()
{
    return stripos(PHP_OS, 'WIN') === 0;
}

if (array_get($_GET, 'phpinfo') === '') {
    phpinfo();
    exit();
}

//
// Give the requested data if the request is from AJAX.
//
if (
    array_get($_SERVER, 'HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest'
    || array_get($_GET, 'execute') === 'php'
) {
    try {
        $data = [
            'installerVersion' => $installerVersion,
            'minPhpVersion' => $minPhpVersion,
            'phpVersion' => parse_php_version(),
            'phpFullVersion' => PHP_VERSION,
            'phpIniPath' => php_ini_loaded_file(),
            'path' => __DIR__,
            'file' => __FILE__,
            'htaccess' => file_exists(__DIR__ . '/.htaccess') && file_exists(__DIR__ . '/public/.htaccess'),
            'webConfig' => file_exists(__DIR__ . '/web.config') && file_exists(__DIR__ . '/public/web.config'),
            'windows' => is_windows(),
        ];

        $writable = is_writable(__DIR__) && is_writable(__DIR__ . '/public');

        $requirements = [
            'php' => version_compare(PHP_VERSION, $minPhpVersion, '>='),
            'writable' => $writable,
            'function-symlink' => has_function('symlink'),
            'rewrite' => detect_url_rewrite(),
        ];

        $extracted = file_exists(__DIR__ . '/vendor');

        foreach ($requiredExtensions as $extension) {
            $requirements['extension-' . $extension] = extension_loaded($extension);
        }

        $data['requirements'] = $requirements;
        $data['compatible'] = !in_array(false, $requirements, true);
        $data['downloaded'] = file_exists(__DIR__ . '/CentralCorpPanel.zip');
        $data['extracted'] = $extracted;

        // Vérification non-bloquante de la dernière version disponible de l'installer
        $latestInstallerVersion = null;
        try {
            $releaseJson = read_url(
                'https://api.github.com/repos/Geoventure-MC/Installer/releases/latest',
                [CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 3]
            );
            $release = json_decode($releaseJson);
            if ($release && isset($release->tag_name)) {
                $latestInstallerVersion = ltrim($release->tag_name, 'v');
            }
        } catch (Throwable $e) {
            // Non-bloquant — GitHub peut être injoignable
        }
        $data['latestInstallerVersion'] = $latestInstallerVersion;

        $action = request_input('action');

        if (request_method() !== 'POST') {
            send_json_response($data);
        }

        if ($action === 'download') {
            // Get the latest panel release from GitHub
            $json = read_url('https://api.github.com/repos/CentralCorp/centralpanel-v2/releases/latest');
            $release = json_decode($json);

            if (!$release || !isset($release->assets)) {
                throw new RuntimeException('Unable to fetch the latest release from GitHub.');
            }

            $asset = null;
            foreach ($release->assets as $a) {
                if (str_starts_with($a->name, 'panel-') && str_ends_with($a->name, '.zip')) {
                    $asset = $a;
                    break;
                }
            }

            if (!$asset) {
                throw new RuntimeException('No matching asset (panel-*.zip) found in the latest release.');
            }

            $file = __DIR__ . '/' . $asset->name;

            download_file($asset->browser_download_url, $file);

            if (!file_exists($file)) {
                throw new RuntimeException('The file was not downloaded.');
            }

            $zip = new ZipArchive();
            if (($status = $zip->open($file)) !== true) {
                throw new RuntimeException('Unable to open zip: ' . $status . '.');
            }
            if (!$zip->extractTo(__DIR__)) {
                throw new RuntimeException('Unable to extract zip');
            }
            $zip->close();

            // Générer .htaccess pour Apache
            $htaccessContent = "<IfModule mod_rewrite.c>\n    <IfModule mod_negotiation.c>\n        Options -MultiViews\n    </IfModule>\n\n    RewriteEngine On\n\n    # Handle Authorization Header\n    RewriteCond %{HTTP:Authorization} .\n    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n\n    # Empecher l'accès aux fichiers sensibles\n    RewriteRule ^(.env|composer.json|package.json)$ - [F,L]\n\n    # Rediriger vers le dossier public\n    RewriteCond %{REQUEST_FILENAME} -d [OR]\n    RewriteCond %{REQUEST_FILENAME} -f\n    RewriteRule ^ ^$1 [N]\n\n    RewriteCond %{REQUEST_URI} (\.\w+$) [NC]\n    RewriteRule ^(.*)$ public/$1\n\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^ server.php [L]\n</IfModule>";
            file_put_contents(__DIR__ . '/.htaccess', $htaccessContent);

            // Générer web.config pour Windows IIS
            if (is_windows()) {
                $webConfigRoot = '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Redirect to public" stopProcessing="true">
                    <match url="^(?!public/)(.*)$" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="public/{R:1}" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>';
                file_put_contents(__DIR__ . '/web.config', $webConfigRoot);

                $webConfigPublic = '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Main Rule" stopProcessing="true">
                    <match url=".*" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="server.php" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>';
                file_put_contents(__DIR__ . '/public/web.config', $webConfigPublic);
            }

            // Cleanup zip
            if (file_exists($file)) {
                unlink($file);
            }

            send_json_response($data);
        }

        send_json_response('Unexpected action: ' . $action, 403);
    } catch (Throwable $t) {
        send_json_response(['message' => $t->getMessage()], 500);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="https://centralcorp.github.io/img/panel.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Installation - CentralCorp Panel</title>
    <script type="module" crossorigin src="/assets/index-C21ecbi6.js"></script>
    <link rel="stylesheet" crossorigin href="/assets/index-pzMaBkPj.css">
</head>

<body>
    <div id="app"></div>
</body>

</html>

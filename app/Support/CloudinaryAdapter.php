<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

class CloudinaryAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    protected string $cloudName;

    protected string $apiKey;

    protected string $apiSecret;

    protected ?string $customUrl;

    public function __construct(array $config)
    {
        $this->cloudName = (string) ($config['cloud_name'] ?? '');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->apiSecret = (string) ($config['api_secret'] ?? '');

        $url = (string) ($config['url'] ?? $config['cloudinary_url'] ?? '');
        if (str_starts_with($url, 'cloudinary://') && preg_match('#^cloudinary://([^:]+):([^@]+)@(.+)$#i', trim($url), $matches)) {
            if (empty($this->apiKey)) {
                $this->apiKey = $matches[1];
            }
            if (empty($this->apiSecret)) {
                $this->apiSecret = $matches[2];
            }
            if (empty($this->cloudName)) {
                $this->cloudName = $matches[3];
            }
            $this->customUrl = null;
        } else {
            $this->customUrl = (! empty($url) && ! str_starts_with($url, 'cloudinary://')) ? rtrim($url, '/') : null;
        }
    }

    public function publicUrl(string $path, Config $config): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if ($this->customUrl) {
            return $this->customUrl . '/' . $cleanPath;
        }

        return "https://res.cloudinary.com/{$this->cloudName}/image/upload/{$cleanPath}";
    }

    public function getUrl(string $path): string
    {
        return $this->publicUrl($path, new Config);
    }

    public function fileExists(string $path): bool
    {
        $url = $this->publicUrl($path, new Config);

        try {
            $response = Http::head($url);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function directoryExists(string $path): bool
    {
        return false;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->uploadFile($path, $contents);
    }

    public function writeStream(string $path, $resource, Config $config): void
    {
        $contents = stream_get_contents($resource);
        if ($contents === false) {
            throw UnableToWriteFile::at($path, 'Could not read input stream.');
        }
        $this->uploadFile($path, $contents);
    }

    protected function uploadFile(string $path, string $contents): void
    {
        $path = ltrim($path, '/');
        $publicId = preg_replace('/\.[^.]+$/', '', $path);

        $timestamp = time();
        $params = [
            'overwrite' => 'true',
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        $params['signature'] = $this->generateSignature($params);
        $params['api_key'] = $this->apiKey;

        try {
            $response = Http::asMultipart()
                ->attach('file', $contents, basename($path))
                ->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/auto/upload", $params);

            if (! $response->successful()) {
                throw UnableToWriteFile::at($path, $response->body());
            }
        } catch (\Throwable $e) {
            throw UnableToWriteFile::at($path, $e->getMessage(), $e);
        }
    }

    public function read(string $path): string
    {
        $url = $this->publicUrl($path, new Config);

        try {
            $response = Http::get($url);
            if (! $response->successful()) {
                throw UnableToReadFile::fromLocation($path, $response->body());
            }

            return $response->body();
        } catch (\Throwable $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function readStream(string $path)
    {
        $contents = $this->read($path);
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        $path = ltrim($path, '/');
        $publicId = preg_replace('/\.[^.]+$/', '', $path);

        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        $params['signature'] = $this->generateSignature($params);
        $params['api_key'] = $this->apiKey;

        try {
            Http::asForm()->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy", $params);
        } catch (\Throwable $e) {
            throw UnableToDeleteFile::at($path, $e->getMessage(), $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
    }

    public function createDirectory(string $path, Config $config): void
    {
    }

    public function setVisibility(string $path, string $visibility): void
    {
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return [];
    }

    public function move(string $path, string $newPath, Config $config): void
    {
        $contents = $this->read($path);
        $this->write($newPath, $contents, new Config);
        $this->delete($path);
    }

    public function copy(string $path, string $newPath, Config $config): void
    {
        $contents = $this->read($path);
        $this->write($newPath, $contents, new Config);
    }

    protected function generateSignature(array $params): string
    {
        ksort($params);
        $query = [];
        foreach ($params as $key => $value) {
            $query[] = "{$key}={$value}";
        }
        $stringToSign = implode('&', $query) . $this->apiSecret;

        return sha1($stringToSign);
    }
}

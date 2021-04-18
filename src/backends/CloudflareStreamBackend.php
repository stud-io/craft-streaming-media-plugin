<?php
namespace StudIO\StreamingMedia\backends;

use Craft;

use BoldBrush\Cloudflare\API;

use StudIO\StreamingMedia\Elements\StreamAsset;

// class CloudflareStreamBackend implements TranscodingBackendInterface, StorageBackendInterface
class CloudflareStreamBackend
{
    public $api;

    public $rsa_key;

    public function __construct($backend_config)
    {
        $this->api = API\Factory::make(
            $backend_config['account_id'],
            $backend_config['api_token'],
            'api_token'
        );

        if(isset($backend_config['rsa_key_id']) &&  isset($backend_config['rsa_key_pem']))
            $this->rsa_key = [
                'id' => $backend_config['rsa_key_id'],
                'pem' => $backend_config['rsa_key_pem'],
            ];
    }

    public function execute(StreamAsset $stream_asset)
    {
        $stream = $this->api->copy(trim($stream_asset->source_url), $stream_asset->title);
        
        if($stream instanceof \BoldBrush\Cloudflare\API\Response\Error){
            return $stream;
        }

        $stream_asset->transcoding_backend_reference = $stream->uid;
        $stream_asset->transcoding_backend_status = StreamAsset::STATUS_TR_INPROG;;
        
        $save_result = Craft::$app->elements->saveElement($stream_asset);

        return $save_result;
    }

    public function get_status(StreamAsset $stream_asset)
    {
        $info = $this->get_info($stream_asset);
        Craft::info("Transcode info: " . json_encode($info), __METHOD__);

        if($info->status->state === 'ready'){
          $stream_asset->transcoding_backend_status = StreamAsset::STATUS_TR_COMPLETED;

          $stream_asset->storage_backend_status = StreamAsset::STATUS_ST_READY;
          $stream_asset->storage_backend_reference = json_encode($info->playback);

          $save_result = Craft::$app->elements->saveElement($stream_asset);

          return true;
        }

        return false;
    }

    public function get_info(StreamAsset $stream_asset)
    {
        if($stream_asset->transcoding_backend_reference != null)
          return $this->api->getStream($stream_asset->transcoding_backend_reference);

        return false;
    }

    public function issue_token(StreamAsset $stream_asset, $options)
    {
        $expire_ts = null;

        // FIXME: Set expire_ts to expire datetime obj
        // if($options['expire'])

        return $this->signToken($stream_asset->transcoding_backend_reference, $this->rsa_key, $expire_ts);
    }

    /**
     * Signs a url token for the stream reproduction
     *
     * @param string $uid The stream uid.
     * @param array $key The key id and pem used for the signing.
     * @param string $exp Expiration; a unix epoch timestamp after which the token will not be accepted.
     * @param string $nbf notBefore; a unix epoch timestamp before which the token will not be accepted.
     *
     * https://dev.to/robdwaller/how-to-create-a-json-web-token-using-php-3gml
     * https://developers.cloudflare.com/stream/viewing-videos/securing-your-stream#creating-a-signing-key
     *
     */
    public static function signToken(string $uid, array $key, string $exp = null, string $nbf = null)
    {
        $privateKey = base64_decode($key['pem']);

        $header = ['alg' => 'RS256', 'kid' => $key['id']];
        $payload = ['sub' => $uid, 'kid' => $key['id']];

        if ($exp) {
            $payload['exp'] = $exp;
        }

        if ($nbf) {
            $payload['nbf'] = $nbf;
        }

        $encodedHeader = self::base64Url(json_encode($header));
        $encodedPayload = self::base64Url(json_encode($payload));

        openssl_sign("$encodedHeader.$encodedPayload", $signature, $privateKey, 'RSA-SHA256');

        $encodedSignature = self::base64Url($signature);

        return "$encodedHeader.$encodedPayload.$encodedSignature";
    }

    protected static function base64Url(string $data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
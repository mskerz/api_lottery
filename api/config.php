<?php 
return [
    'secret_key' => base64_encode(openssl_random_pseudo_bytes(32)),
];
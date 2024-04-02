<?php

namespace App\Trellotrolle\Lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JsonWebToken
{
    /**
     * @var string le code secret pour l'encodage et le décodage
     */
    private static string $jsonSecret = "sX0y9A41feooAqPB56PAa+";

    /**
     * Fonction permettant d'encoder un tableau en JWT
     * @param array $contenu Le tableau à encoder
     * @return string le JWT encodé
     */
    public static function encoder(array $contenu) : string {
        return JWT::encode($contenu, self::$jsonSecret, 'HS256');
    }

    /**
     * Fonction permettant de décoder un JWT
     * @param string $jwt Le JWT à décoder
     * @return array le contenu décodé
     */
    public static function decoder(string $jwt) : array {
        try {
            $decoded = JWT::decode($jwt, new Key(self::$jsonSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $exception) {
            return [];
        }
    }

}

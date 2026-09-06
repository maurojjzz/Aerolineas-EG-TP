<?php 

class CloudinaryController {

    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;



    public function __construct() {
        $config = require __DIR__ . '/../config/cloudinary.php';

        $this->cloudName = $config['cloud_name'];
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];
    }


    public function subirFoto(string $ruta):array{

        $url = "https://api.cloudinary.com/v1_1/".$this->cloudName."/image/upload";

        $archivo = new CURLFile($ruta);

        $data = [
            'file' => $archivo,
            'folder' => 'aerolineas/logos'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->apiSecret
        ]);

        $response = curl_exec($curl);

        if($response === false){
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("Error al subir la imagen a la nube: " . $error);
        }

        $codigoHttp = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $resultado = json_decode($response, true); 



        if($codigoHttp >= 400){
            throw new Exception(
                $resultado["error"]["message"] ?? "Error de Cloudinary"
            );
            
        }

        return ["secure_url" => $resultado["secure_url"], "public_id" => $resultado["public_id"]];
    }    
    
}

?>
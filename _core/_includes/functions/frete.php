<?php







function calcular_frete_pacote($cep_origem, $cep_destino, $altura, $largura, $comprimento, $peso) {

    try{



        $url = "https://www.melhorenvio.com.br/api/v2/me/shipment/calculate";

        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZmMyZWFmMGNlZjBiZTIwYjg0ZjYwMjkyMGQ3NWRmNmJmZDNiYTAxZDhiMjk4N2U2NjZjY2QxMzQ1NmQwNjg1NTkwZWZiZGQwODM3MThjYWYiLCJpYXQiOjE3NTI4NDU5ODQuMzE1NzE0LCJuYmYiOjE3NTI4NDU5ODQuMzE1NzE2LCJleHAiOjE3ODQzODE5ODQuMzAyMDM1LCJzdWIiOiJhMDYwN2QyNC0wZDU3LTRlZGMtOWMyZi0xMjk3MzE3NjVhMjgiLCJzY29wZXMiOlsiY2FydC1yZWFkIiwiY2FydC13cml0ZSIsImNvbXBhbmllcy1yZWFkIiwiY29tcGFuaWVzLXdyaXRlIiwiY291cG9ucy1yZWFkIiwiY291cG9ucy13cml0ZSIsIm5vdGlmaWNhdGlvbnMtcmVhZCIsIm9yZGVycy1yZWFkIiwicHJvZHVjdHMtcmVhZCIsInByb2R1Y3RzLWRlc3Ryb3kiLCJwcm9kdWN0cy13cml0ZSIsInB1cmNoYXNlcy1yZWFkIiwic2hpcHBpbmctY2FsY3VsYXRlIiwic2hpcHBpbmctY2FuY2VsIiwic2hpcHBpbmctY2hlY2tvdXQiLCJzaGlwcGluZy1jb21wYW5pZXMiLCJzaGlwcGluZy1nZW5lcmF0ZSIsInNoaXBwaW5nLXByZXZpZXciLCJzaGlwcGluZy1wcmludCIsInNoaXBwaW5nLXNoYXJlIiwic2hpcHBpbmctdHJhY2tpbmciLCJlY29tbWVyY2Utc2hpcHBpbmciLCJ0cmFuc2FjdGlvbnMtcmVhZCIsInVzZXJzLXJlYWQiLCJ1c2Vycy13cml0ZSIsIndlYmhvb2tzLXJlYWQiLCJ3ZWJob29rcy13cml0ZSIsIndlYmhvb2tzLWRlbGV0ZSIsInRkZWFsZXItd2ViaG9vayJdfQ.xfNqYiCxQ5oS_Kk6GTpiXA0t8YELvt7oE6ET6XwmPZJrqPSEgxhH4cKORSSX28duFLhnDvZJlMQpEOENvis8xeNrK2m1Fvbo9vZUmV7tKyab-Mti-EH6qVIG7lmT8jXSKkHQT6GsJlBVfEUXAl59nvlN0_yiEtOzTGd285ezSkxJR3J5QII2UY9DV6H0_iOXrRU_BdFP9LfHkOIluKPFJe2dcNsPqRAWBuBDdy3p44dOhVQhF3eJHpNbjyqVXKuterQ0Ri1oZgZ64pTMimqKf2pFxNG4CUVyXIgGGDOL2wkhTqzmfkmBmKy2xqKzLFHokiyIEFSKIThRjpd94WelfO6DNtV7RyNg6OxSVfY8bWITVfXB1qbfsKcl2hzo7lPfdzNmaGsQKPCAITKDox_w6u720YRFSWkbj9rg17Vqf7X5xUIVzB71SHtBWrAarEltmc5fKC7nebkyqQnGtqok5X_R5huLYxwLQ64pNB3_1ugVMzNZTPhMOcbI9qlOr8m9QzMoUOzdTG9pq_XyxlHigotJxA8pvKrtsydYp6Gu3vqKZ42mx4ik4BzuOayxeVSbktkWKy7YuBS5T3brSLP-nb2HAtEFV56kuOl_tDBSGyoSKLO2Iw_MV__PXRe8-F5LJt3luPKPAaU6H-l640HC0dNG3n-QJsLHQmBAOR3D53I";

        

        // Dados a serem enviados

        $data = json_encode([

            "from" => [

                "postal_code" => $cep_origem ?? ""

            ],

            "to" => [

                "postal_code" => $cep_destino ?? ""

            ],

            "package" => [

                "height" => $altura ?? "",

                "width" => $largura ?? "",

                "length" => $comprimento ?? "",

                "weight" => $peso ?? ""

            ]

        ]);



        // Configuração do cabeçalho

        $headers = [

            "Accept: application/json",

            "Authorization: Bearer $token",

            "Content-Type: application/json",

            "User-Agent: Aplicação email@email.com"

        ];

    

        // Inicializando cURL

        $curl = curl_init();

    

        // Configurando a solicitação cURL

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => $data,

            CURLOPT_HTTPHEADER => $headers,

        ]);

    

        // Executando a solicitação e capturando a resposta

        $response = curl_exec($curl);

    

        // Verificando erros

        if (curl_errno($curl)) {

            //'Erro na solicitação cURL: ' . curl_error($curl);

            return false; 

        } else {

            // Exibindo a resposta

            // echo "<pre>";

            // var_dump(json_decode($response, true));

            // echo "</pre>";

            return  json_decode($response, true);

        }

    }catch (Exception $e) {

        return false;

    }

}
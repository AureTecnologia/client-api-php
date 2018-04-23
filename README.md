# Cliente para consumo da API do Aure

Repositório do cliente de consumo da API do Aure.

## Uso

```php
<?php

use Aure\AureClientAPI;

// $customer é o identificador da conta do cliente no Aure
// $form é o identificador do formulário dentro da conta do cliente no Aure 
$client = new AureClientAPI($customer, $form);


// $data são os campos que devem ser enviados para o Aure
// $source são os campos de referer da solicitação. Esse campo serve para contabilização dos UTMs
if($client->sendNewLead('email@pontocriativo.adm.br', $data, $source))
    return "Envio realizado com sucesso";
```
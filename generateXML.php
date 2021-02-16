<?php
function clearStr($str)
{
  $c = array('Ç', 'ç');
  $a = array('Á', 'À', 'Ä', 'Â', 'Ã', 'á', 'à', 'ä', 'â', 'â', 'ã');
  $e = array('Ë', 'É', 'Ê', 'ë', 'é', 'ê', '&');
  $i = array('Ï', 'Í', 'ï', 'í');
  $o = array('Ö', 'Ó', 'Ô', 'Õ', 'ö', 'ó', 'ô', 'õ');
  $u = array('Ü', 'Ú', 'ü', 'ú');
  return str_replace('(', '', str_replace(')', '', str_replace('/', '', str_replace($c, 'c', str_replace($a, 'a', str_replace($e, 'e', str_replace($i, 'i', str_replace($o, 'o', str_replace($u, 'u', $str)))))))));
}

function generateXML($products, $client, $shipping, $notificationURL) {
  $generatedXml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
    <payment>
        <mode>default</mode>
        <method>boleto</method>
        <sender>
          <name>" . clearStr($client['name']) . "</name>
          <email>" . $client['email'] . "</email>
          <phone>
            <areaCode>" . $client['ddd'] . "</areaCode>
            <number>" . $client['phone'] . "</number>
          </phone>
          <documents>
            <document>
              <type>CPF</type>
              <value>" . $client['cpf'] . "</value>
            </document>
          </documents>
          <hash>" . $client['senderHash'] . "</hash>
        </sender>
        <currency>BRL</currency>
        <notificationURL>" . $notificationURL . "</notificationURL>
        <items>" . PHP_EOL;
        foreach ($products as $product) {
          $generatedXml .=
            '
            <item>
              <id>' . $product['code'] . '</id>
              <description>' . clearStr($product['name']) . '</description>
              <amount>' .  $product['amount']  . '</amount>
              <quantity>' . $product['quantity'] . '</quantity>
            </item>' . PHP_EOL;
        }
  $generatedXml .= PHP_EOL . "
        </items>
        <extraAmount>0.00</extraAmount>
        <reference>" . $client['code'] . "</reference>
        <shipping>
          <address>
            <street>" . clearStr($client['address']) . "</street>
            <number>" . $client['number'] . "</number>
            <complement>" . clearStr($client['complement']) . "</complement>
            <district>" . clearStr($client['district']) . "</district>
            <city>" . clearStr($client['city']) . "</city>
            <state>" . $client['state'] . "</state>
            <country>BRA</country>
            <postalCode>" . $client['cep'] . "</postalCode>
          </address>
          <type>1</type>
          <cost>" . number_format(floatval(str_replace("R$ ", "", $shipping)), 2, '.', '') . "</cost>
        </shipping>" . PHP_EOL;

  if($client['card']['token']) {
    $generatedXml .= PHP_EOL . "
        <creditCard>
          <token>" . $client['card']['token'] . "</token>
          <installment>
            <quantity>". $client['card']['quantity'] ."</quantity>
            <value>". $client['card']['value'] ."</value>
            <noInterestInstallmentQuantity>18</noInterestInstallmentQuantity>
          </installment>
          <holder>
            <name>". $client['name'] ."</name>
            <documents>
              <document>
                <type>CPF</type>
                <value>". $client['cpf'] ."</value>
              </document>
            </documents>
            <birthDate>". $client['birthDate'] ."</birthDate>
            <phone>
              <areaCode>". $client['ddd'] ."</areaCode>
              <number>". $client['phone'] ."</number>
            </phone>
          </holder>
          <billingAddress>
            <street>" . clearStr($client['address']) . "</street>
            <number>" . $client['number'] . "</number>
            <complement>" . clearStr($client['complement']) . "</complement>
            <district>" . clearStr($client['district']) . "</district>
            <city>" . clearStr($client['city']) . "</city>
            <state>" . $client['state'] . "</state>
            <country>BRA</country>
            <postalCode>" . $client['cep'] . "</postalCode>
          </billingAddress>
        </creditCard>" . PHP_EOL;
  }

  $generatedXml .= PHP_EOL . "
        <dynamicPaymentMethodMessage>
          <creditCard>NomeNoCartao</creditCard>
          <boleto>Nome no Boleto</boleto>
        </dynamicPaymentMethodMessage>
      </payment>";

  return $generatedXml;
}
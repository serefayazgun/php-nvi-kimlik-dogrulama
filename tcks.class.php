<?php
 
class TCKN
{
 
    var $tckn;
    var $name;
    var $surname;
    var $birthYear;
 
    public function uppercase($text)
    {
        $text = trim($text);
        $lowercase = array('i','ş','ı','ö','ğ','ü','ç');
        $uppercase = array('İ','Ş','I','Ö','Ğ','Ü','Ç');
        $upText = str_replace($lowercase,$uppercase,$text);
        return mb_strtoupper($upText);
    }
 
    public function tcknCurl($tckn,$name,$surname,$birthYear)
    {
        $sendSoap = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
        <TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
        <TCKimlikNo>'.$tckn.'</TCKimlikNo>
        <Ad>'.$this->uppercase($name).'</Ad>
        <Soyad>'.$this->uppercase($surname).'</Soyad>
        <DogumYili>'.$birthYear.'</DogumYili>
        </TCKimlikNoDogrula>
        </soap:Body>
        </soap:Envelope>';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sendSoap);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'POST /Service/KPSPublic.asmx HTTP/1.1',
        'Host: tckimlik.nvi.gov.tr',
        'Content-Type: text/xml; charset=utf-8',
        'Content-Length: '.strlen($sendSoap)
        ));
           
        $income = curl_exec($ch);
        curl_close($ch);
        $incomeResult = strip_tags($income);
                   
        if ($incomeResult == "1" or $incomeResult == "true")
        {
            $result = "true";
        }
        else
        {
            $result = "false";
        }      
        return $result;
    }
 
       
       
       
    public function tcknSoap($tckn,$name,$surname,$birthYear)
    {
                       
        $sslHTTPS = stream_context_create(
            [
                'ssl' =>
            [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
   
        $client = new SoapClient('https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL',
            array('trace' => 1,
                'soapaction' => 'http://tckimlik.nvi.gov.tr/WS/TCKimlikNoDogrula',
                'encoding' => 'UTF-8',
                'cache_wsdl' => WSDL_CACHE_NONE,
                'user_agent' => '',
                'keep_alive' => false,
                'stream_context' => $sslHTTPS
            ));
 
        $sendSoap = new SoapVar('<TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS"><TCKimlikNo>'.$tckn.'</TCKimlikNo><Ad>'.$this->uppercase($name).'</Ad><Soyad>'.$this->uppercase($surname).'</Soyad><DogumYili>'.$birthYear.'</DogumYili></TCKimlikNoDogrula>', XSD_ANYXML);
 
        $incomeResult = $client->tcknValidate($sendSoap)->tcknValidateResult;
                   
        if ($incomeResult == "1" or $incomeResult == "true")
        {
            $result = "true";
        }
        else
        {
            $result = "false";
        }  
        return $result;
    }
 
   
    public function tcknAlgo($tckn)
    {          
        if (strlen($tckn) == 11)
        {
            $b = str_split($tckn);
            $b1 = $b[0];
            $b2 = $b[1];
            $b3 = $b[2];
            $b4 = $b[3];
            $b5 = $b[4];
            $b6 = $b[5];
            $b7 = $b[6];
            $b8 = $b[7];
            $b9 = $b[8];
            $b10 = $b[9];
            $b11 = $b[10];
 
            $b10_val = fmod(( $b1 + $b3 + $b5 + $b7 + $b9 ) * 7 - ($b2 + $b4 + $b6 + $b8),10);
            $b11_val = fmod($b1 + $b2 + $b3 + $b4 + $b5 + $b6 + $b7 + $b8 + $b9 + $b10,10);
        }
           
        if (strlen($tckn) != 11)
        {
            $result = "false";
        }
        elseif ($b1 == 0)
        {
            $result = "false";
        }
        elseif (!is_numeric($b1) or !is_numeric($b2) or !is_numeric($b3) or !is_numeric($b4) or !is_numeric($b5) or !is_numeric($b6) or !is_numeric($b7) or !is_numeric($b8) or !is_numeric($b9) or !is_numeric($b10) or !is_numeric($b11))
        {
            $result = "false";      
        }
        elseif($b10_val != $b10)
        {
            $result = "false";
        }
        elseif($b11_val != $b11)
        {
            $result = "false";
        }
        else
        {
            $result = "true";
        }
            return $result;  
    }
 
    public function tcknValidate($tckn,$name,$surname,$birthYear)
    {
        $tcknAlgoResult = $this->tcknAlgo($tckn);
           
        if ($tcknAlgoResult == "true")
        {
            if(function_exists('curl_version'))
            {
                $result = $this->tcknCurl($tckn,$name,$surname,$birthYear);
            }
            elseif (class_exists('SOAPClient'))
            {
                $result = $this->tcknSoap($tckn,$name,$surname,$birthYear);
            }
            elseif(!function_exists('curl_version') and !class_exists('SOAPClient'))
            {
                $result = "Both methods (cURL and Soap Class) doesn't exists.";              
            }
        }
               
            if ($tcknAlgoResult != "true")
                {
                    $result = "false";
                }
            return $result;
    }
}
 
?>
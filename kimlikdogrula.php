<!DOCTYPE html>
<html lang="tr" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TC Kimlik Bilgileri Doğrulama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  </head>
  <body>
    <div class="container mt-5">
      <div class="row justify-content-md-center">
        <div class="col-md-6 align-self-center">
          <div class="card">
            <div class="card-header text-center p-3 mb-3 bg-success bg-gradient text-white">
              <h4>T.C Kimlik Doğrulama</h4>
            </div>
            <div class="card-body">
              <!-- TC Kimlik Doğrulama Formu -->
              <form action="" method="POST">

                <div class="form-floating mb-3">
                  <input type="text" class="form-control" required="" name="tc" maxlength="11" placeholder="TC Kimlik Numaranız">
                  <label for="floatingInput">TC Kimlik Numarası</label>
                </div>

                <div class="form-floating mb-3">
                  <input type="text" class="form-control" required="" name="ad" placeholder="Adınız">
                  <label for="floatingInput">Adınız</label>
                </div>

                <div class="form-floating mb-3">
                  <input type="text" class="form-control" required="" name="soyad"placeholder="Soyadınız">
                  <label for="floatingInput">Soyadınız</label>
                </div>

                <div class="form-floating mb-3">
                  <input type="text" class="form-control" required="" name="dogumyili" maxlength="4" placeholder="Doğum Yılınız">
                  <label for="floatingInput">Doğum Yılınız</label>
                </div>

                <div class="col text-center d-grid gap-2">
                  <button type="submit" class="btn btn-outline-success btn-lg" name="tcKimlikSorgula">Doğrula</button>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    <div class="row justify-content-md-center mt-5">
        <div class="col-md-6 align-self-center">
        <?php
        
        /*
          * tr_strtoupper Fonksiyonu
          * Bu fonksiyonu Türkçe karakterler küçük yazıldığında otomatik olarak büyük hale çevirecek. Doğrulama isteğini bu şekilde göndereceğiz.
        */
        function tr_strtoupper($yazi)
        {
             $ara     = array("ç","i","ı","ğ","ö","ş","ü");

             $degistir= array("Ç","İ","I","Ğ","Ö","Ş","Ü");

             $yazi    = str_replace($ara,$degistir,$yazi);

             $yazi    = strtoupper($yazi);

             return $yazi;
        }
        
        /* T.C Doğrulama */
        function tcKimlikKontrol($tcKimlik = null)
        {
            
            // Boşlukları ve soldaki sıfırı temizle
            $tcKimlik=trim($tcKimlik);
            $tcKimlik=trim($tcKimlik,"0");
            
            if(strlen($tcKimlik)!=11)
            {
                return false;
            }
            
            // TC Kimlik Format Kontrolü : 1-3-5-7-9. haneler toplamından, 2-4-6-8. haneleri çıkar
            // Elde edilen sayıyı 10'a böl, 
            // Kalan sayı TC Kimlik Numarasının 10. karakterini verecek
            $tekBasamaklar = 0;
            $ciftBasamaklar= 0;
            
            for($i=0; $i<=8; $i+=2)
            {
                $tekBasamaklar+=$tcKimlik[$i];
            }
            
            for($i=1; $i<=7; $i+=2)
            {
                $ciftBasamaklar+=$tcKimlik[$i];
            }
            
            if( ((7*$tekBasamaklar)-$ciftBasamaklar)%10!=$tcKimlik[9] )
            {
                return false;
            }
           
            // Format Kontrolü -2 : 1-10. haneler toplamının 10'a bölümünden kalan, 11. haneyi verecek
            $toplam = 0;

            for($i=0; $i<=9; $i++)
            {
              $toplam+=$tcKimlik[$i];
            }

                
            
            if($toplam%10!=$tcKimlik[10])
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        
        /*
          * TC Kimlik No Doğrulama Kısmı
          * Bilgiler doldurulup Doğrula butonuna tıklanana kadar işlem başlamaması için isset() fonksiyonu kullanıyoruz.
        */
        if (isset($_POST['tcKimlikSorgula']))
        {
          /*
           * Değerler, formu gönder butonu ile birlikte POST edildi ve yakalayıp ilgili değiş kenlere atadık.
          */
          $tcKimlikNo = $_POST['tc'];
          if(tcKimlikKontrol($tcKimlikNo))
          {
            
            /*
            Ad ve Soyad için türkçe küçük karakter yazılırsa bunu otomatik olarak büyük hale
            çeviriyoruz (karakterDuzeltme) ve her ihtimale karşın sağında ya da solunda
            boşluk varsa o kısmı kırpıyoruz(trim()).
            */
            $ad       = tr_strtoupper(trim($_POST["ad"]));

            $soyad    = tr_strtoupper(trim($_POST['soyad']));

            $dogumYili= $_POST['dogumyili'];

            /*
              Bundan sonraki kodları TRY CATCH blogunda yazdıracağız ki herhangi bir hata olduğunda bunu yakalayabilelim.
            */
            try {
            /*
            Değişkenlere atadığımız form verilerini $veriler adında bir diziye aktarıyoruz.
            */
              $veriler = array(

                'TCKimlikNo' => $tcKimlikNo,

                'Ad'         => $ad,

                'Soyad'      => $soyad,

                'DogumYili'  => $dogumYili

              );
            /*
            OOP ile SOAP oluşturarak $client adında bir değişkene atıyoruz. Bu sayede
            tckimlik.nvi.gov.tr üzerinden elimizdeki verileri kullanarak sorgulama yapabileceğiz. Eğer php.ini de bulunan extensions'da soap aktif değilse başındaki ";" noktalı virgülü kaldırıp servisi yeniden başlatmanız gerekecektir.
            */
              $params = array('login' => 'kullanici_adi','password' => 'sifre');
              $WSDL_URL = 'https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL';

              /*Kullanıcı ve şifre gönderilecek ise - ŞEREF*/
             //$soap = new SoapClient( $WSDL_URL, $params);
              $client = new SoapClient($WSDL_URL);
              /*
                // Kullanılabilir işlevler ve türler hakkında bilgi aldık
                var_dump($client->__getFunctions()); 
                var_dump($client->__getTypes()); 
              */
          
              $sonuc  = $client->TCKimlikNoDogrula($veriler);

            // Forma girilen bilgilerin hepsi doğruysa aşağıdaki mesaj
              if ($sonuc->TCKimlikNoDogrulaResult)
              {
                 echo '<div class="alert alert-success" role="alert">
                        Girmiş olduğunuz kimlik bilgileri doğrudur.
                      </div>';
              }
            // Bir yada bir kaçtanesi yanlış ise aşağıdaki mesaj son kullanıcıya gösterilir.
              else
              {
                //Girilen bilgiler vefat eden birisine aitse yine yanlış olarak gelecektir.
                echo '<div class="alert alert-danger" role="alert">
                      Girmiş olduğunuz kimlik bilgileri yanlıştır.
                    </div>';
              }
            // Eğer hata oluşursa ekrana yazdırıyoruz.
            } 
              catch (\Exception $e)
              {
               echo $exc->getMessage();
              }
          }
          else
          {
            /*Girilen T.C bilgisi T.C No algoritmasına uymuyor. Böyle bir T.C yok - ŞEREF*/
            echo '<div class="alert alert-warning" role="alert">Girmiş olduğunuz kimlik bilgileri eksik veya yanlış!</div>';
            exit();

          }

        }
        else
        {
          // code...
        }

       
        ?>
      </div>
    </div>
  </div>
  </body>
</html>
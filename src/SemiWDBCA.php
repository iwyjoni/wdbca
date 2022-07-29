<?php

namespace Iwyjoni\Wdbca;

// error_reporting(0);
session_start();
if (!function_exists('curl_init')) {
    die('Error, cURL not installed!');
}
if (!file_exists('cookies')) {
    mkdir('cookies', 0777, true);
}
if (!file_exists('log')) {
    mkdir('log', 0777, true);
}

class semiWDBCA
{
    public $ch;
    public $norek;
    public $source;

    //Fungsi untuk convert hari

    public function dayname($name)
    {
        if ($name == 'Monday') {
            return 'Senin';
        } elseif ($name == 'Tuesday') {
            return 'Selasa';
        } elseif ($name == 'Wednesday') {
            return 'Rabu';
        } elseif ($name == 'Thursday') {
            return 'Kamis';
        } elseif ($name == 'Friday') {
            return 'Jumat';
        } elseif ($name == 'Saturday') {
            return 'Sabtu';
        } elseif ($name == 'Sunday') {
            return 'Minggu';
        }
    }

    // Fungsi untut parse string
    public function string_between($string, $start, $end)
    {
        $string = " " . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    // Fungsi login
    public function Login($session, $user, $pass)
    {

        $cookie = 'cookies/' . $session . '.txt';

        $user_ip = $_SERVER['SERVER_ADDR'];

        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com');
        $info = curl_exec($ch);

        // Login
        $params = 'value%28actions%29=login&value%28user_id%29=' . $user . '&value%28user_ip%29=' . $user_ip . '&value%28browser_info%29=Mozilla%2F5.0+%28Windows+NT+10.0%29+AppleWebKit%2F537.36+%28KHTML%2C+like+Gecko%29+Chrome%2F99.0.7113.93+Safari%2F537.36&value%28mobile%29=false&value%28pswd%29=' . $pass . '&value%28Submit%29=LOGIN';
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        $info = curl_exec($ch);

        $validate = strpos($info, 'Anda dapat melakukan login kembali setelah 5 menit / You can re-login after 5 minutes.');
        $validate2 =  strpos($info, 'Mohon masukkan User ID / Password Anda yg benar (Please enter Your correct User ID / Password)');
        if ($validate) {
            return false; // gagal login session lock
        } else if ($validate2) {
            return 'user/pwd salah';
        } else {
            return 'login'; // berhasil login
        }
    }

    // Fungsi menu
    public function MenuTransfer($session, $norek)
    {
        // Buka menu
        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';

        $cookie = 'cookies/' . $session . '.txt';
        $ch = curl_init();

        // curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
        curl_exec($ch);

        // Buka Transfer dana
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/fund_transfer_menu.htm');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
        curl_exec($ch);

        // Transfer ke rek BCA
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/fundtransfer.do?value(actions)=formentry');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/fund_transfer_menu.htm');
        curl_setopt($ch, CURLOPT_POST, 1);
        $info = curl_exec($ch);

        $num = $info;
        $sixnorek = substr($norek, 4, 6);
        $num = $this->string_between($num, '<input name="value(rndNum)" id="rndNum" maxlength="2" size="3" style="text-align:right;" value="', '" disabled>');
        $num = str_replace(' ', '', $num);
        // return str_replace(' ', '', $num) . $sixnorek;
        if (strpos($info, $norek)) {
            if ($num) {
                return $num . $sixnorek; // jika data catpcah ada
            } else {
                return false; // jika data captcha tidak ada
            }
        } else {
            return 'norek member belum terdaftar'; // jika norek member belum terdafrar
        }
    }

    public function MenuRegister($session, $norek)
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';

        $cookie = 'cookies/' . $session . '.txt';
        $ch = curl_init();

        // Buka Transfer dana
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/fund_transfer_menu.htm');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
        curl_exec($ch);

        // Daftar rekening tujuan
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/ancol.do?value(actions)=choose_bank_type');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/fund_transfer_menu.htm');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_exec($ch);

        // Daftar rekening Bca
        $params = array();

        $params[] = 'value%28actions%29=entry_data';
        $params[] = 'value%28bank_type%29=BCA';

        $params = implode('&', $params);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/ancol.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/ancol.do?value(actions)=choose_bank_type');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);

        $info = curl_exec($ch);

        $num_str = $info;
        $sixnorek = substr($norek, 4, 6);
        $num = $this->string_between($num_str, '<input type="text" name="rndNum" id="rndNum" size="2" style="text-align:right; font-family:courier new; font-size:18px" disabled="disabled" value="', '"/>');
        $num = str_replace(' ', '', $num);

        if ($num) {
            $captha = $num . $sixnorek;
            return $captha; // jika data captcha ada
        } else {
            $filename = 'log/' . $session . '.txt';
            $myfile = fopen($filename, 'w');
            fwrite($myfile, $info);
            return 'gada'; // jika data captcga tidak ada
        }
    }

    // Function to input apli2 register
    public function InputApli2Register($session, $mbrname, $norek, $apli2)
    {
        //
        $params = array();

        $params[] = 'value%28actions%29=validate';
        $params[] = 'value%28bank_type%29=BCA';
        $params[] = 'value%28rekeningnya%29=' . $norek;
        $params[] = 'value%28tantangan%29=' . $apli2;

        $params = implode('&', $params);
        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';

        $cookie = 'cookies/' . $session . '.txt';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/ancol.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/ancol.do?value(actions)=choose_bank_type');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);

        $info = curl_exec($ch);
        $validate = strpos($info, 'ANGKA YANG ANDA MASUKKAN DARI KEYBCA ANDA SALAH.');
        $validate2 = strpos($info, 'NOMOR REKENING TUJUAN TIDAK VALID');
        preg_match_all('/<td class="td-right">(.*?)<\/td>/sim', $info, $mbrname_verfiy);
        if ($validate) {
            return 'apli2 salah';
        } elseif ($validate2) {
            return 'norek tidak vailid';
        } else {
            $mbrname_verfiy = trim(strtolower($mbrname_verfiy[1][1]));
            $mbrname = trim(strtolower($mbrname));

            if (strpos($mbrname, $mbrname_verfiy) !== false) {
                $params = array();

                $params[] = 'value%28actions%29=execute';
                $params[] = 'value%28bank_type%29=BCA';

                $params = implode('&', $params);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/ancol.do');
                curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/ancol.do?value(actions)=choose_bank_type');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_POST, 1);

                $info = curl_exec($ch);

                $issuccess = strpos($info, 'NO. REKENING TUJUAN BCA BERHASIL DIDAFTARKAN');

                if ($issuccess) {
                    return 'register berhasil';
                } else {
                    return 'register gagal';
                }
            } else {

                $params = array();

                $params[] = 'value%28actions%29=entry_data';
                $params[] = 'value%28bank_type%29=BCA';

                $params = implode('&', $params);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/ancol.do');
                curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/ancol.do?value(actions)=choose_bank_type');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_POST, 1);

                $info = curl_exec($ch);
                return $mbrname_verfiy . ' - ' . $mbrname;
            }
        }
    }


    // Function to input apli2
    public function InputApli2Transfer($session, $norek, $amount, $apli2, $date)
    {
        // $amount = str_replace('.00', '', $amount);
        // $amount = str_replace(',00', '', $amount);
        // $amount = str_replace(',', '', $amount);
        // $amount = str_replace('.', '', $amount);
        $dateplus = date("Y-m-d", strtotime($date . ' +1 day'));
        $dayname = $this->dayname(date('l', strtotime($date)));
        $parseTanggal = explode('-', $dateplus);

        // parametes to post apli2
        $params = array();

        $params[] = 'value%28actions%29=validate';
        $params[] = 'value%28StatusSend%29=notfirst';
        $params[] = 'value%28acc_from%29=0';
        $params[] = 'value%28acc_to2%29=0';
        $params[] = 'value%28acc_to_option%29=V3';
        $params[] = 'value%28acc_to3%29=' . $norek;
        $params[] = 'value%28currency%29=Rp.';
        $params[] = 'value%28amount%29=' . $amount;
        $params[] = 'value%28remarkLine1%29=';
        $params[] = 'value%28remarkLine2%29=';
        $params[] = 'value%28keyBCA%29=' . $apli2;
        $params[] = 'value%28trans_type%29=0';
        $params[] = 'value%28post_txfer_dt_day%29=' . $parseTanggal[2];
        $params[] = 'value%28post_txfer_dt_month%29=' . $parseTanggal[1];
        $params[] = 'value%28post_txfer_dt_year%29=' . $parseTanggal[0];
        $params[] = 'value%28recur_param_day%29=01';
        $params[] = 'value%28recur_param_week%29=' . $dayname;
        $params[] = 'value%28recur_param_month%29=01';
        $params[] = 'value%28recur_expire_dt_day%29=21';
        $params[] = 'value%28recur_expire_dt_month%29=' . $parseTanggal[1];
        $params[] = 'value%28recur_expire_dt_year%29=' .  $parseTanggal[0];
        $params[] = 'value%28submit%29=Lanjutkan';

        $params = implode('&', $params);
        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';
        $cookie = 'cookies/' . $session . '.txt';
        $ch = curl_init();

        // curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Post Apli2
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/fundtransfer.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/fundtransfer.do?value(actions)=formentry');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);

        $info = curl_exec($ch);

        # Cek apli2 salah atau benar
        $validate = strpos($info, 'ANGKA YANG ANDA MASUKKAN DARI KEYBCA ANDA SALAH.');
        if ($validate) {
            return false; // apli 2 salah
        } else {
            return true; // post data berhasil
        }
    }

    // Fungsi to input apli 1
    public function InputApli1Transfer($session, $acc_from, $acc_to, $acc_to_name, $amount, $apli1)
    {
        $acc_to_name = str_replace(' ', '+', $acc_to_name);

        $params = array();

        $params[] = 'value%28actions%29=startprocess';
        $params[] = 'value%28acc_from%29=' . $acc_from;
        $params[] = 'value%28acc_to%29=' . $acc_to;
        $params[] = 'value%28ref_no%29=';
        $params[] = 'value%28acctToNm%29=' . $acc_to_name;
        $params[] = 'value%28currency%29=IDR';
        $params[] = 'value%28amount%29=' . $amount;
        $params[] = 'value%28remarkLine1%29=-+++++++++++++++++';
        $params[] = 'value%28remarkLine2%29=-+++++++++++++++++';
        $params[] = 'value%28curToAcc%29=IDR';
        $params[] = 'value%28curFromAcc%29=IDR';
        $params[] = 'value%28acc_type_from%29=1';
        $params[] = 'value%28trans_type%29=0';
        $params[] = 'value%28post_txfer_dt%29=';
        $params[] = 'value%28recur_param%29=';
        $params[] = 'value%28recur_expire_dt%29=';
        $params[] = 'value%28StatusSend%29=notfirst';
        $params[] = 'value%28is_llg%29=0';
        $params[] = 'value%28respondAppli1%29=' . $apli1;
        $params[] = 'value%28submit1%29=Kirim';

        $params = implode('&', $params);

        // echo $params;

        // Post Apli1
        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';
        $cookie = 'cookies/' . $session . '.txt';
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/fundtransfer.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/fundtransfer.do');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);

        $source = curl_exec($ch);


        $params1 = array();

        $params1[] = 'value%28actions%29=transfer';
        $params1[] = 'value%28acc_from%29=' . $acc_from;
        $params1[] = 'value%28remarkLine1%29=-+++++++++++++++++';
        $params1[] = 'value%28remarkLine2%29=-+++++++++++++++++';
        $params1[] = 'value%28keyBCA%29=';
        $params1[] = 'value%28amount%29=' . $amount;
        $params1[] = 'value%28currency%29=IDR';
        $params1[] = 'value%28save_bankid%29=';
        $params1[] = 'value%28acctToNm%29=' . $acc_to_name;
        $params1[] = 'value%28respondAppli1%29=' . $apli1;
        $params1[] = 'value%28trans_type%29=0';
        $params1[] = 'value%28post_txfer_dt%29=';
        $params1[] = 'value%28recur_param%29=';
        $params1[] = 'value%28recur_expire_dt%29=';
        $params1[] = 'value%28is_llg%29=0';
        $params1[] = 'value%28checkedClause%29=';
        $params1[] = 'value%28vreason%29=';

        $params1 = implode('&', $params1);

        // echo $params1;
        // Params post data apli1
        // Post Apli1
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/fundtransfer.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/fundtransfer.do');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $info = curl_exec($ch);
        preg_match_all('/<font face="Arial,Helvetica,Geneva,Swiss,SunSans-Regular" size="1" color="#0000bb">(.*?)<\/font>/sim', $info, $no_ref);

        # Cek apli1 salah atau benar
        $validate = strpos($info, 'ANGKA YANG ANDA MASUKKAN DARI KEYBCA ANDA SALAH.');
        $validate2 = strpos($info, 'TRANSAKSI TRANSFER KE REKENING BCA TELAH SELESAI DIPROSES');
        $validate3 = strpos($info, 'SALDO TIDAK CUKUP');
        $validate4 = strpos($info, 'MAAF SISTEM SEDANG DALAM GANGGUAN');
        $validate5 = strpos($info, 'MAAF, TRANSAKSI ANDA TIDAK DAPAT DIPROSES');
        if ($validate) {
            return 'apli1 salah';
        } elseif ($validate3) {
            return 'Saldo tidak cukup';
        } elseif ($validate2) {
            return $no_ref[1][5]; // jika trf berhasil (return no ref transfer)
        } elseif ($validate4) {
            return 'Terjadi Gangguang pada proses Transaksi'; // cek mutasi
        } elseif ($validate5) {
            return 'Transaksi Gagal'; // cek mutasi
        } else {
            $filename = 'log/' . $session . '.txt';
            $myfile = fopen($filename, 'w');
            fwrite($myfile, $info);
            return 'Transfer gagal unknown';
        }
    }

    // VA 393580895396261040

    public function MenuTransferVA($session, $norekAgent, $noVA, $nominal)
    {
        $ua = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0';

        $cookie = 'cookies/' . $session . '.txt';

        // echo realpath($cookie);
        $ch = curl_init();

        // curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
        curl_exec($ch);

        // Buka Transfer dana
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/nav_bar_indo/fund_transfer_menu.htm');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
        curl_exec($ch);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/vatransfer.do?value(actions)=vaformentry');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/fund_transfer_menu.htm');
        curl_setopt($ch, CURLOPT_POST, 1);
        $info = curl_exec($ch);

        $params = array();

        $params[] = 'value%28actions%29=inq_presentment';
        $params[] = 'value%28acctFrom%29=' . $norekAgent;
        $params[] = 'value%28destAcctType%29=INPUT';
        $params[] = 'value%28txtVirtualAcctTo%29=' . $noVA;
        $params[] = 'value%28productName%29=';
        $params[] = 'value%28selectVirtualAcctTo%29=';
        $params[] = 'value%28submit%29=Lanjutkan';

        $params = implode('&', $params);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/vatransfer.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/vatransfer.do?value(actions)=vaformentry');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        $info = curl_exec($ch);

        preg_match_all('/<table border="0" cellpadding="0" cellspacing="0" width="590">(.*?)<\/table>/sim', $info, $data);
        if (isset($data[1][2])) {
            $data = $data[1][2];
            preg_match_all('/<td class="td-right">(.*?)<\/td>/sim', $data, $td);
            if (isset($td[1])) {
                $params = array();
                $params[] = 'value%28actions%29=validate';
                $params[] = 'value%28amt%29=' . $nominal;
                $params[] = 'value%28submit%29=Lanjutkan';
                $params = implode('&', $params);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/vatransfer.do');
                curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/vatransfer.do');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_POST, 1);
                $info = curl_exec($ch);

                preg_match_all('/<table border="0" cellpadding="0" cellspacing="0" width="590">(.*?)<\/table>/sim', $info, $data);

                if (isset($data[1][2])) {
                    $data = $data[1][2];
                    preg_match_all('/<td class="td-right">(.*?)<\/td>/sim', $data, $td);
                    if (isset($td[1])) {
                        $data = array(
                            'status' => 'success',
                            'from' => $td[1][0],
                            'va' => $td[1][1],
                            'name' => $td[1][2],
                            'brand' => $td[1][3],
                            'nominal' => str_replace('&nbsp;', '', trim(preg_replace('/\s+/', ' ', $td[1][4])))
                        );

                        return json_encode($data);
                    }
                } else {
                    return 'failed';
                }
            } else {
                return 'failed';
            }
        } else {
            return 'failed';
        }
    }

    public function InputApli1TransferVA($session, $token)
    {
        $ua = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0';

        $cookie = 'cookies/' . $session . '.txt';

        $ch = curl_init();

        $params = array();
        $params[] = 'value%28actions%29=startprocess';
        $params[] = 'value%28respondAppli1%29=' . $token;
        $params[] = 'value%28submit%29=Kirim';

        $params = implode('&', $params);

        // curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/vatransfer.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/vatransfer.do');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);

        $info = curl_exec($ch);

        $params = array();
        $params[] = 'value%28actions%29=transfer';
        $params[] = 'value%28totalAmount%29=';
        $params[] = 'value%28respondAppli1%29=' . $token;

        $params = implode('&', $params);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/vatransfer.do');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/vatransfer.do');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);

        $info = curl_exec($ch);

        $validate = strpos($info, 'TRANSAKSI TRANSFER KE BCA VIRTUAL ACCOUNT TELAH SELESAI DIPROSES');
        if ($validate) {
            preg_match_all('/<table border="0" cellpadding="0" cellspacing="0" width="590">(.*?)<\/table>/sim', $info, $data);
            if (isset($data[1][2])) {
                $data = $data[1][2];
                preg_match_all('/<td class="td-right">(.*?)<\/td>/sim', $data, $td);
                if (isset($td[1])) {
                    return $td[1][2];
                } else {
                    return 'failed';
                }
            } else {
                return 'failed';
            }
        } else {
            return 'failed';
        }
    }

    // Fungsi logout
    public function Logout($session)
    {
        // Logout, cURL close, hapus cookies
        $ua = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36';
        $cookie = 'cookies/' . $session . '.txt';
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do?value(actions)=logout');
        curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
        curl_exec($ch);
    }
}

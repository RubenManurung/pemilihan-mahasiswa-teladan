<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Kriteria;
use App\SKKM;
use App\Mahasiswa;
use App\DimPenilaian;
use App\AdakRegistrasi;
use App\DimxDim;
use DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        return view('homepage');
    }

    public function Seleksi_FT()
    {
      $data = $this->PerhitunganFT();
      $hasilAkhir = $data['hasilAkhir'];
      $tfn = $data['tfn'];

      // $paginate = $this->paginate($hasilAkhir, 2);
      return view("seleksi_awal_ft2",['semua'=>$data,'tfn'=>$tfn,'hasilAkhir'=>$hasilAkhir]);
    }

    public function NFDM(){
      $data = $this->Seleksi_FT();
      $hasilAkhir = $data['hasilAkhir'];
      $tfn = $data['tfn'];

      $Cj = [];
      $Aj = [];
      foreach ($hasilAkhir as $key => $value) {
      $Cij = null;
      $Aij = null;

      $Cij = $value['test_ip_max'];
      $Aij = $value['test_perilaku_min'];


      if ((!isset($Cj[$key]["Cj"])) || ($Cj[$key]["Cj"] > $Cij))
        {
          $Cj[$key]["Cj"] = $Cij;
        }
      if ((!isset($Aj[$key]["Aj"])) || ($Aj[$key]["Aj"] > $Aij))
        {
          $Aj[$key]["Aj"] = $Aij;
        }
      }
      $Cj = max($Cj);
      $Aj = min($Aj);

      return view("seleksi_awal_ft3",['Aj'=>$Aj,'Cj'=>$Cj,'semua'=>$data,'tfn'=>$tfn,'hasilAkhir'=>$hasilAkhir]);
    }

    public function PBHNFDM(){
      $data = $this->NFDM();
      $Aj = $data['Aj'];
      $Cj = $data['Cj'];

      $hasilAkhir = $data['hasilAkhir'];
      $tfn = $data['tfn'];

      return view("seleksi_awal_ft4",['Aj'=>$Aj,'Cj'=>$Cj,'semua'=>$data,'tfn'=>$tfn,'hasilAkhir'=>$hasilAkhir]);
    }

    public function FPIS_FNIS(){
      $data = $this->PBHNFDM();
      $Aj = $data['Aj'];
      $Cj = $data['Cj'];

      $hasilAkhir = $data['hasilAkhir'];
      $tfn = $data['tfn'];

      return view("seleksi_awal_ft5",['Aj'=>$Aj,'Cj'=>$Cj,'semua'=>$data,'tfn'=>$tfn,'hasilAkhir'=>$hasilAkhir]);
    }

    public function jarak_FPIS_FNIS(){
      $data = $this->FPIS_FNIS();
      $Aj = $data['Aj'];
      $Cj = $data['Cj'];

      $hasilAkhir = $data['hasilAkhir'];
      $tfn = $data['tfn'];

      return view("seleksi_awal_ft6",['Aj'=>$Aj,'Cj'=>$Cj,'semua'=>$data,'tfn'=>$tfn,'hasilAkhir'=>$hasilAkhir]);
    }

    public function hasilAwal()
    {
      $saw = DimPenilaian::selectRaw("
    askm_dim_penilaian.akumulasi_skor,
    askm_dim_penilaian.dim_id,
    askm_dim_penilaian.ta,
    askm_dim_penilaian.sem_ta");
      $query = AdakRegistrasi::selectRaw("skkm.id AS skkm_id,skkm.skkm as skkm,dimx_dim.dim_id,dimx_dim.nama,adak_registrasi.ta,adak_registrasi.nr AS IPK, adak_registrasi.sem_ta, adak_registrasi.nr, p.akumulasi_skor")
          ->join('dimx_dim', 'dimx_dim.dim_id', 'adak_registrasi.dim_id')
          ->leftJoin('skkm', 'skkm.dim_id', 'dimx_dim.dim_id')
          ->leftJoin(\DB::raw("(" . $saw->toSql() . ") as p"), function ($query) {
              $query->on('p.dim_id', '=', 'adak_registrasi.dim_id');
              $query->on('p.ta', '=', 'adak_registrasi.ta');
              $query->on('p.sem_ta', '=', 'adak_registrasi.sem_ta');
          })
          ->orderBy('dimx_dim.nama','asc')
          ->get();

          $tfn = [
            "Very High"=>[7,9,9],
            "High"=>[5,7,9],
            "Average"=>[3,5,7],
            "Low"=>[1,3,5],
            "Very Low"=>[1,1,3]
          ];


          $ip = [];
          // $maxIp = (float)$query[0]['IPK'];
          // $minIp = $query[0]['IPK'];
          //
          // $maxPrilaku = $query[0]['akumulasi_skor'];
          // $minPrilaku = $query[0]['akumulasi_skor'];

          $hasilAkhir = [];
          foreach ($query as $s) {
            $valMatch = null;
            $seperK = null;
            $valMatchMax = null;

            if ($s['ta'] == 2017 && $s['sem_ta']== 2 || $s['sem_ta']== 1) {
              if ($s['nr'] >= 3.50 && $s['nr'] <= 4.00) {
                $valMatch = $tfn['Very High'][0];
                $seperK = $tfn['Very High'][1];
                $valMatchMax = $tfn['Very High'][2];
              }elseif($s['nr'] >= 3.00 && $s['nr'] <=3.49){
                $valMatch = $tfn['High'][0];
                $seperK = $tfn['High'][1];
                $valMatchMax = $tfn['High'][2];
              }elseif( $s['nr'] >= 2.00 && $s['nr'] <= 2.99){
                $valMatch = $tfn['Average'][0];
                $seperK = $tfn['Average'][1];
                $valMatchMax = $tfn['Average'][2];
              }elseif( $s['nr'] >= 1.00 && $s['nr'] <=1.99){
                $valMatch = $tfn['Low'][0];
                $seperK = $tfn['Low'][1];
                $valMatchMax = $tfn['Low'][2];
              }elseif($s['nr'] >= 0.00 && $s['nr'] <=0.99){
                $valMatch = $tfn['Very Low'][0];
                $seperK = $tfn['Very Low'][1];
                $valMatchMax = $tfn['Very Low'][2];
              }

              if(isset($valMatch)){
                /* Menghitung nilai minimal test_ip */
                if ((!isset($hasilAkhir[$s['nama']]["test_ip_min"])) || ($hasilAkhir[$s['nama']]["test_ip_min"] > $valMatch)) {
                  $hasilAkhir[$s['nama']]["test_ip_min"] = $valMatch;
                }

                /* Menghitung total test_ip */
                if (!isset($hasilAkhir[$s['nama']]["total_test_ip"])) {
                  $hasilAkhir[$s['nama']]["total_test_ip"] = $seperK;
                } else {
                  $hasilAkhir[$s['nama']]["total_test_ip"] += $seperK;
                }

                /* Mencari Maximal IP */
                if ((!isset($hailAkhir[$s['nama']]['test_ip_max'])) || ($hasilAkhir[$s['nama']]['test_ip_max'] < $valMatchMax)) {
                  $hasilAkhir[$s['nama']]['test_ip_max'] = $valMatchMax;
                }
              }
            }

            $valMatch = null;
            $seperK = null;
            $valMatchMax = null;
            if ($s['ta'] == 2017 && $s['sem_ta']==2 || $s['sem_ta']==1) {
              //  Very Low
              if($s['akumulasi_skor'] >=0 && $s['akumulasi_skor'] <=5){
                $valMatch = $tfn['Very High'][0];
                $seperK = $tfn['Very High'][1];
                $valMatchMax = $tfn['Very High'][2];
              }elseif( $s['akumulasi_skor'] >=6 && $s['akumulasi_skor'] <=10){
                $valMatch = $tfn['High'][0];
                $seperK = $tfn['High'][1];
                $valMatchMax = $tfn['High'][2];
              }elseif( $s['akumulasi_skor'] >=11 && $s['akumulasi_skor'] <=15){
                $valMatch = $tfn['Average'][0];
                $seperK = $tfn['Average'][1];
                $valMatchMax = $tfn['Average'][2];
              }elseif( $s['akumulasi_skor'] >=16 && $s['akumulasi_skor'] <=25){
                $valMatch = $tfn['Low'][0];
                $seperK = $tfn['Low'][1];
                $valMatchMax = $tfn['Low'][2];
              }elseif( $s['akumulasi_skor'] >=26 && $s['akumulasi_skor'] <= 100){
                $valMatch = $tfn['Very Low'][0];
                $seperK = $tfn['Very Low'][1];
                $valMatchMax = $tfn['Very Low'][2];
              }
            }
            if(isset($valMatch)){
              /* Menghitung nilai minimal test_perilaku */
              if ((!isset($hasilAkhir[$s['nama']]["test_prilaku_min"])) || ($hasilAkhir[$s['nama']]["test_prilaku_min"] > $valMatch)) {
                $hasilAkhir[$s['nama']]["test_prilaku_min"] = $valMatch;
              }

              /* Menghitung total test_perilaku */
              if (!isset($hasilAkhir[$s['nama']]["total_test_prilaku"])) {
                $hasilAkhir[$s['nama']]["total_test_prilaku"] = $seperK;
              } else {
                $hasilAkhir[$s['nama']]["total_test_prilaku"] += $seperK;
              }
              if ((!isset($hasilAkhir[$s['nama']]['test_prilaku_max'])) || ($hasilAkhir[$s['nama']]['test_prilaku_max'] < $valMatchMax)) {
                $hasilAkhir[$s['nama']]['test_prilaku_max'] = $valMatchMax;
              }
            }
          }



          foreach ($hasilAkhir as $key => $value) {
          $Cij[] = $value['test_ip_max'];
          $Aij[] = $value['test_prilaku_min'];
          }


          $Cj = max($Cij);
          $Aj = min($Aij);


          foreach ($hasilAkhir as $key => $value) {
            $Rij1_IP[] = $tfn['Very High'][0] * $value['test_ip_min'] / $Cj;
            $Rij2_IP[] = $tfn['Very High'][1] * (1/3 * $value['total_test_ip']) / $Cj;
            $Rij3_IP[] = $tfn['Very High'][2] * $value['test_ip_max'] / $Cj;


            $Rij1_Prilaku[] = $tfn['Very Low'][2] * $Aj / $value['test_prilaku_min'];
            $Rij2_Prilaku[] = $tfn['Very Low'][1] * $Aj / (1/3 * $value['total_test_prilaku']);
            $Rij3_Prilaku[] = $tfn['Very Low'][0] * $Aj / $value['test_prilaku_max'];
          }


          $FPIS_IP_dan_Prilaku = [max($Rij1_IP), max($Rij2_IP), max($Rij3_IP), max($Rij3_Prilaku), max($Rij2_Prilaku), max($Rij1_Prilaku)];
          $FNIS_IP_dan_Prilaku = [min($Rij1_IP), min($Rij2_IP), min($Rij3_IP), min($Rij3_Prilaku), min($Rij2_Prilaku), min($Rij1_Prilaku)];

          foreach ($hasilAkhir as $key => $value) {
            //FPIS IP
            $fpis1 = pow($tfn['Very High'][0] * $value['test_ip_min'] / $Cj - $FPIS_IP_dan_Prilaku[0], 2);
            $fpis2 = pow($tfn['Very High'][1] * (1/3 * $value['total_test_ip']) / $Cj - $FPIS_IP_dan_Prilaku[1], 2);
            $fpis3 = pow($tfn['Very High'][2] * $value['test_ip_max'] / $Cj - $FPIS_IP_dan_Prilaku[2], 2);
            $totalIP_fpis = sqrt(1/3 * ($fpis1 + $fpis2 + $fpis3));

            //FPIS PRILAKU
            $fpis11 = pow($tfn['Very Low'][2] * $Aj / $value['test_prilaku_min'] - $FPIS_IP_dan_Prilaku[3], 2);
            $fpis12 = pow($tfn['Very Low'][1] * $Aj / (1/3 * $value['total_test_prilaku']) - $FPIS_IP_dan_Prilaku[4], 2);
            $fpis13 = pow($tfn['Very Low'][0] * $Aj / $value['test_prilaku_max'] - $FPIS_IP_dan_Prilaku[5], 2);
            $totalPrilaku_fpis = sqrt(1/3 * ($fpis11 + $fpis12 + $fpis13));

            //FNIS IP
            $fnis1 = pow($tfn['Very High'][0] * $value['test_ip_min'] / $Cj - $FNIS_IP_dan_Prilaku[0], 2);
            $fnis2 = pow($tfn['Very High'][1] * (1/3 * $value['total_test_ip']) / $Cj - $FNIS_IP_dan_Prilaku[1], 2);
            $fnis3 = pow($tfn['Very High'][2] * $value['test_ip_max'] / $Cj - $FNIS_IP_dan_Prilaku[2], 2);
            $totalIP_fnis = sqrt(1/3 * ($fnis1 + $fnis2 + $fnis3));


            //FNIS PRILAKU
            $fnis11 = pow($tfn['Very Low'][0] * $Aj / $value['test_prilaku_min'] - $FNIS_IP_dan_Prilaku[3],2);
            $fnis12 = pow($tfn['Very Low'][1] * $Aj / (1/3 * $value['total_test_prilaku']) - $FNIS_IP_dan_Prilaku[4],2);
            $fnis13 = pow($tfn['Very Low'][2] * $Aj / $value['test_prilaku_max'] - $FNIS_IP_dan_Prilaku[5],2);
            $totalPrilaku_fnis = sqrt( 1/3 * ($fnis11 + $fnis12 + $fnis13));

            $dBintang = $totalIP_fpis + $totalPrilaku_fpis;
            $dmin = $totalIP_fnis + $totalPrilaku_fnis;
            $Cci[] = $dmin / ($dmin + $dBintang);

          }



          $saw2 = DimPenilaian::selectRaw("
          askm_dim_penilaian.akumulasi_skor,
          askm_dim_penilaian.dim_id,
          askm_dim_penilaian.ta,
          askm_dim_penilaian.sem_ta");
          $query2 = AdakRegistrasi::selectRaw("skkm.id AS skkm_id,skkm.skkm, dimx_dim.dim_id, dimx_dim.nama,adak_registrasi.ta,(SUM(adak_registrasi.nr)/4) AS IPK,
          adak_registrasi.sem_ta, adak_registrasi.nr, p.akumulasi_skor")
              ->join('dimx_dim', 'dimx_dim.dim_id', 'adak_registrasi.dim_id')
              ->leftJoin('skkm', 'skkm.dim_id', 'dimx_dim.dim_id')
              ->leftJoin(\DB::raw("(" . $saw2->toSql() . ") as p"), function ($query2) {
                  $query2->on('p.dim_id', '=', 'adak_registrasi.dim_id');
                  $query2->on('p.ta', '=', 'adak_registrasi.ta');
                  $query2->on('p.sem_ta', '=', 'adak_registrasi.sem_ta');
              })
              ->orderBy('dimx_dim.nama','asc')
              ->groupBy('dimx_dim.dim_id')
              ->get();

          foreach ($query2 as $key => $value) {
            $data_mahasiswa[] = $value;
          }


          foreach ($data_mahasiswa as $key => &$value) {
            $value["cci"] = $Cci[$key];
          }


          // uasort($data_mahasiswa, function($a, $b){
          //   return $a["cci"] <=> $b["cci"];
          // });

          $key = array_column($data_mahasiswa, 'cci');
          array_multisort($key, SORT_DESC, $data_mahasiswa);
          $krt2 = array_slice($data_mahasiswa, 0, 50);

          return view('seleksi_awal_ft7',['krt2'=>$krt2]);
    }



    public function hasilAkhirFT()
    {
      $data = $this->hasilAwal();
      $dtMhs = $data['krt2'];


      $tfn = [
        "Very High"=>[7,9,9],
        "High"=>[5,7,9],
        "Average"=>[3,5,7],
        "Low"=>[1,3,5],
        "Very Low"=>[1,1,3]
      ];

      $hasilAkhir = [];
      foreach ($dtMhs as $s) {
        $valMatch = null;
        $seperK = null;
        $valMatchMax = null;

          if ($s['cci'] >= 0.81 && $s['cci'] <= 1) {
            $valMatch = $tfn['Very High'][0];
            $seperK = $tfn['Very High'][1];
            $valMatchMax = $tfn['Very High'][2];
          }elseif($s['cci'] >= 0.61 && $s['cci'] <= 0.80){
            $valMatch = $tfn['High'][0];
            $seperK = $tfn['High'][1];
            $valMatchMax = $tfn['High'][2];
          }elseif( $s['cci'] >= 0.41 && $s['cci'] <= 0.60){
            $valMatch = $tfn['Average'][0];
            $seperK = $tfn['Average'][1];
            $valMatchMax = $tfn['Average'][2];
          }elseif( $s['cci'] >= 0.21 && $s['cci'] <= 0.40){
            $valMatch = $tfn['Low'][0];
            $seperK = $tfn['Low'][1];
            $valMatchMax = $tfn['Low'][2];
          }elseif($s['cci'] >= 0.00 && $s['cci'] <= 0.20){
            $valMatch = $tfn['Very Low'][0];
            $seperK = $tfn['Very Low'][1];
            $valMatchMax = $tfn['Very Low'][2];
          }

          if(isset($valMatch)){
            /* Menghitung nilai minimal test_hasilAwal */
            if ((!isset($hasilAkhir[$s['nama']]["test_hasilAwal_min"])) || ($hasilAkhir[$s['nama']]["test_hasilAwal_min"] > $valMatch)) {
              $hasilAkhir[$s['nama']]["test_hasilAwal_min"] = $valMatch;
            }

            /* Menghitung total test_hasilAwal */
            if (!isset($hasilAkhir[$s['nama']]["total_test_hasilAwal"])) {
              $hasilAkhir[$s['nama']]["total_test_hasilAwal"] = $seperK;
            } else {
              $hasilAkhir[$s['nama']]["total_test_hasilAwal"] += $seperK;
            }

            /* Mencari Maximal hasilAwal */
            if ((!isset($hailAkhir[$s['nama']]['test_hasilAwal_max'])) || ($hasilAkhir[$s['nama']]['test_hasilAwal_max'] < $valMatchMax)) {
              $hasilAkhir[$s['nama']]['test_hasilAwal_max'] = $valMatchMax;
            }
          }


          $valMatch = null;
          $seperK = null;
          $valMatchMax = null;

          if($s['skkm'] > 35){
            $valMatch = $tfn['Very High'][0];
            $seperK = $tfn['Very High'][1];
            $valMatchMax = $tfn['Very High'][2];
          }
          elseif($s['skkm'] >= 29 && $s['skkm'] <=35){
            $valMatch = $tfn['High'][0];
            $seperK = $tfn['High'][1];
            $valMatchMax = $tfn['High'][2];
          }
          elseif($s['skkm'] >= 22 && $s['skkm'] <= 28){
            $valMatch = $tfn['Average'][0];
            $seperK = $tfn['Average'][1];
            $valMatchMax = $tfn['Average'][2];
          }
          elseif($s['skkm'] >= 15 && $s['skkm'] <= 21){
            $valMatch = $tfn['Low'][0];
            $seperK = $tfn['Low'][1];
            $valMatchMax = $tfn['Low'][2];
          }
          elseif($s['skkm'] >= 8 && $s['skkm'] <=14){
            $valMatch = $tfn['Very Low'][0];
            $seperK = $tfn['Very Low'][1];
            $valMatchMax = $tfn['Very Low'][2];
          }

          if(isset($valMatch)){
            /* Menghitung nilai minimal test_perilaku */
            if ((!isset($hasilAkhir[$s['nama']]["test_skkm_min"])) || ($hasilAkhir[$s['nama']]["test_skkm_min"] > $valMatch)) {
              $hasilAkhir[$s['nama']]["test_skkm_min"] = $valMatch;
            }

            /* Menghitung total test_perilaku */
            if (!isset($hasilAkhir[$s['nama']]["total_test_skkm"])) {
              $hasilAkhir[$s['nama']]["total_test_skkm"] = $seperK;
            } else {
              $hasilAkhir[$s['nama']]["total_test_prilaku"] += $seperK;
            }
            if ((!isset($hasilAkhir[$s['nama']]['test_skkm_max'])) || ($hasilAkhir[$s['nama']]['test_skkm_max'] < $valMatchMax)) {
              $hasilAkhir[$s['nama']]['test_skkm_max'] = $valMatchMax;
            }
          }
      }


      foreach ($hasilAkhir as $key => $value) {
      $Cij[] = $value['test_hasilAwal_max'];
      $Aij[] = $value['test_skkm_min'];
      }


      $Cj = max($Cij);
      $Aj = min($Aij);


      foreach ($hasilAkhir as $key => $value) {
        $Rij1_hasilAwal[] = $tfn['Very High'][0] * $value['test_hasilAwal_min'] / $Cj;
        $Rij2_hasilAwal[] = $tfn['Very High'][1] * (1/3 * $value['total_test_hasilAwal']) / $Cj;
        $Rij3_hasilAwal[] = $tfn['Very High'][2] * $value['test_hasilAwal_max'] / $Cj;


        $Rij1_skkm[] = $tfn['Very High'][2] * $Aj / $value['test_skkm_min'];
        $Rij2_skkm[] = $tfn['Very High'][1] * $Aj / (1/3 * $value['total_test_skkm']);
        $Rij3_skkm[] = $tfn['Very High'][0] * $Aj / $value['test_skkm_max'];
      }


      $FPIS_hasilAwal_dan_skkm = [max($Rij1_hasilAwal), max($Rij2_hasilAwal), max($Rij3_hasilAwal), max($Rij3_skkm), max($Rij2_skkm), max($Rij1_skkm)];
      $FNIS_hasilAwal_dan_skkm = [min($Rij1_hasilAwal), min($Rij2_hasilAwal), min($Rij3_hasilAwal), min($Rij3_skkm), min($Rij2_skkm), min($Rij1_skkm)];

      foreach ($hasilAkhir as $key => $value) {
        //FPIS IP
        $fpis1 = pow($tfn['Very High'][0] * $value['test_hasilAwal_min'] / $Cj - $FPIS_hasilAwal_dan_skkm[0], 2);
        $fpis2 = pow($tfn['Very High'][1] * (1/3 * $value['total_test_hasilAwal']) / $Cj - $FPIS_hasilAwal_dan_skkm[1], 2);
        $fpis3 = pow($tfn['Very High'][2] * $value['test_hasilAwal_max'] / $Cj - $FPIS_hasilAwal_dan_skkm[2], 2);
        $totalhasilAwal_fpis = sqrt(1/3 * ($fpis1 + $fpis2 + $fpis3));

        //FPIS PRILAKU
        $fpis11 = pow($tfn['Very Low'][2] * $Aj / $value['test_skkm_min'] - $FPIS_hasilAwal_dan_skkm[3], 2);
        $fpis12 = pow($tfn['Very Low'][1] * $Aj / (1/3 * $value['total_test_skkm']) - $FPIS_hasilAwal_dan_skkm[4], 2);
        $fpis13 = pow($tfn['Very Low'][0] * $Aj / $value['test_skkm_max'] - $FPIS_hasilAwal_dan_skkm[5], 2);
        $totalskkm_fpis = sqrt(1/3 * ($fpis11 + $fpis12 + $fpis13));

        //FNIS IP
        $fnis1 = pow($tfn['Very High'][0] * $value['test_hasilAwal_min'] / $Cj - $FNIS_hasilAwal_dan_skkm[0], 2);
        $fnis2 = pow($tfn['Very High'][1] * (1/3 * $value['total_test_hasilAwal']) / $Cj - $FNIS_hasilAwal_dan_skkm[1], 2);
        $fnis3 = pow($tfn['Very High'][2] * $value['test_hasilAwal_max'] / $Cj - $FNIS_hasilAwal_dan_skkm[2], 2);
        $totalhasilAwal_fnis = sqrt(1/3 * ($fnis1 + $fnis2 + $fnis3));


        //FNIS PRILAKU
        $fnis11 = pow($tfn['Very Low'][0] * $Aj / $value['test_skkm_min'] - $FNIS_hasilAwal_dan_skkm[3],2);
        $fnis12 = pow($tfn['Very Low'][1] * $Aj / (1/3 * $value['total_test_skkm']) - $FNIS_hasilAwal_dan_skkm[4],2);
        $fnis13 = pow($tfn['Very Low'][2] * $Aj / $value['test_skkm_max'] - $FNIS_hasilAwal_dan_skkm[5],2);
        $totalskkm_fnis = sqrt( 1/3 * ($fnis11 + $fnis12 + $fnis13));

        $dBintang = $totalhasilAwal_fpis + $totalskkm_fpis;
        $dmin = $totalhasilAwal_fnis + $totalskkm_fnis;
        $Cci[] = $dmin / ($dmin + $dBintang);

      }

      foreach ($hasilAkhir as $key => $value) {
        $data_mahasiswa[] = $key;
      }

      $combineData2 = array_combine($data_mahasiswa, $Cci);

      arsort($combineData2);
      $krt2 = array_slice($combineData2, 0,10);
      return view('hasil_akhir_ft',['hasilFinals' => $krt2]);

    }

    // public function paginate($items, int $perPage) : LengthAwarePaginator
    // {
    //   $items = $items instanceof Collection ? $items : Collection::make($items);
    //
    //   $currentPage = LengthAwarePaginator::resolveCurrentPage();
    //
    //   $currentPageItems = $items->slice(($currentPage - 1) * $perPage, $perPage);
    //
    //   $paginator = new LengthAwarePaginator(
    //
    //     $currentPageItems, $items->count(), $perPage, $currentPage
    //   );
    //   return $paginator;
    // }

    public function PerhitunganFT()
    {
      $dimx_dim = DimPenilaian::selectRaw("
      askm_dim_penilaian.akumulasi_skor,
      askm_dim_penilaian.dim_id,
      askm_dim_penilaian.ta,
      askm_dim_penilaian.sem_ta");
        $query = AdakRegistrasi::selectRaw("skkm.id AS skkm_id,skkm.skkm,dimx_dim.nama,dimx_dim.dim_id,adak_registrasi.ta,adak_registrasi.nr AS IPK, adak_registrasi.sem_ta, adak_registrasi.nr, p.akumulasi_skor")

            ->join('dimx_dim', 'dimx_dim.dim_id', 'adak_registrasi.dim_id')
            ->leftJoin('skkm', 'skkm.dim_id', 'dimx_dim.dim_id')
            ->leftJoin(\DB::raw("(" . $dimx_dim->toSql() . ") as p"), function ($query) {
                $query->on('p.dim_id', '=', 'adak_registrasi.dim_id');
                $query->on('p.ta', '=', 'adak_registrasi.ta');
                $query->on('p.sem_ta', '=', 'adak_registrasi.sem_ta');
            })
            ->orderBy('dimx_dim.nama','asc')
            ->get();

            $tfn = [
              "Very High"=>[7,9,9],
              "High"=>[5,7,9],
              "Average"=>[3,5,7],
              "Low"=>[1,3,5],
              "Very Low"=>[1,1,3]
            ];

        $hasilAkhir = [];
        foreach ($query as $s) {
          $valMatch = null;
          $seperK = null;
          $valMatchMax = null;

          if ($s['ta'] == 2017 && $s['sem_ta']== 2 || $s['sem_ta']== 1) {
            if ($s['nr'] >= 3.50 && $s['nr'] <= 4.00) {
              $valMatch = $tfn['Very High'][0];
              $seperK = $tfn['Very High'][1];
              $valMatchMax = $tfn['Very High'][2];
            }elseif($s['nr'] >= 3.00 && $s['nr'] <=3.49){
              $valMatch = $tfn['High'][0];
              $seperK = $tfn['High'][1];
              $valMatchMax = $tfn['High'][2];
            }elseif( $s['nr'] >= 2.00 && $s['nr'] <= 2.99){
              $valMatch = $tfn['Average'][0];
              $seperK = $tfn['Average'][1];
              $valMatchMax = $tfn['Average'][2];
            }elseif( $s['nr'] >= 1.00 && $s['nr'] <=1.99){
              $valMatch = $tfn['Low'][0];
              $seperK = $tfn['Low'][1];
              $valMatchMax = $tfn['Low'][2];
            }elseif($s['nr'] >= 0.00 && $s['nr'] <=0.99){
              $valMatch = $tfn['Very Low'][0];
              $seperK = $tfn['Very Low'][1];
              $valMatchMax = $tfn['Very Low'][2];
            }

            if(isset($valMatch)){
              /* Menghitung nilai minimal test_ip */
              if ((!isset($hasilAkhir[$s['nama']]["test_ip_min"])) || ($hasilAkhir[$s['nama']]["test_ip_min"] > $valMatch)) {
                $hasilAkhir[$s['nama']]["test_ip_min"] = $valMatch;
              }

              /* Menghitung total test_ip */
              if (!isset($hasilAkhir[$s['nama']]["total_test_ip"])) {
                $hasilAkhir[$s['nama']]["total_test_ip"] = $seperK;
              } else {
                $hasilAkhir[$s['nama']]["total_test_ip"] += $seperK;
              }

              /* Mencari Maximal IP */
              if ((!isset($hailAkhir[$s['nama']]['test_ip_max'])) || ($hasilAkhir[$s['nama']]['test_ip_max'] < $valMatchMax)) {
                $hasilAkhir[$s['nama']]['test_ip_max'] = $valMatchMax;
              }
            }
          }

          $valMatch = null;
          $seperK = null;
          $valMatchMax = null;
          if ($s['ta'] == 2017 && $s['sem_ta']==2 || $s['sem_ta']==1) {
            //  Very Low
            if($s['akumulasi_skor'] >=0 && $s['akumulasi_skor'] <=5){
              $valMatch = $tfn['Very High'][0];
              $seperK = $tfn['Very High'][1];
              $valMatchMax = $tfn['Very High'][2];
            }elseif( $s['akumulasi_skor'] >=6 && $s['akumulasi_skor'] <=10){
              $valMatch = $tfn['High'][0];
              $seperK = $tfn['High'][1];
              $valMatchMax = $tfn['High'][2];
            }elseif( $s['akumulasi_skor'] >=11 && $s['akumulasi_skor'] <=15){
              $valMatch = $tfn['Average'][0];
              $seperK = $tfn['Average'][1];
              $valMatchMax = $tfn['Average'][2];
            }elseif( $s['akumulasi_skor'] >=16 && $s['akumulasi_skor'] <=25){
              $valMatch = $tfn['Low'][0];
              $seperK = $tfn['Low'][1];
              $valMatchMax = $tfn['Low'][2];
            }elseif( $s['akumulasi_skor'] >=26 && $s['akumulasi_skor'] <= 100){
              $valMatch = $tfn['Very Low'][0];
              $seperK = $tfn['Very Low'][1];
              $valMatchMax = $tfn['Very Low'][2];
            }
          }
          if(isset($valMatch)){
            /* Menghitung nilai minimal test_perilaku */
            if ((!isset($hasilAkhir[$s['nama']]["test_perilaku_min"])) || ($hasilAkhir[$s['nama']]["test_perilaku_min"] > $valMatch)) {
              $hasilAkhir[$s['nama']]["test_perilaku_min"] = $valMatch;
            }

            /* Menghitung total test_perilaku */
            if (!isset($hasilAkhir[$s['nama']]["total_test_perilaku"])) {
              $hasilAkhir[$s['nama']]["total_test_perilaku"] = $seperK;
            } else {
              $hasilAkhir[$s['nama']]["total_test_perilaku"] += $seperK;
            }
            if ((!isset($hasilAkhir[$s['nama']]['test_prilaku_max'])) || ($hasilAkhir[$s['nama']]['test_prilaku_max'] < $valMatchMax)) {
              $hasilAkhir[$s['nama']]['test_prilaku_max'] = $valMatchMax;
            }
          }
        }

      return view("seleksi_awal_ft",['semua'=>$query,'tfn'=>$tfn,'hasilAkhir'=>$hasilAkhir]);
    }


    public function sawPage()
    {
        $kriteria_saw = kriteria::all();
        $data = $this->Mahasiswa();
        return view('sawPage', ['krt' => $data], ['vdata' => $kriteria_saw]);
    }


    public function Penilaian()
    {
      $data = $this->mahasiswa();
      $dataDetail = $data['dataMahasiswa'];

      foreach ($dataDetail as $key => $value) {
        $dataMahasiswas[] = $value;
        $IP_max[] = $value['ipTotal'];
        $Prilaku_min[] = $value['akumulasi_skor'];
      }

      foreach ($dataDetail as $key => $value) {
        $IP = ($value['ipTotal'] / max($IP_max)) * 0.5;
        if ($value['akumulasi_skor'] == 0) {
          $value['akumulasi_skor'] = 0.001;
        }
        $Prilaku = (min($Prilaku_min) / $value['akumulasi_skor']) * 0.5;
        $hasil[] = $IP + $Prilaku;
      }

      foreach ($dataMahasiswas as $key => &$value) {
        $value["hasil_awal_saw"] = $hasil[$key];
      }

      $key = array_column($dataMahasiswas, 'hasil_awal_saw');
      array_multisort($key, SORT_DESC, $dataMahasiswas);
      $krt2 = array_slice($dataMahasiswas, 0, 20);
      return view('SAW.seleksi_awal_saw',['data_20_besar'=>$krt2]);
    }


    public function hasilAkhirSaw(){
      $data = $this->Penilaian();
      $dataDtl = $data['data_20_besar'];

      foreach ($dataDtl as $key => $value) {
        $dataMahasiswas[] = $value;
        $hasilAkhirSAW_max[] = $value['hasil_awal_saw'];
        $skkm_max[] = $value['skkm'];
      }


      foreach ($dataDtl as $key => $value) {
        $hasilAkhirSAW = ($value['hasil_awal_saw'] / max($hasilAkhirSAW_max)) * 0.5;
        $skkm = ($value['skkm'] / max($skkm_max)) * 0.5;
        $hasil[] = $hasilAkhirSAW + $skkm;
      }


      foreach ($dataMahasiswas as $key => &$value) {
        $value["hasil_akhir_saw"] = $hasil[$key];
      }


      $key = array_column($dataMahasiswas, 'hasil_akhir_saw');

      array_multisort($key, SORT_DESC, $dataMahasiswas);

      $krt2 = array_slice($dataMahasiswas, 0, 10);

      return view('SAW.seleksi_akhir_saw',['hasil_akhir_saw'=>$krt2]);
    }



    public function Mahasiswa()
    {
      $saw = DimPenilaian::selectRaw("
    askm_dim_penilaian.akumulasi_skor,
    askm_dim_penilaian.dim_id,
    askm_dim_penilaian.ta,
    askm_dim_penilaian.sem_ta");
      $query = AdakRegistrasi::selectRaw("skkm.id AS skkm_id,skkm.skkm as skkm,dimx_dim.dim_id,dimx_dim.nama,adak_registrasi.ta,adak_registrasi.nr AS IPK, adak_registrasi.sem_ta, adak_registrasi.nr, p.akumulasi_skor")
          ->join('dimx_dim', 'dimx_dim.dim_id', 'adak_registrasi.dim_id')
          ->leftJoin('skkm', 'skkm.dim_id', 'dimx_dim.dim_id')
          ->leftJoin(\DB::raw("(" . $saw->toSql() . ") as p"), function ($query) {
              $query->on('p.dim_id', '=', 'adak_registrasi.dim_id');
              $query->on('p.ta', '=', 'adak_registrasi.ta');
              $query->on('p.sem_ta', '=', 'adak_registrasi.sem_ta');
          })
          ->orderBy('dimx_dim.nama','asc')
          ->get();

          $dataM = [];
            foreach ($query as $dt_mhs) {
              $ipsem1 = null;
              $ipsem2 = null;
              $ipsem3 = null;

                if ($dt_mhs['ta'] == 2017 && $dt_mhs['sem_ta'] == 1) {
                  $ipsem1 = $dt_mhs['nr'];
                }
                if ($dt_mhs['ta'] == 2017 && $dt_mhs['sem_ta'] == 2) {
                  $ipsem2 = $dt_mhs['nr'];
                }
                if ($dt_mhs['ta'] == 2018 && $dt_mhs['sem_ta'] == 1) {
                  $ipsem3 = $dt_mhs['nr'];
                }
                if (isset($ipsem1)) {
                  $dataM[$dt_mhs['nama']]['ipsem1'] = $ipsem1;
                }
                if (isset($ipsem2)) {
                  $dataM[$dt_mhs['nama']]['ipsem2'] = $ipsem2;
                }
                if (isset($ipsem3)) {
                  $dataM[$dt_mhs['nama']]['ipsem3'] = $ipsem3;
                }
            }

            foreach ($dataM as $key => $value) {
              $IPK[] = ($value['ipsem1'] + $value['ipsem2'] + $value['ipsem3']) / 3 ;
            }

            $saw2 = DimPenilaian::selectRaw("
            askm_dim_penilaian.akumulasi_skor,
            askm_dim_penilaian.dim_id,
            askm_dim_penilaian.ta,
            askm_dim_penilaian.sem_ta");
            $query2 = AdakRegistrasi::selectRaw("skkm.id AS skkm_id,skkm.skkm, dimx_dim.dim_id, dimx_dim.nama,adak_registrasi.ta,(SUM(adak_registrasi.nr)/4) AS IPK,
            adak_registrasi.sem_ta, adak_registrasi.nr, p.akumulasi_skor")
                ->join('dimx_dim', 'dimx_dim.dim_id', 'adak_registrasi.dim_id')
                ->leftJoin('skkm', 'skkm.dim_id', 'dimx_dim.dim_id')
                ->leftJoin(\DB::raw("(" . $saw2->toSql() . ") as p"), function ($query2) {
                    $query2->on('p.dim_id', '=', 'adak_registrasi.dim_id');
                    $query2->on('p.ta', '=', 'adak_registrasi.ta');
                    $query2->on('p.sem_ta', '=', 'adak_registrasi.sem_ta');
                })
                ->orderBy('dimx_dim.nama','asc')
                ->groupBy('dimx_dim.dim_id')
                ->get();

                foreach ($query2 as $key) {

                  $data_mahasiswa[] = $key;
                }

                foreach ($data_mahasiswa as $key => &$value) {
                  $value["ipTotal"] = $IPK[$key];
                }


            return view('SAW.sawPage', ['dataMahasiswa' => $data_mahasiswa]);
    }
}

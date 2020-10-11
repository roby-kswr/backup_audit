<?php

class Laporan_pertanggung_jawaban extends Controller
{
  
  private $table      = "dja_pagu";

  private $table2     = "view_spk";

  private $primaryKey = "id";

  private $menu       = "Analisa";

   private $model      = "laporan_pertanggung_jawaban_model";

  private $title      = "Laporan Pelaksanaan Kerja";

  private $curl       = BASE_URL."laporan_pertanggung_jawaban";

  

  function __construct()
   {
      $session = $this->loadHelper('Session_helper');

        if(!$session->get('username')){

         $this->redirect('auth/login');

      }
   }

   function index()
   {
      $model = $this->loadModel($this->model);

      $data                = array();
      $data['breadcrumb1'] = $this->menu;
      $data['curl']        = $this->curl;
      $data['title']       = $this->title;
      // $data['satker']      = $model->satker();
      $template            = $this->loadView('lap_pertanggung_jawaban_view');
      $template->set('data', $data);

      $template->render();
   }

   function numberformat($n)
   {
      $sa = 'Rp. '.number_format($n);

      return $sa;
   }

    function detail($detail)
  {
   
    $model = $this->loadModel($this->model);
    $data                = array();
    $data['breadcrumb1'] = $this->menu;
    $data['title']       = $this->title;
    $data['curl']        = $this->curl;
    $data['satker']      = $detail;
    $template            = $this->loadView('lap_pertanggung_jawaban_detil_view');
    $template->set('data', $data);
    $template->render();
  }

   function get($satker)
  {
    $request = $_REQUEST;

    $sat        = $satker;

    $columns = array(
      array( 'db' => 'id', 'dt' => 0, 'formatter' => function( $d, $row ) { return $this->base64url_encode($d); } ),
      array( 'db' => 'nmsatker',  'dt' => 1 ),
      array( 'db' => 'wasgiat',   'dt' => 2 ),
      array( 'db' => 'jml_pagu',   'dt' => 3,'formatter' => function( $d, $row ) { return $this->numberformat($d); } ),
      array( 'db' => 'nilai_hps',   'dt' => 4, 'formatter' => function( $d, $row ) { return $this->numberformat($d); }),
      array( 'db' => 'nilai_rab',   'dt' => 5 ,'formatter' => function( $d, $row ) { return $this->numberformat($d); }),
      array( 'db' => 'nilai_sph',   'dt' => 6, 'formatter' => function( $d, $row ) { return $this->numberformat($d); }),
      array( 'db' => 'nilai_kontrak',   'dt' => 7, 'formatter' => function( $d, $row ) { return $this->numberformat($d); }),
      array( 'db' => 'nilai_sp2d',   'dt' => 8 ,'formatter' => function( $d, $row ) { return $this->numberformat($d); }),
      array( 'db' => 'urskmpnen',   'dt' => 9 ),
    );

    $join   = "AS t1 
    LEFT JOIN (SELECT `kdsatker` AS kode, `nmsatker` FROM dja_satker) AS t2 ON t1.KDSATKER = t2.kode
    LEFT JOIN (SELECT `kd_bidang` AS kode_bid, `wasgiat` AS nm_giat,kd_bidang FROM tbidang) AS t3 ON t1.wasgiat = t3.nm_giat ";
    $model  = $this->loadModel($this->model);
  
    $result  = $model->mgetsat($request, $this->table2, $this->primaryKey, $columns ,$sat, $join);

    return json_encode($result);
  }




  function getProgja()
  {
    $request = $_REQUEST;
    $columns = array(
      array( 'db' => 'id', 'dt' => 0, 'formatter' => function( $d, $row ) { return $this->base64url_encode($d); } ),
      array( 'db' => 'thang',  'dt' => 1 ),
      array( 'db' => 'kdsatker',  'dt' => 2 ),
      array( 'db' => 'SUM(jml_pagu)',   'dt' => 3, 'formatter' => function( $d, $row ) { return number_format($d); } ),
      array( 'db' => 'nm_satminkal',   'dt' => 4 ),
    
    );
    
    $model  = $this->loadModel($this->model);
    $result = $model->loadProgja($request, $this->table, $this->primaryKey, $columns);

    return json_encode($result);
  }

   


}
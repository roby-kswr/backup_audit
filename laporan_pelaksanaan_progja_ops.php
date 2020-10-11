<?php

class Laporan_pelaksanaan_progja_ops extends Controller
{
	
	private $table      = "tbidang";

	private $primaryKey = "id";

	private $menu       = "Analisa";

   private $model      = "laporan_pelaksanaan_progja_ops_model";

	private $title      = "Laporan Pelaksanaan Kerja";

	private $curl       = BASE_URL."laporan_pelaksanaan_progja_ops";

	

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
      $data['satker']      = $model->satker();
      $data['tahun']       = $model->query("SELECT thang FROM dja_pagu GROUP BY thang ORDER BY thang ASC");
      $data['ttd']         = $model->ttd();
      $template            = $this->loadView('laporan_pelaksanaan_progja_ops_view');
      $template->set('data', $data);

      $template->render();
   }

   function numberformat($n)
   {
      $sa = 'Rp. '.number_format($n);

      return $sa;
   }

   function get($tahun,$satker)
   {

      $request    = $_REQUEST;

      $sat        = $satker;

      $id         = $tahun;

      $columns = array(

      array( 'db' => 'autono', 'dt' => 0, 'formatter' => function( $d, $row ) { return $this->base64url_encode($d); } ),
      array( 'db' => 'kd_bidang',  'dt' => 1 ),
      array( 'db' => 'nm_bidang',  'dt' => 2 ),
      array( 'db' => 'jml_pagu',  'dt' => 3, 'formatter' => function( $d, $row ) { return $this->numberformat($d); } ),
      array( 'db' => 'urskmpnen',  'dt' => 4 ),
      array( 'db' => 'thang', 'dt'=> 5 ),
      array( 'db' => 'kdsatker', 'dt'=> 6 ),
      array( 'db' => 'nm_satminkal', 'dt'=> 7 ),
      array( 'db' => 'nmakun', 'dt'=> 8 ),

      );
      $model   = $this->loadModel($this->model);
      $join   = "a LEFT JOIN( SELECT id, wasgiat, jml_pagu,urskmpnen,thang,kdsatker,kdakun FROM dja_pagu ) AS b ON a.wasgiat = b.wasgiat
                   LEFT JOIN (SELECT nm_satminkal,kd_satminkal FROM tsatminkal GROUP BY kd_satminkal ) AS c ON  b.kdsatker = c.kd_satminkal
                   LEFT JOIN (SELECT kdakun AS akun ,nmakun FROM dja_akun GROUP BY akun) AS d ON b.kdakun = d.akun";
      if ($satker != '00') {

          $result  = $model->mgetsat($request, $this->table, $this->primaryKey, $columns ,$id,$sat, $join);
         
      }else{

        $result  = $model->mget($request, $this->table, $this->primaryKey, $columns ,$id,$join);
   
          
     
      }
     

      return json_encode($result);

   }

    function print()
     {
        $pdf = $this->loadLibrary('fpdf');

        $tahun    = $_REQUEST['filtertahun'];
        $satker   = $_REQUEST['filtersatker'];

        $exp = explode('-', $tahun);
        $ss = $exp[1];
        // membuat halaman baru
        $pdf->AddPage('P','A4');
        // setting jenis font yang akan digunakan
        $pdf->setX(23);

        $pdf->Image(ROOT_DIR.'static/images/mabesad.png',18,10,18);
        
        // Arial bold 12
       $pdf->Ln(1);
       $pdf->SetFont('Arial','B',18);
        
        // Geser Ke Kanan 35mm
       $pdf->Cell(60);
        
        // Judul

       $pdf->Cell(80,7,'E-AUDIT TNI AD',0,1,'C');
       $pdf->Cell(60);
       $pdf->SetFont('Arial','B',13);
       $pdf->Cell(80,10,'INSPEKTORAT JENDRAL ANGKATAN DARAT',0,1,'C');
        // Garis Bawah Double
       $pdf->Ln(5);
       $pdf->Cell(190,0,'','B',1,'L');
       $pdf->Cell(190,1,'','B',1,'L');
       // $pdf->Cell(260,1,'','B',0,'L');
        
        // Line break 5mm
        $pdf->Ln(3);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(2); 
        $pdf->Cell(195,7,'LAPORAN PELAKSANAAN PROGJA WASRIK '.$tahun,0,1,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln(2);
        
        //header
        $pdf->Cell(12, 10, 'NO', 'LRT', 0, 'C');
        $pdf->Cell(35, 10, 'NAMA SATKER', 'LRT', 0, 'C');
        $pdf->Cell(60, 10, 'NAMA URAIAN', 'LRT', 0, 'C');
        $pdf->Cell(50, 10, 'NAMA AKUN', 'LRT', 0, 'C');
        $pdf->Cell(35, 10, 'NILAI PAGU', 'LRT', 0, 'C');
        // Total Max 880 Kertas A4
        $pdf->Ln();

     


        $pdf->SetY(35);
        $pdf->Ln(5);         
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetY(63);
        $pdf->Cell(12, 5, '1', 'LRTB',0,'C');
        $pdf->Cell(35, 5, '2', 'LRTB', 0, 'C');
        $pdf->Cell(60, 5, '3', 'LRTB', 0, 'C');
        $pdf->Cell(50, 5, '4', 'LRTB', 0, 'C');
        $pdf->Cell(35, 5, '5', 'LRTB', 0, 'C');
        $pdf->Cell(30, 7, '', 0, 0);
        $pdf->Ln(5);


        //body

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetWidths(array(12, 35, 60,50,35));
        $pdf->SetAligns(array('C', 'C', 'C', 'C','C'));


        $model = $this->loadModel($this->model);

        if ($satker == 00) {
           $query = $model->query("SELECT id, kd_bidang,nm_bidang, FORMAT(jml_pagu,0) AS jumlah, urskmpnen,thang,kdsatker,nm_satminkal,nmakun FROM tbidang a 
                    LEFT JOIN( SELECT id, wasgiat, jml_pagu,urskmpnen,thang,kdsatker,kdakun FROM dja_pagu ) AS b ON a.wasgiat = b.wasgiat
                   LEFT JOIN (SELECT nm_satminkal,kd_satminkal FROM tsatminkal GROUP BY kd_satminkal ) AS c ON  b.kdsatker = c.kd_satminkal
                   LEFT JOIN (SELECT kdakun AS akun ,nmakun FROM dja_akun GROUP BY akun) AS d ON b.kdakun = d.akun
              WHERE a.kd_bidang  = 02 AND b.thang = '$tahun' GROUP BY b.kdakun ORDER BY nm_satminkal ASC");

        $no = 1;
        foreach ($query as $row){
          // $pdf->setX(20);
           $pdf->Row(
            array($no++,
            $row['nm_satminkal'],
            $row['urskmpnen'],
            $row['nmakun'],
            'Rp. '.$row['jumlah']           
        ));

        }
        }else{
          $query = $model->query("SELECT id, kd_bidang,nm_bidang, FORMAT(jml_pagu,0) AS jumlah, urskmpnen,thang,kdsatker,nm_satminkal,nmakun FROM tbidang a 
                   LEFT JOIN( SELECT id, wasgiat, jml_pagu,urskmpnen,thang,kdsatker,kdakun FROM dja_pagu ) AS b ON a.wasgiat = b.wasgiat
                   LEFT JOIN (SELECT nm_satminkal,kd_satminkal FROM tsatminkal GROUP BY kd_satminkal ) AS c ON  b.kdsatker = c.kd_satminkal
                   LEFT JOIN (SELECT kdakun AS akun ,nmakun FROM dja_akun GROUP BY akun) AS d ON b.kdakun = d.akun
                   WHERE a.kd_bidang  = 02 AND c.kd_satminkal = '$satker' AND b.thang = '$tahun' GROUP BY b.kdakun ORDER BY nm_satminkal ASC");

        $no = 1;
        foreach ($query as $row){
          // $pdf->setX(20);
           $pdf->Row(
            array($no++,
            $row['nm_satminkal'],
            $row['urskmpnen'],
            $row['nmakun'],
            'Rp. '.$row['jumlah']           
        ));

        }

        }


        $ttd = $_REQUEST['ttd'];

        if ($ttd != '') {
           $quer = $model->getvalue("SELECT a.nm_personel, a.autono, a.nrp , a.id_jabatan ,b.nm_korps, c.nm_pangkat FROM ttandatangan a 
                                  LEFT JOIN tkorps b    ON a.id_korps = b.kd_korps 
                                  LEFT JOIN  tpangkat c ON a.id_pangkat = c.kd_pangkat WHERE a.autono = '$ttd'");


          $pdf->Ln(5);
          $pdf->CheckPageBreak(60);
          $pdf->Ln(5);
          $pdf->Cell(135);
          $pdf->Cell(10,5,$quer['id_jabatan'],0,1,'C');
         
          $pdf->Cell(100 ,10,'',0,1);//end of line
          $pdf->Ln(25);

          $pdf->Cell(135,5,'',0,0);
          $pdf->Cell(15,5,$quer['nm_personel'],0,1,'C');
          $pdf->Cell(135,5,'',0,0);
          $pdf->Cell(16,5,$quer['nm_pangkat']. " ".$quer['nm_korps']." NRP ".$quer['nrp'],0,1,'C');
          $pdf->Ln(1);
        }else{
          echo "";
        }

       

        global $totalPageForFooter;
      if($pdf->PageNo() != $totalPageForFooter){
        if($pdf->PageNo() > 1){
          $pdf->SetY(25);
          // $pdf->Ln(18);         
          $pdf->SetFont('Arial' , '', 10);
          $pdf->SetY(25);
          $pdf->Cell(12, 6, '1', 'LRTB',0,'C');
          $pdf->Cell(35, 6, '2', 'LRTB', 0, 'C');
          $pdf->Cell(60, 6, '3', 'LRTB', 0, 'C');
          $pdf->Cell(50, 6, '4', 'LRTB', 0, 'C');
          $pdf->Cell(35, 6, '5', 'LRTB', 0, 'C');
          $pdf->Cell(30, 7, '', 0, 0);
          $pdf->Ln(0);
        }
      }


        $pdf->Output();
    }


}
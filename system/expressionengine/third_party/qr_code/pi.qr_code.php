<?php

/*
=====================================================
 Author: MaxLazar
 http://eec.ms
=====================================================
 File: pi.qr_code.php
-----------------------------------------------------
 Purpose: QR Code Generator
=====================================================
#   d = data         URL encoded data.
#   e = ECC level    L or M or Q or H   (default M)
#   s = module size  (dafault PNG:4 JPEG:8)
#   v = version      1-40 or Auto select if you do not set.
#   t = image type   J:jpeg image , other: PNG image
*/

$plugin_info = array(
						'pi_name'			=> 'MX QR code',
						'pi_version'			=> '2.0.6',
						'pi_author'			=> 'Max Lazar',
						'pi_author_url'		=> 'http://eec.ms',
						'pi_description'	=> 'QR Code Generator',
						'pi_usage'			=> Qr_code::usage()
					);


class Qr_code {

    var $return_data="";


    function Qr_code ()
    {

		$this->EE =& get_instance();

		$libfolder = PATH_THIRD.'qr_code/';

		$base_path = ( ! $this->EE->TMPL->fetch_param('base_path')) ? $_SERVER['DOCUMENT_ROOT']."/" : $this->EE->TMPL->fetch_param('base_path');

		$base_path = str_replace("\\", "/", $base_path);
		$base_path = $this->EE->functions->remove_double_slashes($base_path);

		$cache = ( ! $this->EE->TMPL->fetch_param('cache')) ? '' : $this->EE->TMPL->fetch_param('cache');

		$data = array(
									'd' =>  (!$this->EE->TMPL->fetch_param('data')) ? $this->EE->TMPL->tagdata : str_replace(SLASH,'/',$this->EE->TMPL->fetch_param('data')),
									'e' => (!$this->EE->TMPL->fetch_param('ecc')) ? 'M' : $this->EE->TMPL->fetch_param('ecc'),
									't' => (!$this->EE->TMPL->fetch_param('type')) ? 'PNG' : $this->EE->TMPL->fetch_param('type'),
									's' => (!$this->EE->TMPL->fetch_param('size')) ? '' : $this->EE->TMPL->fetch_param('size'),
									'v' => (!$this->EE->TMPL->fetch_param('version')) ? null : $this->EE->TMPL->fetch_param('version'),
								);

		$action = (!$this->EE->TMPL->fetch_param('action')) ? $this->EE->TMPL->tagdata : $this->EE->TMPL->fetch_param('action');

		$data['bk_color']  = ($this->EE->TMPL->fetch_param('bk_color')) ? ltrim($this->EE->TMPL->fetch_param('bk_color'), '#') : 'ffffff';
		$data['px_color']  = ($this->EE->TMPL->fetch_param('px_color')) ? ltrim($this->EE->TMPL->fetch_param('px_color'), '#') : '000000';
		$data['outline_size']  = ($this->EE->TMPL->fetch_param('outline_size')) ? $this->EE->TMPL->fetch_param('outline_size') : 2;

		switch ($action)
		{
		   case "sms":
			  $tel = (!$this->EE->TMPL->fetch_param('tel')) ? '': $this->EE->TMPL->fetch_param('tel');
			  $data['d'] = "SMSTO:".((!$this->EE->TMPL->fetch_param('tel')) ? '': $this->EE->TMPL->fetch_param('tel')).':'.$data['d'];
			  break;
		   case "email":
			  $data['d'] = "SMTP:".((!$this->EE->TMPL->fetch_param('email')) ? '': $this->EE->TMPL->fetch_param('email')).':'.((!$this->EE->TMPL->fetch_param('sabj')) ? '': $this->EE->TMPL->fetch_param('sabj')).':'.$data['d'];
			  break;
		   case "tel":
			  $data['d'] = "TEL:".((!$this->EE->TMPL->fetch_param('tel')) ? '': $this->EE->TMPL->fetch_param('tel'));
			  break;
		   case "site":
			  $data['d'] = $this->SmartUrlEncode($data['d']);
			  break;
		   case "bm":
			  $data['d'] = "MEBKM:TITLE:".((!$this->EE->TMPL->fetch_param('title')) ? '': $this->EE->TMPL->fetch_param('title')).':'.urlencode($data['d']);
			  break;
		}

		$base_cache = $this->EE->functions->remove_double_slashes($base_path."images/cache/");
		$base_cache = ( !$this->EE->TMPL->fetch_param('base_cache')) ? $base_cache : $this->EE->TMPL->fetch_param('base_cache');
		$base_cache = $this->EE->functions->remove_double_slashes($base_cache);

		if(!is_dir($base_cache))
		{
				// make the directory if we can
				if (!mkdir($base_cache,0777,true))
				{
				$this->EE->TMPL->log_item("Error: could not create cache directory ".$base_cache." with 777 permissions");
				return $this->EE->TMPL->no_results();
				}
		}

		$file_ext = ($data['t'] =='J'?'.jpeg':'.png');
		$file_name = md5(serialize($data)).$file_ext;

		if (!is_readable($base_cache.$file_name) ) {
			$qrcode_data_string   = $data['d'];
			$qrcode_error_correct = $data['e'];
			$qrcode_module_size   =  $data['s'];
			$qrcode_version       = $data['v'];
			$qrcode_image_type    = $data['t'];

			$path  = $libfolder.'qrcode_lib/data';
			$image_path = $libfolder.'qrcode_lib/image';

			require_once $libfolder.'qrcode/qrlib.php';

			QRcode::png($qrcode_data_string, $base_cache.$file_name, $qrcode_error_correct , $qrcode_module_size, $data['outline_size'], false,$data['px_color'],$data['bk_color']);
		}


		return $this->return_data =$this->EE->functions->remove_double_slashes("/".str_replace($base_path, '', $base_cache.$file_name));
    }

function SmartUrlEncode($url){
    if (strpos($url, '=') === false):
        return $url;
    else:
        $startpos = strpos($url, "?");
        $tmpurl=substr($url, 0 , $startpos+1) ;
        $qryStr=substr($url, $startpos+1 ) ;
         $qryvalues=explode("&", $qryStr);
          foreach($qryvalues as $value):
              $buffer=explode("=", $value);
            $buffer[1]=urlencode($buffer[1]);
           endforeach;
          $finalqrystr=implode("&amp;", &$qryvalues);
        $finalURL=$tmpurl . $finalqrystr;
        return $finalURL;
    endif;
}

// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage()
{
ob_start();
?>

User Guide http://eec.ms

<?php
$buffer = ob_get_contents();

ob_end_clean();

return $buffer;
}
/* END */

}

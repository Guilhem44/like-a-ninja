<?php

    /**
     * netCloaker Edition Cloaking
     * Cloacking bas� sur Reverse DNS et Detection des plages ip
	 * Reconnaissances de Google Trad et Google Pages Insight
	 * Syst�me de Stats en TXT
     * Jaffaar Saleh
     * http://www.scripts-seo.com
     * Version 1.0
     */
    

	class netCloaker {
	
		private $owner_ip;
		private $root;
		private $debug;
		private $ua_cloaker;
		private $allowGoogleTool;
		private $tracker;
	
		function __construct(){
			//header("X-Robots-Tag: NOARCHIVE", true);
		}
		
		public function setDebugMode($bool=0){
			if($_SERVER['REMOTE_ADDR']==$this->owner_ip)$this->debug=1;
			else $this->debug = $bool;
			if($this->debug)echo'DBUG = '.$this->debug.'<br>';
		}
		public function setUACloaker($bool){
			$this->ua_cloaker = $bool;
			if($this->debug)echo'GGUA = '.$this->ua_cloaker.'<br>';
		}
		public function setRoot($root){
			$this->root = $root.'/index.php?';
			if($this->debug)echo'ROOTS = '.$this->root.'<br>';
		}
		public function setOwnerIp($ip){
			$this->owner_ip = $ip;
			$this->setDebugMode();
			if($this->debug)echo'OWNIP = '.$this->owner_ip.'<br>';
		}
		public function setAllowGoogleTool($bool){
			$this->allowGoogleTool = $bool;
			if($this->debug)echo'GGTRA = '.$this->allowGoogleTool.'<br>';
		}
		public function setTracker($bool){
			$this->tracker = $bool;
			if($this->debug)echo'STATS = '.$this->tracker.'<br>';
		}	
		
		public function isGoogle(){
			
			if($this->allowGoogleTool!=true){
				$pagespeed = strpos($_SERVER["HTTP_USER_AGENT"],"Google Page Speed Insights");
				$gtranslate = strpos($_SERVER["HTTP_USER_AGENT"],"gfe");
				if($pagespeed == true OR $gtranslate == true){
					$this->setDown();
					exit;
				}
			}
			
			/* Analyse User Agent (d�sactiv�) */
			if($this->ua_cloaker){
			$ua = $_SERVER['HTTP_USER_AGENT'];	
			$googlebot_ua = strpos(strtolower($ua),"googlebot");
			}else $googlebot_ua = false;
			
			if($_SERVER['REMOTE_ADDR']==$this->owner_ip)$googlebot_ua = true;
		
			/* Analyse DNS reverse */
			$dns=gethostbyaddr($_SERVER["REMOTE_ADDR"]);
			//$googlebot_dns = strpos(strtolower($dns),"googlebot");
			$googlebot_dns_2 = preg_match("/crawl-66-249-[\d]{1,3}-[\d]{1,3}\.googlebot\.com/", $dns);
			
			/*ANalyse de l'ip */
			$ip = strval($_SERVER['REMOTE_ADDR']);
			$googlebot_ip = preg_match("/66\.249\.[\d]{1,3}\.[\d]{1,3}/", $ip);
			$googlebot_ip_2 = preg_match("/72\.14\.[\d]{1,3}\.[\d]{1,3}/", $ip);
			//$googlebot_ip = preg_match("/82\.237\.[\d]{1,3}\.[\d]{1,3}/", $ip);
	
			if($googlebot_ua!=false||$googlebot_dns_2!=false||$googlebot_ip!=false
			||$googlebot_ip_2!=false||$pagespeed == true OR $gtranslate == true){
				//$this->track_stats(true);
				return true; 
			}else{
				//$this->track_stats(false);
				return false;
			}
		}		
		public function is_referal($who){
			if (strpos( $_SERVER['HTTP_REFERER'],$who))return true;else return false;
		}
		public function makeCloaked301($target){
			header("Status: 301 Moved Permanently", false, 301);
			header("Location:".$target);
			header("X-Robots-Tag: NOARCHIVE", true);
		}
		public function makeCloaked302($target){
			header("Status: 302 Found", false, 302);
			header("Location:".$target);
			header("X-Robots-Tag: NOARCHIVE", true);
		}
		public function setDown(){
			sleep(120);
			header("HTTP/1.0 503 Service unavailable");
			//echo 'Service unavailable';
		}

		private function track_stats($isGoogle){

			if(!$this->tracker)return false;
			
			if (!file_exists('stats.txt')) {
				$file = fopen('stats.txt', 'w+');
			} 
		
			$file = fopen('stats.txt', 'r+');
			chmod ('stats.txt',0777);
			$file_str = fgets($file); 
			$file_tab = explode('|', $file_str);
			
			$googlebot = str_replace('googlebot:','',$file_tab[0]);
			$visitors = str_replace('visitors:','',$file_tab[1]);
			if($googlebot=='')$googlebot=0;
			if($visitors=='')$visitors=0;
			
			if($_SERVER['REMOTE_ADDR']!=$this->owner_ip){
				if($isGoogle)$googlebot++;
				else $visitors++;
				fseek($file, 0);
				$data = 'googlebot:'.$googlebot;
				$data .= '|';
				$data .= 'visitors:'.$visitors;
				fputs($file, $data);
			}
			fclose($file);
			
			if($this->debug)echo'STAT = googlebot : '.$googlebot.' | visitors : '.$visitors.'<hr>';
			
			if($_SERVER['REMOTE_ADDR']!=$this->owner_ip){
				$file = fopen('logs.txt', 'a+');
				chmod ('logs.txt',0777);
				$date = date('Y-m-d h:i:s');
				$file_str = $date.' : ';
				if($isGoogle)$file_str .= 'GOOGLE : ';
				else $file_str .= 'HUMAN  : ';
				$file_str .= $_SERVER['REMOTE_ADDR'].' - '.$_SERVER['HTTP_USER_AGENT'].' - '.$_SERVER['REQUEST_URI'].' - '.
				$_SERVER['HTTP_REFERER'].'|'."\r\n";
				fputs($file, $file_str);
				fclose($file);
			}
		
		}
		
	
	
	}